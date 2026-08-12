@extends('layouts.app')

@section('content')
    <section class="panel hero-copy">
        <div class="eyebrow">Booking {{ $booking->ref }}</div>
        <h1 class="title">{{ $booking->applicant_name }}</h1>
        <p class="lede" style="margin-top:18px; max-width: 70ch;">
            Live booking details pulled from the database. This is the central detail view for a single booking record.
        </p>
    </section>

    <div class="section-title">
        <div>
            <h2>Booking details</h2>
            <p>Applicant data, schedule, rooms, equipment, and admin notes.</p>
        </div>
        <a class="button button-secondary" href="{{ route('bookings.index') }}">Back to bookings</a>
    </div>

    <section class="grid-2">
        <article class="summary-card" style="padding:18px;">
            <h3 style="margin-bottom:12px;">Core information</h3>
            <p class="muted" style="margin-bottom:8px;"><strong>Reference:</strong> {{ $booking->ref }}</p>
            <p class="muted" style="margin-bottom:8px;"><strong>Status:</strong> {{ ucfirst($booking->status) }}</p>
            <p class="muted" style="margin-bottom:8px;"><strong>Applicant:</strong> {{ $booking->applicant_name }} ({{ $booking->applicant_id }})</p>
            <p class="muted" style="margin-bottom:8px;"><strong>Email:</strong> {{ $booking->applicant_email }}</p>
            <p class="muted" style="margin-bottom:0;"><strong>Department:</strong> {{ $booking->applicant_department ?: 'Not provided' }}</p>
        </article>

        <article class="summary-card" style="padding:18px;">
            <h3 style="margin-bottom:12px;">Schedule</h3>
            <p class="muted" style="margin-bottom:8px;"><strong>Type:</strong> {{ ucfirst($booking->lab_type) }}</p>
            <p class="muted" style="margin-bottom:8px;"><strong>Block:</strong> {{ strtoupper($booking->lab_block) }}</p>
            <p class="muted" style="margin-bottom:8px;"><strong>Date:</strong> {{ $booking->booking_date_from?->format('d M Y') ?? $booking->booking_date_from }}</p>
            <p class="muted" style="margin-bottom:0;"><strong>Time:</strong> {{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i') }}</p>
        </article>
    </section>

    <div class="section-title">
        <div>
            <h2>Rooms and equipment</h2>
            <p>Relations are loaded from the live child tables.</p>
        </div>
    </div>

    <section class="grid-2">
        <article class="card" style="padding:18px;">
            <h3>Rooms</h3>
            @forelse ($booking->rooms as $room)
                <div style="padding:10px 0; border-top:1px solid var(--line);">
                    <strong>{{ $room->lab?->name ?? 'Unknown room' }}</strong>
                    <div class="muted">Primary: {{ $room->is_primary ? 'Yes' : 'No' }}</div>
                </div>
            @empty
                <div class="empty" style="text-align:left; padding:10px 0 0;">No room rows linked to this booking.</div>
            @endforelse
        </article>

        <article class="card" style="padding:18px;">
            <h3>Equipment</h3>
            @forelse ($booking->equipment as $equipment)
                <div style="padding:10px 0; border-top:1px solid var(--line);">
                    <strong>{{ $equipment->equipment_name }}</strong>
                    <div class="muted">{{ $equipment->lab?->name ?? 'Unknown lab' }}</div>
                </div>
            @empty
                <div class="empty" style="text-align:left; padding:10px 0 0;">No equipment rows linked to this booking.</div>
            @endforelse
        </article>
    </section>

    @if ($booking->students->isNotEmpty())
        <div class="section-title">
            <div>
                <h2>Students</h2>
                <p>Student rows from the live booking_students table.</p>
            </div>
        </div>

        <section class="card" style="padding:18px;">
            @foreach ($booking->students as $student)
                <div style="padding:10px 0; border-top:1px solid var(--line);">
                    <strong>{{ $student->student_name }}</strong>
                    <div class="muted">{{ $student->student_id }}{{ $student->student_year ? ' · Year ' . $student->student_year : '' }}</div>
                </div>
            @endforeach
        </section>
    @endif

    @if ($booking->admin_remark || $booking->applicant_remark)
        <div class="section-title">
            <div>
                <h2>Remarks</h2>
                <p>Notes captured with the booking.</p>
            </div>
        </div>

        <section class="grid-2">
            <article class="summary-card" style="padding:18px;">
                <h3 style="margin-bottom:8px;">Applicant remark</h3>
                <p class="muted" style="margin:0; white-space:pre-line;">{{ $booking->applicant_remark ?: 'None' }}</p>
            </article>
            <article class="summary-card" style="padding:18px;">
                <h3 style="margin-bottom:8px;">Admin remark</h3>
                <p class="muted" style="margin:0; white-space:pre-line;">{{ $booking->admin_remark ?: 'None' }}</p>
            </article>
        </section>
    @endif
@endsection