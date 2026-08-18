@extends('emails.layout')

@section('subject', 'Reminder: booking still pending — ' . $booking->ref)

@php
    $startsAt = $booking->starts_at;
    $hoursLeft = $startsAt ? (int) floor(now()->diffInHours($startsAt, false)) : null;
@endphp

@section('content')
    <h1>Booking still awaiting review</h1>
    <p class="lede">
        {{ $booking->ref }} starts
        @if ($hoursLeft !== null && $hoursLeft > 0)
            in about {{ $hoursLeft }} {{ \Illuminate\Support\Str::plural('hour', $hoursLeft) }}
        @else
            shortly
        @endif
        and has not been approved or rejected yet.
    </p>

    <span class="pill pill--pending">Pending review</span>

    <div class="rows">
        <div class="row"><span class="lbl">Reference</span><span class="val">{{ $booking->ref }}</span></div>
        <div class="row"><span class="lbl">Applicant</span><span class="val">{{ $booking->applicant_name }} ({{ $booking->applicant_role }})</span></div>
        <div class="row"><span class="lbl">Lab type</span><span class="val">{{ ucfirst($booking->lab_type) }}</span></div>
        <div class="row"><span class="lbl">Date</span><span class="val">{{ $booking->date_range_label }}</span></div>
        <div class="row"><span class="lbl">Time</span><span class="val">{{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i') }}</span></div>
        <div class="row"><span class="lbl">Submitted</span><span class="val">{{ $booking->submitted_at?->format('d/m/Y, H:i') ?? '—' }}</span></div>
        <div class="row"><span class="lbl">Purpose</span><span class="val">{{ $booking->purpose }}</span></div>
    </div>

    @include('emails.bookings._rooms')

    <a class="btn" href="{{ route('admin.bookings.pending', ['open' => $booking->ref]) }}">Review in admin panel</a>
@endsection
