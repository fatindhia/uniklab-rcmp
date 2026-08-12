<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LabController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Manage Labs';

        $labs = Lab::query()
            ->with(['equipment' => fn ($q) => $q->orderBy('sort_order')])
            ->withCount('equipment')
            ->when($request->query('q'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->query('type'), fn ($q, $type) => $q->where('lab_type', $type))
            ->orderBy('lab_type')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => Lab::count(),
            'active' => Lab::where('status', 'active')->count(),
            'maintenance' => Lab::where('status', 'maintenance')->count(),
            'inactive' => Lab::where('status', 'inactive')->count(),
        ];

        return view('admin.labs.index', compact('pageTitle', 'labs', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'lab_type' => ['required', 'in:research,csl,pharma'],
            'location' => ['nullable', 'string', 'max:200'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,maintenance,inactive'],
            'is_room_only' => ['nullable', 'boolean'],
            'weekends_allowed' => ['nullable', 'boolean'],
            'requires_special_conditions' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'equipment_names' => ['array'],
            'equipment_names.*' => ['nullable', 'string', 'max:300'],
            'equipment_notes' => ['array'],
            'equipment_notes.*' => ['nullable', 'string', 'max:500'],
        ]);

        $data['is_room_only'] = $request->boolean('is_room_only');
        $data['weekends_allowed'] = $request->boolean('weekends_allowed');
        $data['requires_special_conditions'] = $request->boolean('requires_special_conditions');
        // Both columns are NOT NULL with a sensible default, but the form
        // submits them empty when unused — leaving a blank capacity or
        // location as null would fail the insert outright. Capacity 0 is what
        // the UI already reads as "not pax-limited".
        $data['capacity'] = $data['capacity'] ?? 0;
        $data['location'] = $data['location'] ?? '';

        $lab = Lab::create($data);

        $this->syncEquipment($lab, $request);

        ActivityLog::record('labs', 'created', (string) $lab->id, $lab->name);

        Cache::forget('admin_labs_by_type');

        return back()->with('status', 'Lab created.');
    }

    public function update(Request $request, Lab $lab)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:200'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,maintenance,inactive'],
            'is_room_only' => ['nullable', 'boolean'],
            'weekends_allowed' => ['nullable', 'boolean'],
            'requires_special_conditions' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'equipment_names' => ['array'],
            'equipment_names.*' => ['nullable', 'string', 'max:300'],
            'equipment_notes' => ['array'],
            'equipment_notes.*' => ['nullable', 'string', 'max:500'],
        ]);

        $data['is_room_only'] = $request->boolean('is_room_only');
        $data['weekends_allowed'] = $request->boolean('weekends_allowed');
        $data['requires_special_conditions'] = $request->boolean('requires_special_conditions');
        // Both columns are NOT NULL with a sensible default, but the form
        // submits them empty when unused — leaving a blank capacity or
        // location as null would fail the insert outright. Capacity 0 is what
        // the UI already reads as "not pax-limited".
        $data['capacity'] = $data['capacity'] ?? 0;
        $data['location'] = $data['location'] ?? '';

        $before = $lab->getOriginal();
        $lab->update($data);
        $changes = ActivityLog::diff($before, $lab->getChanges());

        // Equipment lives in its own table, so a rewrite there is invisible to
        // the model diff — surface it as one explicit field.
        $equipmentBefore = $lab->equipment()->orderBy('sort_order')->pluck('equipment_name')->all();
        $this->syncEquipment($lab, $request);
        $equipmentAfter = $lab->equipment()->orderBy('sort_order')->pluck('equipment_name')->all();

        if ($equipmentBefore !== $equipmentAfter) {
            $changes['equipment'] = [
                $equipmentBefore ? implode(', ', $equipmentBefore) : '—',
                $equipmentAfter ? implode(', ', $equipmentAfter) : '—',
            ];
        }

        if ($changes) {
            ActivityLog::record('labs', 'updated', (string) $lab->id, $lab->name, $changes);
        }

        Cache::forget('admin_labs_by_type');

        return back()->with('status', 'Lab updated.');
    }

    /**
     * Replace a lab's equipment list with the rows submitted from the modal.
     * Room-only labs carry no bookable equipment, so their list is cleared.
     * Bookings reference equipment by name (not FK), so replacing rows is safe.
     */
    protected function syncEquipment(Lab $lab, Request $request): void
    {
        $lab->equipment()->delete();

        if ($lab->is_room_only) {
            return;
        }

        $names = $request->input('equipment_names', []);
        $notes = $request->input('equipment_notes', []);
        $sort = 0;

        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $lab->equipment()->create([
                'equipment_name' => $name,
                'special_conditions_note' => trim((string) ($notes[$i] ?? '')),
                'sort_order' => $sort++,
            ]);
        }
    }

    public function destroy(Lab $lab)
    {
        if ($lab->bookingRooms()->exists()) {
            return back()->withErrors(['lab' => 'This lab has existing bookings and cannot be removed. Set it to inactive instead.']);
        }

        $lab->equipment()->delete();
        $lab->delete();

        ActivityLog::record('labs', 'deleted', (string) $lab->id, $lab->name);

        Cache::forget('admin_labs_by_type');

        return back()->with('status', 'Lab removed.');
    }
}
