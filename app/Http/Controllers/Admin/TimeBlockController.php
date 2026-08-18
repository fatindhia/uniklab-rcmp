<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TimeBlock;
use App\Support\BookingCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimeBlockController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Schedule & Block';

        $calendarEvents = BookingCalendar::events();

        // The list only carries blocks that still matter — today's and later.
        // A recurring block keeps blocking slots past its first date (up to 4
        // occurrences), so "past" means its *last* occurrence has gone by, not
        // merely its block_date; the SQL filter is a cheap prefilter and the
        // exact recurrence check happens below. Past blocks stay on record and
        // remain visible in History.
        $allBlocks = TimeBlock::query()
            ->where(fn ($q) => $q->whereDate('block_date', '>=', today())->orWhere('recurring', '!=', 'none'))
            ->orderBy('block_date')
            ->orderBy('start_time')
            ->get()
            ->filter(fn ($block) => $this->lastOccurrence($block)->gte(today()))
            ->values();

        $blockTab = in_array($request->query('blockTab'), ['research', 'csl', 'pharma'], true)
            ? $request->query('blockTab')
            : 'all';

        $blockCounts = [
            'all' => $allBlocks->count(),
            'research' => $allBlocks->where('lab_type', 'research')->count(),
            'csl' => $allBlocks->where('lab_type', 'csl')->count(),
            'pharma' => $allBlocks->where('lab_type', 'pharma')->count(),
        ];

        $blocks = $blockTab === 'all' ? $allBlocks : $allBlocks->where('lab_type', $blockTab)->values();

        // How many rooms each multi-room request covers, so a block saved as
        // part of one can say so (they're separate rows, one room each).
        // Counted over every block on record, not just the upcoming ones —
        // "1 of 3 rooms" describes the original request, so it shouldn't shrink
        // as the sibling rooms' dates go by.
        $groupSizes = TimeBlock::whereNotNull('group_id')->pluck('group_id')->countBy();

        return view('admin.time-blocks.index', compact('pageTitle', 'calendarEvents', 'blockTab', 'blockCounts', 'blocks', 'groupSizes'));
    }

    /**
     * The last date a block still occupies a slot: its own date for a one-off,
     * or the final repeat for a recurring one.
     */
    private function lastOccurrence(TimeBlock $block): \Illuminate\Support\Carbon
    {
        $dates = BookingCalendar::recurDates($block->block_date, $block->recurring);

        return \Illuminate\Support\Carbon::parse(end($dates));
    }

    /**
     * One request can block out several rooms, each on its own date and time
     * slot ("Room 1 → 08:00-10:00, Room 2 → 10:00-12:00"). Each room is saved
     * as its own TimeBlock — so it can be reviewed, conflict-checked and
     * removed independently — with a shared group_id tying the set together.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'lab_type' => ['required', 'in:research,csl,pharma'],
            'blocks' => ['required', 'array', 'min:1'],
            'blocks.*.room' => ['required', 'string', 'max:150'],
            // Blocking a slot that has already gone changes nothing — the room
            // was never bookable for it anyway — and a recurring block seeded
            // from a past date would put its occurrences in the past too.
            'blocks.*.block_date' => ['required', 'date', 'after_or_equal:today'],
            'blocks.*.start_time' => ['required', 'date_format:H:i', 'after_or_equal:08:00'],
            'blocks.*.end_time' => ['required', 'date_format:H:i', 'before_or_equal:17:00'],
            'blocks.*.equipment' => ['nullable', 'array'],
            'blocks.*.equipment.*' => ['string'],
            // Details are per room too — each block carries its own purpose,
            // title, PIC, recurrence and notes.
            'blocks.*.purpose' => ['required', 'in:class,practical,maintenance,reserved,exam,event'],
            'blocks.*.title' => ['required', 'string', 'max:200'],
            'blocks.*.pic' => ['nullable', 'string', 'max:150'],
            'blocks.*.recurring' => ['required', 'in:none,weekly,biweekly'],
            'blocks.*.notes' => ['nullable', 'string'],
        ], [
            'blocks.*.block_date.after_or_equal' => 'The block date cannot be in the past.',
            'blocks.*.start_time.after_or_equal' => 'Blocks can only start from 08:00.',
            'blocks.*.end_time.before_or_equal' => 'Blocks must end by 17:00.',
        ], [
            'blocks.*.room' => 'room',
            'blocks.*.block_date' => 'date',
            'blocks.*.start_time' => 'start time',
            'blocks.*.end_time' => 'end time',
            'blocks.*.title' => 'title',
            'blocks.*.purpose' => 'purpose',
        ]);

        $entries = array_values($data['blocks']);
        $messages = [];

        foreach ($entries as $index => $entry) {
            $room = $entry['room'];

            if ($entry['end_time'] <= $entry['start_time']) {
                $messages['blocks.'.$index.'.end_time'] = $room.': end time must be after start time.';

                continue;
            }

            // after_or_equal:today admits today, but a slot that already ended
            // today is still in the past.
            if ($entry['block_date'] === today()->toDateString() && $entry['end_time'] <= now()->format('H:i')) {
                $messages['blocks.'.$index.'.start_time'] = $room.': that time has already passed today.';

                continue;
            }

            // Every room's slot is checked on its own date/time, against
            // existing bookings, existing blocks, and the other rooms in this
            // very request (the same room can't be listed twice for
            // overlapping slots).
            $conflict = $this->slotConflict($room, $entry, array_slice($entries, 0, $index));

            if ($conflict) {
                $messages['blocks.'.$index.'.start_time'] = $conflict;
            }
        }

        if ($messages) {
            throw ValidationException::withMessages($messages);
        }

        $next = TimeBlock::query()->pluck('id')
            ->map(fn ($id) => (int) preg_replace('/\D/', '', $id))
            ->max() + 1;

        $groupId = null;

        DB::transaction(function () use ($entries, $data, $next, &$groupId) {
            foreach ($entries as $index => $entry) {
                $id = 'TB-'.str_pad((string) ($next + $index), 2, '0', STR_PAD_LEFT);
                $groupId ??= $id;

                TimeBlock::create([
                    'id' => $id,
                    'group_id' => $groupId,
                    'lab_type' => $data['lab_type'],
                    'purpose' => $entry['purpose'],
                    'title' => $entry['title'],
                    'pic' => $entry['pic'] ?? '',
                    'block_date' => $entry['block_date'],
                    'start_time' => $entry['start_time'],
                    'end_time' => $entry['end_time'],
                    'rooms' => [$entry['room']],
                    'equipment' => array_values($entry['equipment'] ?? []),
                    'recurring' => $entry['recurring'],
                    'notes' => $entry['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }
        });

        BookingCalendar::flush();

        return back()->with('status', count($entries) === 1
            ? 'Time block added.'
            : count($entries).' time blocks added (one per room).');
    }

    /**
     * Why this room can't be blocked for the given slot, or null if it's free.
     * Recurring blocks are checked on every occurrence they would create, and
     * against every occurrence of the blocks already saved.
     *
     * @param  array<int, array<string, mixed>>  $earlierEntries  rooms already scheduled in this same request
     */
    private function slotConflict(string $room, array $entry, array $earlierEntries): ?string
    {
        $dates = BookingCalendar::recurDates($entry['block_date'], $entry['recurring']);
        $start = $entry['start_time'];
        $end = $entry['end_time'];

        foreach ($earlierEntries as $other) {
            if ($other['room'] !== $room) {
                continue;
            }

            $otherDates = BookingCalendar::recurDates($other['block_date'], $other['recurring']);

            if (array_intersect($dates, $otherDates) && $other['start_time'] < $end && $other['end_time'] > $start) {
                return $room.' is listed twice in this request with overlapping times.';
            }
        }

        $bookingClash = Booking::query()
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->whereHas('rooms.lab', fn ($q) => $q->where('name', $room))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->where(function ($q) use ($dates) {
                foreach ($dates as $date) {
                    $q->orWhere(fn ($inner) => $inner->where('booking_date_from', '<=', $date)->where('booking_date_to', '>=', $date));
                }
            })
            ->first();

        if ($bookingClash) {
            return $room.' already has an overlapping '.$bookingClash->status.' booking ('.$bookingClash->ref.') at '.$start.'–'.$end.'.';
        }

        $blockClash = TimeBlock::query()
            ->whereJsonContains('rooms', $room)
            ->whereTime('start_time', '<', $end)
            ->whereTime('end_time', '>', $start)
            ->get()
            ->first(fn (TimeBlock $block) => (bool) array_intersect(
                $dates,
                BookingCalendar::recurDates($block->block_date, $block->recurring)
            ));

        if ($blockClash) {
            return $room.' is already blocked by "'.$blockClash->title.'" overlapping '.$start.'–'.$end.'.';
        }

        return null;
    }

    public function destroy(TimeBlock $timeBlock)
    {
        $timeBlock->delete();

        BookingCalendar::flush();

        return back()->with('status', 'Time block removed.');
    }

    /**
     * One block as JSON, for the calendar's hover card.
     *
     * The cached calendar payload only carries what the grid draws (title,
     * rooms, times); equipment, notes, recurrence and who created it are
     * fetched here for the single block being hovered, mirroring
     * Admin\BookingController::details().
     */
    public function details(TimeBlock $timeBlock)
    {
        $timeBlock->loadMissing('creator');

        return response()->json([
            'id' => $timeBlock->id,
            'title' => $timeBlock->title,
            'purpose' => $timeBlock->purpose,
            'lab_type' => $timeBlock->lab_type,
            'pic' => $timeBlock->pic,
            'date' => $timeBlock->block_date?->format('d/m/Y'),
            'start' => substr((string) $timeBlock->start_time, 0, 5),
            'end' => substr((string) $timeBlock->end_time, 0, 5),
            'rooms' => $timeBlock->rooms ?? [],
            'equipment' => $timeBlock->equipment ?? [],
            'recurring' => $timeBlock->recurring,
            'notes' => $timeBlock->notes,
            'created_by' => $timeBlock->creator?->full_name,
            'created_at' => $timeBlock->created_at?->format('d/m/Y, H:i'),
        ]);
    }
}
