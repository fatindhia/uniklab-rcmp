<?php

namespace App\Http\Controllers;

use App\Mail\BookingSubmitted;
use App\Mail\NewBookingTicket;
use App\Models\Booking;
use App\Models\BookingAuditLog;
use App\Models\BookingEquipment;
use App\Models\BookingRoom;
use App\Models\BookingStudent;
use App\Models\Lab;
use App\Models\LabEquipment;
use App\Models\TimeBlock;
use App\Models\User;
use App\Support\BookingCalendar;
use App\Support\EquipmentConditions;
use App\Support\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $type = in_array($request->query('type'), ['equipment', 'csl', 'pharma'], true)
            ? $request->query('type')
            : 'equipment';

        $maintenanceBlocked = Maintenance::blocksBooking($request->user());

        if ($maintenanceBlocked) {
            $maintenanceTitle = Maintenance::title();
            $maintenanceMessage = Maintenance::message();

            return view('bookings.create', compact('type', 'maintenanceBlocked', 'maintenanceTitle', 'maintenanceMessage'));
        }

        $labType = $type === 'equipment' ? 'research' : $type;

        $labs = Lab::query()
            ->with(['equipment' => fn ($query) => $query->orderBy('sort_order')])
            ->where('status', 'active')
            ->where('lab_type', $labType)
            // CSL rooms are seeded in CSL1-then-CSL2, numeric room order; preserve that
            // instead of sorting alphabetically (which would break the grouping below
            // and put "Room 10" before "Room 2").
            ->orderBy($labType === 'csl' ? 'id' : 'name')
            ->get();

        $staffRoles = config('booking.staff_roles');
        $studentRoles = config('booking.student_roles');
        $applicantRoles = array_merge($staffRoles, $studentRoles);
        $cslSessionTypes = config('booking.csl_session_types');
        $cslDisciplines = config('booking.csl_disciplines');
        $cslPackageDisciplines = config('booking.csl_package_disciplines');
        // Discipline -> selectable lab ids, so the room tiles can be filtered
        // (and package disciplines auto-selected) client-side without a fetch.
        $cslDisciplineLabIds = $labType === 'csl'
            ? collect(config('booking.csl_discipline_rooms'))
                ->map(fn ($roomNames) => $labs->whereIn('name', $roomNames)->pluck('id')->values())
            : collect();
        $researchRules = config('booking.research');
        $pharmaRules = config('booking.pharma');
        $cslRules = config('booking.csl');
        $staffEmailDomain = config('booking.staff_email_domain');
        $studentEmailDomain = config('booking.student_email_domain');
        $paxMax = config('booking.pax_max');
        $buildings = $labs->where('lab_type', 'research')->pluck('building')->unique()->sort()->values();

        return view('bookings.create', compact(
            'type', 'labs', 'applicantRoles', 'staffRoles', 'studentRoles',
            'cslSessionTypes', 'cslDisciplines', 'cslPackageDisciplines', 'cslDisciplineLabIds',
            'researchRules', 'pharmaRules', 'cslRules',
            'staffEmailDomain', 'studentEmailDomain', 'paxMax', 'buildings', 'maintenanceBlocked'
        ));
    }

    public function store(Request $request)
    {
        if (Maintenance::blocksBooking($request->user())) {
            throw ValidationException::withMessages([
                'maintenance' => Maintenance::message(),
            ]);
        }

        $type = in_array($request->input('type'), ['equipment', 'csl', 'pharma'], true)
            ? $request->input('type')
            : 'equipment';

        $labType = $type === 'equipment' ? 'research' : $type;
        $staffRoles = config('booking.staff_roles');
        $studentRoles = config('booking.student_roles');

        $data = $request->validate([
            'applicant_name' => ['required', 'string', 'max:150'],
            'applicant_id' => ['required', 'string', 'max:30'],
            'applicant_email' => ['required', 'email', 'max:150'],
            // Digits only, no spaces/dashes/country code (e.g. 01114354678).
            'applicant_phone' => ['nullable', 'digits_between:10,11'],
            'applicant_department' => ['nullable', 'string', 'max:150'],
            'applicant_role' => ['required', 'string', 'in:'.implode(',', array_merge($staffRoles, $studentRoles))],
            'applicant_group' => ['nullable', 'string', 'max:30'],
            'applicant_remark' => ['nullable', 'string', 'max:1000'],
            // A booking can only ever be for now or later. Without this a
            // backdated booking would take up a room in the calendar and the
            // conflict checks below, for a slot that has already gone.
            'booking_date_from' => ['required', 'date', 'after_or_equal:today'],
            'booking_date_to' => ['nullable', 'date', 'after_or_equal:booking_date_from'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string'],
            // CSL rooms come from the discipline mapping below (a package
            // discipline supplies them itself), so they're only required
            // once that mapping has been applied.
            'lab_ids' => $type === 'csl' ? ['array'] : ['required', 'array', 'min:1'],
            'lab_ids.*' => ['integer', 'exists:labs,id'],
            'equipment_names' => ['array'],
            'equipment_names.*' => ['string', 'max:300'],
            'csl_session_type' => [$type === 'csl' ? 'required' : 'nullable', 'string', 'max:60'],
            'csl_discipline' => [$type === 'csl' ? 'required' : 'nullable', 'string', 'max:60'],
            'csl_procedure' => [$type === 'csl' ? 'required' : 'nullable', 'string', 'max:2000'],
            'more_pax' => ['nullable', 'boolean'],
            'pax_count' => ['nullable', 'integer', 'min:0', 'max:'.config('booking.pax_max')],
            'pax_names' => ['array'],
            'pax_names.*' => ['nullable', 'string', 'max:150'],
            'pax_ids' => ['array'],
            'pax_ids.*' => ['nullable', 'string', 'max:30'],
            'pharma_tc_accepted' => ['nullable', 'boolean'],
        ], [
            // The generated wording ("The booking date from field must be a date
            // after or equal to today") reads like a rule, not an instruction.
            'booking_date_from.after_or_equal' => 'The booking date cannot be in the past.',
            'booking_date_to.after_or_equal' => 'The end date cannot be earlier than the start date.',
            'end_time.after' => 'The end time must be later than the start time.',
        ]);

        // --- Email domain -> applicant category (staff/student), and role cross-check ---
        $staffDomain = config('booking.staff_email_domain');
        $studentDomain = config('booking.student_email_domain');
        $emailDomain = strtolower(trim((string) strrchr($data['applicant_email'], '@'), '@'));

        $category = match ($emailDomain) {
            $studentDomain => 'student',
            $staffDomain => 'staff',
            default => null, 
        };

        if ($category === null) {
            throw ValidationException::withMessages([
                'applicant_email' => "Email must be a @{$staffDomain} (staff) or @{$studentDomain} (student) address.",
            ]);
        }

        $allowedRoles = $category === 'staff' ? $staffRoles : $studentRoles;

        if (! in_array($data['applicant_role'], $allowedRoles, true)) {
            throw ValidationException::withMessages([
                'applicant_role' => 'Selected role is not valid for a @'.($category === 'staff' ? $staffDomain : $studentDomain).' email.',
            ]);
        }

        if ($type === 'pharma') {
            $pharmaAllowedRoles = config('booking.pharma_allowed_roles');

            if ($category !== 'staff' || ! in_array($data['applicant_role'], $pharmaAllowedRoles, true)) {
                throw ValidationException::withMessages([
                    'applicant_role' => 'Pharma lab bookings can only be made by: '.implode(', ', $pharmaAllowedRoles).'.',
                ]);
            }
        } elseif ($type === 'equipment') {
            $researchAllowedRoles = config('booking.research_allowed_roles');

            if (! in_array($data['applicant_role'], $researchAllowedRoles, true)) {
                throw ValidationException::withMessages([
                    'applicant_role' => 'Research & Development lab bookings can only be made by: '.implode(', ', $researchAllowedRoles).'.',
                ]);
            }
        }

        $minutes = \Carbon\Carbon::parse($data['start_time'])->diffInMinutes(\Carbon\Carbon::parse($data['end_time']));

        if ($minutes < config('booking.min_booking_minutes')) {
            throw ValidationException::withMessages([
                'end_time' => 'Bookings must be at least '.config('booking.min_booking_minutes').' minutes long.',
            ]);
        }

        $bookingDate = \Carbon\Carbon::parse($data['booking_date_from']);
        $bookingDateTo = $data['booking_date_to'] ?? $data['booking_date_from'];
        $isWeekend = $bookingDate->isWeekend();

        // after_or_equal:today lets today through, but a slot earlier today has
        // already gone — the date rule alone can't see that.
        if ($bookingDate->isToday() && $data['start_time'] <= now()->format('H:i')) {
            throw ValidationException::withMessages([
                'start_time' => 'That time has already passed today. Pick a later time, or a later date.',
            ]);
        }

        if ($type === 'equipment') {
            $rules = config('booking.research');

            if ($data['start_time'] < $rules['weekday_start'] || $data['end_time'] > $rules['weekday_end']) {
                throw ValidationException::withMessages([
                    'start_time' => 'Research & Development lab hours are '.$rules['weekday_start'].'–'.$rules['weekday_end'].'.',
                ]);
            }
        } elseif ($type === 'pharma') {
            $rules = config('booking.pharma');
            [$windowStart, $windowEnd] = $isWeekend
                ? [$rules['weekend_start'], $rules['weekend_end']]
                : [$rules['weekday_start'], $rules['weekday_end']];

            if ($data['start_time'] < $windowStart || $data['end_time'] > $windowEnd) {
                throw ValidationException::withMessages([
                    'start_time' => 'Pharma lab hours are '.$windowStart.'–'.$windowEnd.' '.($isWeekend ? 'on weekends.' : 'on weekday evenings.'),
                ]);
            }
        } elseif ($type === 'csl') {
            $rules = config('booking.csl');

            if ($rules['weekdays_only'] && $this->rangeIncludesWeekend($data['booking_date_from'], $bookingDateTo)) {
                throw ValidationException::withMessages([
                    'booking_date_from' => 'CSL bookings are only available on weekdays.',
                ]);
            }

            if ($data['start_time'] < $rules['day_start'] || $data['end_time'] > $rules['day_end']) {
                throw ValidationException::withMessages([
                    'start_time' => 'CSL lab hours are '.$rules['day_start'].'–'.$rules['day_end'].'.',
                ]);
            }

            $advanceViolation = $this->cslAdvanceNoticeViolation($bookingDate, $rules['advance_working_days']);

            if ($advanceViolation) {
                throw ValidationException::withMessages(['booking_date_from' => $advanceViolation]);
            }
        }

        if ($type === 'csl') {
            $data['lab_ids'] = $this->cslDisciplineLabIds($data['csl_discipline'], $data['lab_ids'] ?? []);
        }

        $labs = Lab::whereIn('id', $data['lab_ids'])->get();

        $weekendViolation = $this->weekendClosedRoomsViolation($labs, $data['booking_date_from'], $bookingDateTo);

        if ($weekendViolation) {
            throw ValidationException::withMessages(['booking_date_from' => $weekendViolation]);
        }

        if ($type === 'csl') {
            $rules = config('booking.csl');
            $bufferViolation = $this->cslBufferConflict($labs->pluck('id'), $data['booking_date_from'], $data['start_time'], $data['end_time'], $rules['buffer_minutes']);

            if ($bufferViolation) {
                throw ValidationException::withMessages(['start_time' => $bufferViolation]);
            }

            $blockedCslIds = $this->blockedRoomLabIds($labs->pluck('id'), $data['booking_date_from'], $bookingDateTo, $data['start_time'], $data['end_time']);

            if ($blockedCslIds->isNotEmpty()) {
                throw ValidationException::withMessages(['start_time' => 'A selected CSL room is blocked by the admin for an overlapping time slot. Please choose another time.']);
            }
        }

        if ($type === 'equipment') {
            // Rooms are multi-select (a booking can span several rooms in the
            // same building at once), but they must all share one building —
            // the pill filter already keeps the UI to one building at a time.
            if ($labs->pluck('building')->unique()->count() > 1) {
                throw ValidationException::withMessages([
                    'lab_ids' => 'All selected rooms must be from the same building.',
                ]);
            }

            $roomOnlyViolations = $this->roomOnlyConflicts($labs, $data['booking_date_from'], $bookingDateTo, $data['start_time'], $data['end_time']);

            if ($roomOnlyViolations) {
                throw ValidationException::withMessages(['lab_ids' => $roomOnlyViolations[0]]);
            }
        }

        // --- Primary lab: which room "owns" the booking (pharma code) — every
        // type now supports selecting several rooms at once, so the first selected room is
        // treated as primary purely for bookkeeping (BookingRoom.is_primary, admin room
        // reassignment), while every selected room still gets its own BookingRoom row below. ---
        $primaryLabId = (int) $data['lab_ids'][0];
        $primaryLab = $labs->firstWhere('id', $primaryLabId);

        // --- Pax / "more than you" ---
        $morePax = $type === 'pharma' ? true : $request->boolean('more_pax');
        $paxCount = $morePax ? (int) ($data['pax_count'] ?? 0) : 0;

        if ($morePax && $paxCount < 1) {
            throw ValidationException::withMessages([
                'pax_count' => 'Please enter the number of additional pax.',
            ]);
        }

        $paxNames = array_slice($data['pax_names'] ?? [], 0, $paxCount);
        $paxIds = array_slice($data['pax_ids'] ?? [], 0, $paxCount);

        for ($i = 0; $i < $paxCount; $i++) {
            if (trim((string) ($paxNames[$i] ?? '')) === '' || trim((string) ($paxIds[$i] ?? '')) === '') {
                throw ValidationException::withMessages([
                    'pax_names' => 'Please provide a name and Staff/Student ID for every pax.',
                ]);
            }
        }

        $pharmaCode = null;

        if ($type === 'pharma') {
            $pharmaCode = $this->derivePharmaCode($primaryLab);

            if (! $pharmaCode) {
                throw ValidationException::withMessages([
                    'lab_ids' => 'Selected lab is not a recognised pharma room.',
                ]);
            }

            // Equipment can't leave its room, so every selected lab (whether
            // chosen as a room outright or only for its equipment) reserves
            // the same headcount from ITS OWN capacity — not just the first
            // ("primary") one.
            foreach ($labs as $lab) {
                if ($lab->capacity <= 0) {
                    continue;
                }

                $remaining = $this->pharmaRemainingCapacity($lab, $data['booking_date_from'], $bookingDateTo, $data['start_time'], $data['end_time']);

                if ($paxCount > $remaining) {
                    throw ValidationException::withMessages([
                        'pax_count' => 'Number of pax ('.$paxCount.') exceeds the remaining capacity ('.$remaining.' of '.$lab->capacity.') for '.$lab->name.' at this time.',
                    ]);
                }
            }

            if (! $request->boolean('pharma_tc_accepted')) {
                throw ValidationException::withMessages([
                    'pharma_tc_accepted' => 'You must accept the Pharma Lab Terms & Conditions before booking.',
                ]);
            }
        }

        $selectedEquipment = $this->parseEquipmentSelections($data['equipment_names'] ?? []);

        if ($type === 'equipment') {
            $buildingViolation = $this->equipmentLabsValid($primaryLab, $selectedEquipment);

            if ($buildingViolation) {
                throw ValidationException::withMessages(['equipment_names' => $buildingViolation]);
            }

            $equipmentRequiredViolation = $this->equipmentRequiredForRoom($labs, $selectedEquipment);

            if ($equipmentRequiredViolation) {
                throw ValidationException::withMessages(['equipment_names' => $equipmentRequiredViolation]);
            }
        }

        if ($type === 'equipment' || $type === 'pharma') {
            // Equipment cannot be borrowed from a lab that wasn't itself
            // selected as a room — the room's capacity/exclusivity is what
            // "pays" for it, equipment can't leave its room.
            $selectedRoomIds = array_map('intval', $data['lab_ids']);
            $hasOrphanEquipment = collect($selectedEquipment)->contains(fn ($sel) => ! in_array($sel['lab_id'], $selectedRoomIds, true));

            if ($hasOrphanEquipment) {
                throw ValidationException::withMessages([
                    'equipment_names' => 'Equipment can only be selected from a lab that is also selected as a room — equipment cannot leave its room.',
                ]);
            }
        }

        // Whether any selected equipment carries its own day/time/duration rules
        // (already enforced below) — recorded on the booking for admin visibility,
        // no separate applicant acknowledgement required.
        $hasSpecialConditions = false;

        if ($selectedEquipment) {
            $equipmentRows = $this->loadEquipmentRows($selectedEquipment);
            $hasSpecialConditions = $equipmentRows->contains(fn ($row) => $row->special_conditions_note !== '');

            $equipmentViolations = $this->equipmentConflicts($selectedEquipment, $data['booking_date_from'], $bookingDateTo, $data['start_time'], $data['end_time'], $equipmentRows);

            if ($equipmentViolations) {
                throw ValidationException::withMessages(['equipment_names' => $equipmentViolations[0]]);
            }
        }

        $booking = DB::transaction(function () use ($data, $type, $labType, $labs, $primaryLab, $primaryLabId, $pharmaCode, $paxCount, $paxNames, $paxIds, $selectedEquipment, $hasSpecialConditions, $request) {
            $ref = 'BK-'.str_pad((string) (Booking::max('id') + 1), 3, '0', STR_PAD_LEFT);

            $booking = Booking::create([
                'ref' => $ref,
                'user_staff_id' => auth()->id(),
                'applicant_name' => $data['applicant_name'],
                'applicant_id' => $data['applicant_id'],
                'applicant_email' => $data['applicant_email'],
                'applicant_phone' => $data['applicant_phone'] ?? '',
                'applicant_department' => $data['applicant_department'] ?? '',
                'applicant_role' => $data['applicant_role'],
                'applicant_group' => $data['applicant_group'] ?? '',
                'applicant_remark' => $data['applicant_remark'] ?? null,
                'lab_type' => $labType,
                'booking_date_from' => $data['booking_date_from'],
                'booking_date_to' => $data['booking_date_to'] ?? $data['booking_date_from'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'research_pax' => $type === 'equipment' ? (1 + $paxCount) : 0,
                'has_special_conditions' => $hasSpecialConditions,
                'csl_session_type' => $data['csl_session_type'] ?? '',
                'csl_discipline' => $data['csl_discipline'] ?? '',
                'csl_procedure' => $data['csl_procedure'] ?? '',
                'csl_num_students' => $type === 'csl' ? $paxCount : 0,
                'pharma_primary_lab' => $pharmaCode,
                'pharma_num_students' => $type === 'pharma' ? $paxCount : 0,
                'pharma_tc_accepted' => $request->boolean('pharma_tc_accepted'),
                'purpose' => $data['purpose'],
                'status' => 'pending',
            ]);

            foreach ($labs as $lab) {
                BookingRoom::create([
                    'booking_id' => $booking->id,
                    'lab_id' => $lab->id,
                    'is_primary' => $lab->id === $primaryLabId,
                ]);
            }

            // "Alt lab" equipment is equipment borrowed from a lab that wasn't
            // itself selected as a room (bonus equipment, no BookingRoom row) —
            // not just "not the primary", since Equipment now supports booking
            // several rooms at once.
            $selectedRoomIds = array_map('intval', $data['lab_ids']);

            foreach ($selectedEquipment as $sel) {
                BookingEquipment::create([
                    'booking_id' => $booking->id,
                    'lab_id' => $sel['lab_id'],
                    'equipment_name' => $sel['equipment_name'],
                    'is_alt_lab' => ! in_array($sel['lab_id'], $selectedRoomIds, true),
                ]);
            }

            for ($i = 0; $i < $paxCount; $i++) {
                BookingStudent::create([
                    'booking_id' => $booking->id,
                    'student_name' => $paxNames[$i],
                    'student_id' => $paxIds[$i],
                    'student_year' => null,
                    'sort_order' => $i,
                ]);
            }

            BookingAuditLog::create([
                'booking_id' => $booking->id,
                'action' => 'created',
                'performed_by' => auth()->id(),
                'detail' => 'Booking submitted',
            ]);

            return $booking;
        });

        BookingCalendar::flush();

        $this->sendBookingSubmittedEmails($booking);

        return redirect()->route('bookings.show', $booking)
            ->with('status', 'Booking submitted. Reference: '.$booking->ref);
    }

    /**
     * Confirmation ticket to the applicant, plus a heads-up to the staff who
     * actually handle this booking's lab area — best-effort: a mail failure
     * shouldn't undo an already-committed booking.
     */
    private function sendBookingSubmittedEmails(Booking $booking): void
    {
        $accepted = [];

        try {
            Mail::to($booking->applicant_email)->send(new BookingSubmitted($booking));
            $accepted[] = 'applicant:'.$booking->applicant_email;
        } catch (\Throwable $e) {
            report($e);
        }

        // Admins plus the lab staff assigned to this lab type — see
        // User::scopeBookingTicketRecipients() for who's left out and why.
        // Each send is isolated: one unreachable or malformed address must not
        // stop the rest of the team being told about the booking.
        foreach (User::bookingTicketRecipientEmails($booking->lab_type) as $adminEmail) {
            try {
                Mail::to($adminEmail)->send(new NewBookingTicket($booking));
                $accepted[] = 'staff:'.$adminEmail;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Recorded so "who was actually emailed about this booking?" is
        // answerable afterwards. Handing a message to the SMTP server is not
        // the same as it reaching an inbox — spam filtering happens after
        // this point — but it separates "the app never sent it" from
        // "it was sent and filtered", which is otherwise guesswork.
        Log::info('Booking '.$booking->ref.' ('.$booking->lab_type.') mail accepted by SMTP for: '
            .(implode(', ', $accepted) ?: 'nobody'));
    }

    /**
     * Live pre-submit check: same conflict rules as store(), read-only, used to
     * surface inline alerts (min. booking length, CSL advance-notice/buffer,
     * room-only exclusivity, equipment double-booking) as soon as the applicant
     * picks a date/time/room — split by section so each alert can be shown right
     * next to the fields it's about (Schedule vs. Rooms & equipment).
     */
    public function checkAvailability(Request $request)
    {
        $type = in_array($request->input('type'), ['equipment', 'csl', 'pharma'], true)
            ? $request->input('type')
            : 'equipment';

        $data = $request->validate([
            'booking_date_from' => ['nullable', 'date'],
            'booking_date_to' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'lab_ids' => ['array'],
            'lab_ids.*' => ['integer'],
            'equipment_names' => ['array'],
            'equipment_names.*' => ['string', 'max:300'],
        ]);

        $dateFrom = $data['booking_date_from'] ?? null;
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;

        if (! $dateFrom || ! $startTime || ! $endTime) {
            return response()->json(['ok' => true, 'schedule' => [], 'rooms' => []]);
        }

        $scheduleMessages = [];

        if ($endTime <= $startTime) {
            $scheduleMessages[] = 'End time must be after start time.';

            return response()->json(['ok' => false, 'schedule' => $scheduleMessages, 'rooms' => []]);
        }

        $minutes = \Carbon\Carbon::parse($startTime)->diffInMinutes(\Carbon\Carbon::parse($endTime));

        if ($minutes < config('booking.min_booking_minutes')) {
            $scheduleMessages[] = 'Bookings must be at least '.config('booking.min_booking_minutes').' minutes long.';
        }

        $dateTo = $data['booking_date_to'] ?? $dateFrom;
        $labIds = $data['lab_ids'] ?? [];
        $roomMessages = [];

        if ($type === 'csl') {
            $rules = config('booking.csl');

            if ($rules['weekdays_only'] && $this->rangeIncludesWeekend($dateFrom, $dateTo)) {
                $scheduleMessages[] = 'CSL bookings are only available on weekdays.';
            }

            $advanceViolation = $this->cslAdvanceNoticeViolation(\Carbon\Carbon::parse($dateFrom), $rules['advance_working_days']);

            if ($advanceViolation) {
                $scheduleMessages[] = $advanceViolation;
            }

            if ($labIds) {
                $bufferViolation = $this->cslBufferConflict(collect($labIds), $dateFrom, $startTime, $endTime, $rules['buffer_minutes']);

                if ($bufferViolation) {
                    $scheduleMessages[] = $bufferViolation;
                }

                if ($this->blockedRoomLabIds(collect($labIds), $dateFrom, $dateTo, $startTime, $endTime)->isNotEmpty()) {
                    $scheduleMessages[] = 'A selected CSL room is blocked by the admin for an overlapping time slot.';
                }
            }
        }

        if ($labIds) {
            $selectedLabs = Lab::whereIn('id', $labIds)->get();
            $weekendViolation = $this->weekendClosedRoomsViolation($selectedLabs, $dateFrom, $dateTo);

            if ($weekendViolation) {
                $scheduleMessages[] = $weekendViolation;
            }

            if ($type === 'equipment') {
                if ($selectedLabs->pluck('building')->unique()->count() > 1) {
                    $roomMessages[] = 'All selected rooms must be from the same building.';
                }

                $roomMessages = array_merge($roomMessages, $this->roomOnlyConflicts($selectedLabs, $dateFrom, $dateTo, $startTime, $endTime));
            }
        }

        $selectedEquipment = $this->parseEquipmentSelections($data['equipment_names'] ?? []);

        if ($selectedEquipment) {
            $equipmentRows = $this->loadEquipmentRows($selectedEquipment);
            $roomMessages = array_merge($roomMessages, $this->equipmentConflicts($selectedEquipment, $dateFrom, $dateTo, $startTime, $endTime, $equipmentRows));
        }

        $scheduleMessages = array_values(array_unique($scheduleMessages));
        $roomMessages = array_values(array_unique($roomMessages));

        return response()->json([
            'ok' => empty($scheduleMessages) && empty($roomMessages),
            'schedule' => $scheduleMessages,
            'rooms' => $roomMessages,
        ]);
    }

    /**
     * Pharma lab dropdown options for a given date/time/pax count: only labs
     * with enough remaining (unbooked) capacity are returned.
     */
    public function pharmaEligibleLabs(Request $request)
    {
        $data = $request->validate([
            'booking_date_from' => ['required', 'date'],
            'booking_date_to' => ['nullable', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'pax_count' => ['required', 'integer', 'min:1'],
        ]);

        $dateFrom = $data['booking_date_from'];
        $dateTo = $data['booking_date_to'] ?? $dateFrom;

        $labs = Lab::query()
            ->with(['equipment' => fn ($query) => $query->orderBy('sort_order')])
            ->where('lab_type', 'pharma')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $conflictingEquipmentKeys = $this->conflictingEquipmentKeys($labs->pluck('id'), $dateFrom, $dateTo, $data['start_time'], $data['end_time']);

        $result = [];

        foreach ($labs as $lab) {
            $code = $this->derivePharmaCode($lab);

            if (! $code) {
                continue;
            }

            $remaining = $lab->capacity > 0
                ? $this->pharmaRemainingCapacity($lab, $dateFrom, $dateTo, $data['start_time'], $data['end_time'])
                : PHP_INT_MAX;

            // Even with remaining pax capacity, a lab with nothing left to use
            // (every item already booked by someone else) isn't actually usable.
            $fullyEquipped = $lab->equipment->isNotEmpty()
                && $lab->equipment->every(fn ($item) => $conflictingEquipmentKeys->contains($lab->id.'::'.$item->equipment_name));

            $result[] = [
                'id' => $lab->id,
                'name' => $lab->name,
                'capacity' => $lab->capacity,
                'remaining' => $lab->capacity > 0 ? $remaining : null,
                'eligible' => $remaining >= $data['pax_count'] && ! $fullyEquipped,
                'fully_equipped' => $fullyEquipped,
                'equipment' => $lab->equipment->map(fn ($item) => [
                    'equipment_name' => $item->equipment_name,
                    'booked' => $conflictingEquipmentKeys->contains($lab->id.'::'.$item->equipment_name),
                ]),
            ];
        }

        return response()->json(['labs' => $result]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['rooms.lab.equipment', 'equipment', 'students', 'processedBy', 'applicantUser']);

        return view('bookings.show', compact('booking'));
    }

    public function lookup(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $bookings = collect();

        if ($query !== '') {
            $bookings = Booking::query()
                ->with(['rooms.lab', 'equipment'])
                ->where('ref', $query)
                ->orWhere('applicant_email', $query)
                ->orWhere('applicant_id', $query)
                ->orderByDesc('id')
                ->get();
        }

        return view('bookings.lookup', compact('query', 'bookings'));
    }

    /**
     * Applies an inclusive date-range + exclusive time-range overlap filter
     * to a bookings query (shared by every conflict check in this controller).
     */
    private function isOverlapping($query, string $dateFrom, string $dateTo, string $startTime, string $endTime)
    {
        return $query->where('booking_date_from', '<=', $dateTo)
            ->where('booking_date_to', '>=', $dateFrom)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);
    }

    /**
     * Weekends are open per room, not globally: a room with weekends_allowed = 0
     * (e.g. Lab Level 2, MDL 3, Instrumentation Room, Thermal Analysis Room)
     * can't be booked on a Saturday or Sunday. Checks the whole date range,
     * since an extended booking can run over a weekend without starting on one.
     */
    private function weekendClosedRoomsViolation($labs, string $dateFrom, string $dateTo): ?string
    {
        $closedRooms = $labs->where('weekends_allowed', false);

        if ($closedRooms->isEmpty() || ! $this->rangeIncludesWeekend($dateFrom, $dateTo)) {
            return null;
        }

        $names = $closedRooms->pluck('name');

        return $names->implode(', ').($names->count() === 1 ? ' is' : ' are')
            .' only available on weekdays. Please choose a weekday or remove '.($names->count() === 1 ? 'this room' : 'these rooms').'.';
    }

    private function rangeIncludesWeekend(string $dateFrom, string $dateTo): bool
    {
        $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $end = \Carbon\Carbon::parse($dateTo)->startOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        // Any span of 7+ days covers a weekend; only shorter ones need walking.
        if ($start->diffInDays($end) >= 6) {
            return true;
        }

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekend()) {
                return true;
            }
        }

        return false;
    }

    private function cslAdvanceNoticeViolation(\Carbon\Carbon $bookingDate, int $advanceWorkingDays): ?string
    {
        $earliestAllowed = now()->startOfDay();

        for ($i = 0; $i < $advanceWorkingDays;) {
            $earliestAllowed->addDay();
            if (! $earliestAllowed->isWeekend()) {
                $i++;
            }
        }

        if ($bookingDate->lt($earliestAllowed)) {
            return 'CSL bookings must be made at least '.$advanceWorkingDays.' working day(s) in advance.';
        }

        return null;
    }

    /**
     * Rooms a CSL booking may reserve, given its discipline. Package-based
     * disciplines (Surgical, BCC Surgery, BCC Medicine, IPE) are booked as a
     * whole set: every room mapped to the discipline is reserved and whatever
     * the form submitted is ignored. Every other discipline books the selected
     * subset of its rooms, so anything outside that set is rejected.
     *
     * @param  array<int, mixed>  $selectedLabIds
     * @return array<int, int>
     */
    private function cslDisciplineLabIds(string $discipline, array $selectedLabIds): array
    {
        $roomsByDiscipline = config('booking.csl_discipline_rooms');

        if (! isset($roomsByDiscipline[$discipline])) {
            throw ValidationException::withMessages([
                'csl_discipline' => 'Please select a valid discipline.',
            ]);
        }

        $allowedLabIds = Lab::where('lab_type', 'csl')
            ->where('status', 'active')
            ->whereIn('name', $roomsByDiscipline[$discipline])
            ->pluck('id');

        if ($allowedLabIds->isEmpty()) {
            throw ValidationException::withMessages([
                'lab_ids' => 'No active CSL room is currently available for '.$discipline.'.',
            ]);
        }

        if (in_array($discipline, config('booking.csl_package_disciplines'), true)) {
            return $allowedLabIds->all();
        }

        $selected = collect($selectedLabIds)->map(fn ($id) => (int) $id)->unique()->values();

        if ($selected->isEmpty()) {
            throw ValidationException::withMessages([
                'lab_ids' => 'Please select at least one CSL room for this session.',
            ]);
        }

        if ($selected->diff($allowedLabIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'lab_ids' => 'One or more selected rooms are not available for the '.$discipline.' discipline.',
            ]);
        }

        return $selected->all();
    }

    private function cslBufferConflict($labIds, string $date, string $startTime, string $endTime, int $bufferMinutes): ?string
    {
        $bufferStart = \Carbon\Carbon::parse($startTime)->subMinutes($bufferMinutes)->format('H:i');
        $bufferEnd = \Carbon\Carbon::parse($endTime)->addMinutes($bufferMinutes)->format('H:i');

        $conflictingLabIds = BookingRoom::whereIn('lab_id', $labIds)
            ->whereHas('booking', function ($q) use ($date, $bufferStart, $bufferEnd) {
                $q->whereNotIn('status', ['rejected', 'cancelled'])
                    ->where('booking_date_from', $date)
                    ->where('start_time', '<', $bufferEnd)
                    ->where('end_time', '>', $bufferStart);
            })
            ->pluck('lab_id')
            ->unique();

        if ($conflictingLabIds->isEmpty()) {
            return null;
        }

        $names = Lab::whereIn('id', $conflictingLabIds)->orderBy('id')->pluck('name');

        return $names->implode(', ').($names->count() === 1 ? ' is' : ' are')
            .' already booked within the required '.$bufferMinutes.'-minute buffer window. Please choose another time or room.';
    }

    /**
     * Room-only labs (no equipment tied to them, e.g. Cell Culture Room 1) are
     * exclusive: once booked, nobody else can use that room in the same slot.
     * Equipment-based rooms are never checked here — they're shared, and only
     * the specific equipment item selected becomes unavailable (see
     * equipmentConflicts()).
     */
    private function roomOnlyConflicts($labs, string $dateFrom, string $dateTo, string $startTime, string $endTime): array
    {
        $roomOnlyLabs = $labs->where('is_room_only', true);
        $conflictingIds = $this->conflictingRoomOnlyLabIds($roomOnlyLabs->pluck('id'), $dateFrom, $dateTo, $startTime, $endTime);

        $messages = [];

        foreach ($roomOnlyLabs as $lab) {
            if ($conflictingIds->contains($lab->id)) {
                $messages[] = $lab->name.' is already booked for an overlapping time slot (this room is booked as a whole, not by equipment).';
            }
        }

        return $messages;
    }

    /**
     * Room-only labs (from the given set) with an overlapping, non-rejected/
     * cancelled BookingRoom for the slot — shared by roomOnlyConflicts() and
     * the live equipmentAvailability() snapshot.
     */
    private function conflictingRoomOnlyLabIds($labIds, string $dateFrom, string $dateTo, string $startTime, string $endTime)
    {
        $labIds = collect($labIds);

        if ($labIds->isEmpty()) {
            return collect();
        }

        $booked = BookingRoom::whereIn('lab_id', $labIds)
            ->whereHas('booking', function ($q) use ($dateFrom, $dateTo, $startTime, $endTime) {
                $q->whereNotIn('status', ['rejected', 'cancelled']);
                $this->isOverlapping($q, $dateFrom, $dateTo, $startTime, $endTime);
            })
            ->pluck('lab_id');

        // Admin time blocks reserve a whole room too — treat a blocked room as
        // unavailable, same as a booked one.
        return $booked->merge($this->blockedRoomLabIds($labIds, $dateFrom, $dateTo, $startTime, $endTime))
            ->unique()
            ->values();
    }

    /**
     * Equipment-based rooms are booked by equipment, not by room — so a room
     * with equipment attached must have at least one item selected from it.
     */
    private function equipmentRequiredForRoom($labs, array $selectedEquipment): ?string
    {
        $roomsNeedingEquipment = $labs->where('is_room_only', false)->pluck('id');

        if ($roomsNeedingEquipment->isEmpty()) {
            return null;
        }

        $labsWithEquipment = LabEquipment::whereIn('lab_id', $roomsNeedingEquipment)->pluck('lab_id')->unique();
        $selectedLabIds = collect($selectedEquipment)->pluck('lab_id')->unique();

        foreach ($labsWithEquipment as $labId) {
            if (! $selectedLabIds->contains($labId)) {
                $lab = $labs->firstWhere('id', $labId);

                return 'Please select at least one equipment item for '.$lab->name.' — you are booking the equipment, not the room.';
            }
        }

        return null;
    }

    /**
     * Equipment-based rooms let the applicant borrow equipment from a
     * non-primary lab too (see BookingEquipment.is_alt_lab) — but only within
     * the same building as the primary room. Rejects otherwise, and rejects
     * any equipment referencing a lab that isn't an active research lab.
     */
    private function equipmentLabsValid(Lab $primaryLab, array $selectedEquipment): ?string
    {
        $labIds = collect($selectedEquipment)->pluck('lab_id')->unique();

        if ($labIds->isEmpty()) {
            return null;
        }

        $labs = Lab::whereIn('id', $labIds)->where('lab_type', 'research')->where('status', 'active')->get()->keyBy('id');

        foreach ($labIds as $labId) {
            if (! $labs->has($labId)) {
                return 'Selected equipment references an invalid or inactive lab.';
            }

            if ($labs[$labId]->building !== $primaryLab->building) {
                return 'Equipment must be from the same building as your selected room.';
            }
        }

        return null;
    }

    /**
     * Pharma labs' names already embed their short code parenthetically
     * (e.g. "Chemistry Lab (CL)") — derive the CL/MDLP/PL1/PL2 code from
     * that instead of a separate room_code column. Returns null if the
     * name doesn't end in a recognised "(CODE)" suffix.
     */
    private function derivePharmaCode(Lab $lab): ?string
    {
        if (! preg_match('/\(([A-Za-z0-9]+)\)\s*$/', $lab->name, $matches)) {
            return null;
        }

        $code = strtoupper($matches[1]);

        return in_array($code, ['CL', 'MDLP', 'PL1', 'PL2'], true) ? $code : null;
    }

    /**
     * Parses "lab_id::equipment_name" composite checkbox values into pairs,
     * so each selected item keeps track of which room it actually belongs to.
     */
    private function parseEquipmentSelections(array $rawValues): array
    {
        $selected = [];

        foreach (array_filter($rawValues) as $raw) {
            [$labIdRaw, $equipmentName] = array_pad(explode('::', $raw, 2), 2, null);

            if ($labIdRaw === null || $equipmentName === null || $equipmentName === '') {
                continue;
            }

            $selected[] = ['lab_id' => (int) $labIdRaw, 'equipment_name' => $equipmentName];
        }

        return $selected;
    }

    private function loadEquipmentRows(array $selectedEquipment)
    {
        $labIds = array_unique(array_column($selectedEquipment, 'lab_id'));
        $names = array_unique(array_column($selectedEquipment, 'equipment_name'));

        return LabEquipment::whereIn('lab_id', $labIds)
            ->whereIn('equipment_name', $names)
            ->get()
            ->keyBy(fn ($row) => $row->lab_id.'::'.$row->equipment_name);
    }

    /**
     * Cannot double-book the same equipment item in the same room for an
     * overlapping slot, and any item-specific special conditions must be met.
     */
    private function equipmentConflicts(array $selectedEquipment, string $dateFrom, string $dateTo, string $startTime, string $endTime, $equipmentRows): array
    {
        $messages = [];
        $labIds = collect($selectedEquipment)->pluck('lab_id')->unique();
        $conflictingKeys = $this->conflictingEquipmentKeys($labIds, $dateFrom, $dateTo, $startTime, $endTime);

        foreach ($selectedEquipment as $sel) {
            if ($conflictingKeys->contains($sel['lab_id'].'::'.$sel['equipment_name'])) {
                $messages[] = $sel['equipment_name'].' is already booked for an overlapping time slot.';

                continue;
            }

            $equipmentRow = $equipmentRows->get($sel['lab_id'].'::'.$sel['equipment_name']);

            if (! $equipmentRow || $equipmentRow->special_conditions_note === '') {
                continue;
            }

            $rules = EquipmentConditions::parse($equipmentRow->special_conditions_note);
            $violations = EquipmentConditions::violations($rules, $dateFrom, $startTime, $endTime);

            if ($rules['buffer_days']) {
                $bufferConflict = BookingEquipment::where('equipment_name', $equipmentRow->equipment_name)
                    ->where('lab_id', $equipmentRow->lab_id)
                    ->whereHas('booking', function ($q) use ($dateFrom, $rules) {
                        $q->whereNotIn('status', ['rejected', 'cancelled'])
                            ->whereBetween('booking_date_from', [
                                now()->parse($dateFrom)->subDays($rules['buffer_days']),
                                now()->parse($dateFrom)->addDays($rules['buffer_days']),
                            ]);
                    })
                    ->exists();

                if ($bufferConflict) {
                    $violations[] = 'requires a '.$rules['buffer_days'].'-day gap before/after another booking of this equipment.';
                }
            }

            if ($violations) {
                $messages[] = $equipmentRow->equipment_name.' '.implode(' ', $violations);
            }
        }

        return $messages;
    }

    /**
     * "lab_id::equipment_name" keys (from the given labs) with an overlapping,
     * non-rejected/cancelled BookingEquipment for the slot — shared by
     * equipmentConflicts() and the live equipmentAvailability() snapshot.
     */
    private function conflictingEquipmentKeys($labIds, string $dateFrom, string $dateTo, string $startTime, string $endTime)
    {
        $labIds = collect($labIds);

        if ($labIds->isEmpty()) {
            return collect();
        }

        $booked = BookingEquipment::whereIn('lab_id', $labIds)
            ->whereHas('booking', function ($q) use ($dateFrom, $dateTo, $startTime, $endTime) {
                $q->whereNotIn('status', ['rejected', 'cancelled']);
                $this->isOverlapping($q, $dateFrom, $dateTo, $startTime, $endTime);
            })
            ->get()
            ->map(fn ($row) => $row->lab_id.'::'.$row->equipment_name)
            ->toBase();

        // Admin time blocks can reserve specific equipment (or a whole room, in
        // which case all its equipment) — fold those in so a blocked item can't
        // be booked either.
        return $booked->merge($this->blockedEquipmentKeys($labIds, $dateFrom, $dateTo, $startTime, $endTime))
            ->unique()
            ->values();
    }

    /**
     * Admin time blocks whose time range overlaps the requested slot AND whose
     * date (or a weekly/biweekly recurrence of it) falls inside the booking's
     * date range. These reserve rooms/equipment just like a booking does.
     */
    private function overlappingBlocks(string $dateFrom, string $dateTo, string $startTime, string $endTime)
    {
        return TimeBlock::query()
            ->whereTime('start_time', '<', $endTime)
            ->whereTime('end_time', '>', $startTime)
            ->get()
            ->filter(function (TimeBlock $block) use ($dateFrom, $dateTo) {
                foreach (BookingCalendar::recurDates($block->block_date, $block->recurring) as $date) {
                    if ($date >= $dateFrom && $date <= $dateTo) {
                        return true;
                    }
                }

                return false;
            });
    }

    /**
     * Lab ids (from the given set) whose room is reserved by an overlapping
     * admin time block — blocks store rooms by name, so map name → id.
     */
    private function blockedRoomLabIds($labIds, string $dateFrom, string $dateTo, string $startTime, string $endTime)
    {
        $labIds = collect($labIds);

        if ($labIds->isEmpty()) {
            return collect();
        }

        $blockedRoomNames = $this->overlappingBlocks($dateFrom, $dateTo, $startTime, $endTime)
            ->flatMap(fn (TimeBlock $block) => $block->rooms ?? [])
            ->unique();

        if ($blockedRoomNames->isEmpty()) {
            return collect();
        }

        return Lab::whereIn('id', $labIds)
            ->whereIn('name', $blockedRoomNames)
            ->pluck('id');
    }

    /**
     * "lab_id::equipment_name" keys (from the given labs) reserved by an
     * overlapping admin time block. A block that names specific equipment only
     * reserves those items; a block that names a room without any equipment
     * reserves every item in that room.
     */
    private function blockedEquipmentKeys($labIds, string $dateFrom, string $dateTo, string $startTime, string $endTime)
    {
        $labIds = collect($labIds);

        if ($labIds->isEmpty()) {
            return collect();
        }

        $blocks = $this->overlappingBlocks($dateFrom, $dateTo, $startTime, $endTime);

        if ($blocks->isEmpty()) {
            return collect();
        }

        $labsByName = Lab::with('equipment')->whereIn('id', $labIds)->get()->keyBy('name');
        $keys = collect();

        foreach ($blocks as $block) {
            // Specific equipment the block reserved, grouped by room name.
            $specificByRoom = collect($block->equipment ?? [])
                ->map(fn ($entry) => explode('::', $entry, 2))
                ->filter(fn ($parts) => count($parts) === 2)
                ->groupBy(fn ($parts) => $parts[0])
                ->map(fn ($group) => $group->map(fn ($parts) => $parts[1])->all());

            foreach (($block->rooms ?? []) as $roomName) {
                $lab = $labsByName->get($roomName);

                if (! $lab) {
                    continue;
                }

                if (isset($specificByRoom[$roomName])) {
                    foreach ($specificByRoom[$roomName] as $equipmentName) {
                        $keys->push($lab->id.'::'.$equipmentName);
                    }
                } else {
                    foreach ($lab->equipment as $item) {
                        $keys->push($lab->id.'::'.$item->equipment_name);
                    }
                }
            }
        }

        return $keys->unique()->values();
    }

    /**
     * Live per-lab availability snapshot for every active research lab, used
     * to grey out already-booked rooms/equipment as soon as the applicant
     * fills in a date/time — independent of the building filter (all research
     * labs are returned; the frontend applies its own display filter). A
     * room-only lab is "booked" if it has an overlapping booking; an
     * equipment-bearing lab is "booked" only once every one of its items is.
     */
    public function equipmentAvailability(Request $request)
    {
        $data = $request->validate([
            'booking_date_from' => ['required', 'date'],
            'booking_date_to' => ['nullable', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ]);

        $dateFrom = $data['booking_date_from'];
        $dateTo = $data['booking_date_to'] ?? $dateFrom;
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];

        $labs = Lab::query()
            ->with(['equipment' => fn ($query) => $query->orderBy('sort_order')])
            ->where('lab_type', 'research')
            ->where('status', 'active')
            ->get();

        $roomOnlyLabs = $labs->where('is_room_only', true);
        $equipmentLabs = $labs->where('is_room_only', false);

        $conflictingRoomIds = $this->conflictingRoomOnlyLabIds($roomOnlyLabs->pluck('id'), $dateFrom, $dateTo, $startTime, $endTime);
        $conflictingEquipmentKeys = $this->conflictingEquipmentKeys($equipmentLabs->pluck('id'), $dateFrom, $dateTo, $startTime, $endTime);

        // Rooms that don't open on weekends are unavailable for the whole slot,
        // regardless of what else is booked — surfaced separately from "booked"
        // so the form can explain why the room is greyed out.
        $weekendClosed = $this->rangeIncludesWeekend($dateFrom, $dateTo);

        $result = $labs->map(function ($lab) use ($conflictingRoomIds, $conflictingEquipmentKeys, $weekendClosed) {
            $weekendBlocked = $weekendClosed && ! $lab->weekends_allowed;

            if ($lab->is_room_only) {
                return [
                    'lab_id' => $lab->id,
                    'is_room_only' => true,
                    'weekend_blocked' => $weekendBlocked,
                    'booked' => $weekendBlocked || $conflictingRoomIds->contains($lab->id),
                    'equipment' => [],
                ];
            }

            // Equipment in a weekend-closed room isn't "booked" — the room is
            // shut. The form greys it out from weekend_blocked instead, so the
            // two reasons stay distinguishable in the UI.
            $equipment = $lab->equipment->map(fn ($item) => [
                'equipment_name' => $item->equipment_name,
                'booked' => $conflictingEquipmentKeys->contains($lab->id.'::'.$item->equipment_name),
            ]);

            return [
                'lab_id' => $lab->id,
                'is_room_only' => false,
                'weekend_blocked' => $weekendBlocked,
                'booked' => $weekendBlocked || ($equipment->isNotEmpty() && $equipment->every(fn ($item) => $item['booked'])),
                'equipment' => $equipment->values(),
            ];
        });

        return response()->json(['labs' => $result->values()]);
    }

    /**
     * Pharma rooms can host multiple groups at once up to capacity, so
     * "remaining capacity" is the room's capacity minus pax already booked
     * by other overlapping, non-rejected/cancelled bookings that reserved
     * this lab — via booking_rooms, not just the single "primary" lab, since
     * a booking can reserve several pharma labs at once (equipment can't
     * leave its room, so using a lab's equipment reserves that lab's
     * capacity too, same headcount as every other lab in the booking).
     */
    private function pharmaRemainingCapacity(Lab $lab, string $dateFrom, string $dateTo, string $startTime, string $endTime): int
    {
        $booked = Booking::where('lab_type', 'pharma')
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->whereHas('rooms', fn ($q) => $q->where('lab_id', $lab->id))
            ->where(function ($q) use ($dateFrom, $dateTo, $startTime, $endTime) {
                $this->isOverlapping($q, $dateFrom, $dateTo, $startTime, $endTime);
            })
            ->sum('pharma_num_students');

        return max(0, $lab->capacity - $booked);
    }
}
