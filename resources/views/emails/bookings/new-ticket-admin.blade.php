@extends('emails.layout')

@section('subject', 'New booking ticket — ' . $booking->ref)

@section('content')
    <h1>New booking ticket</h1>
    <p class="lede">A new booking request needs review.</p>

    <span class="pill pill--pending">Pending review</span>

    <div class="rows">
        <div class="row"><span class="lbl">Reference</span><span class="val">{{ $booking->ref }}</span></div>
        <div class="row"><span class="lbl">Applicant</span><span class="val">{{ $booking->applicant_name }} ({{ $booking->applicant_role }})</span></div>
        <div class="row"><span class="lbl">Lab type</span><span class="val">{{ ucfirst($booking->lab_type) }}</span></div>
        <div class="row"><span class="lbl">Room(s)</span><span class="val">{{ $booking->rooms->pluck('lab.name')->filter()->join(', ') ?: '—' }}</span></div>
        <div class="row"><span class="lbl">Date</span><span class="val">{{ $booking->date_range_label }}</span></div>
        <div class="row"><span class="lbl">Time</span><span class="val">{{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i') }}</span></div>
        @if ($booking->lab_type === 'csl')
            <div class="row"><span class="lbl">Discipline</span><span class="val">{{ $booking->csl_discipline ?: '—' }}</span></div>
            <div class="row"><span class="lbl">Procedure</span><span class="val" style="white-space:pre-line;">{{ $booking->csl_procedure ?: '—' }}</span></div>
        @endif
        <div class="row"><span class="lbl">Purpose</span><span class="val">{{ $booking->purpose }}</span></div>
    </div>

    <a class="btn" href="{{ route('admin.bookings.pending', ['open' => $booking->ref]) }}">Review in admin panel</a>
@endsection
