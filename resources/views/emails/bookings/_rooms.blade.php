{{-- Rooms with the equipment booked in each, mirroring the "Labs & equipment"
     section of resources/views/bookings/show.blade.php. Shared by every booking
     email so the applicant and the lab staff see the same breakdown. --}}
@php
    $equipmentByLab = $booking->equipment->groupBy('lab_id');
    $roomLabIds = $booking->rooms->pluck('lab_id');
    // Equipment can sit in a lab that isn't one of the booked rooms (is_alt_lab),
    // so anything left over is listed separately rather than silently dropped.
    $strayEquipment = $booking->equipment->whereNotIn('lab_id', $roomLabIds)->groupBy('lab_id');
@endphp

<div class="rooms">
    <div class="rooms-head">Rooms &amp; equipment</div>

    @forelse ($booking->rooms as $room)
        <div class="room">
            <div class="room-name">
                {{ $room->lab?->name ?? 'Unknown room' }}
                @if ($room->is_primary)<span class="tag">Primary</span>@endif
            </div>
            @php $items = $equipmentByLab->get($room->lab_id, collect()); @endphp
            @if ($items->isNotEmpty())
                @foreach ($items as $equipment)
                    <div class="equip">&bull; {{ $equipment->equipment_name }}</div>
                @endforeach
            @else
                <div class="equip equip--none">No equipment booked in this room</div>
            @endif
        </div>
    @empty
        <div class="room"><div class="equip equip--none">No rooms linked to this booking.</div></div>
    @endforelse

    @foreach ($strayEquipment as $labId => $items)
        <div class="room">
            <div class="room-name">
                {{ $items->first()->lab?->name ?? 'Other lab' }}<span class="tag">Equipment only</span>
            </div>
            @foreach ($items as $equipment)
                <div class="equip">&bull; {{ $equipment->equipment_name }}</div>
            @endforeach
        </div>
    @endforeach
</div>
