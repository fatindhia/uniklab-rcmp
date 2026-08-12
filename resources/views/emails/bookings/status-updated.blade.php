@extends('emails.layout')

@php
    $labels = [
        'approved' => ['Booking approved', "Great news — your booking has been approved."],
        'rejected' => ['Booking rejected', 'Unfortunately your booking request was not approved.'],
        'cancelled' => ['Booking cancelled', 'Your booking has been cancelled by an admin.'],
    ];
    [$heading, $lede] = $labels[$status] ?? ['Booking updated', 'Your booking status has changed.'];
@endphp

@section('subject', $heading . ' — ' . $booking->ref)

@section('content')
    <h1>{{ $heading }}</h1>
    <p class="lede">Hi {{ $booking->applicant_name }}, {{ $lede }}</p>

    <span class="pill pill--{{ $status }}">{{ ucfirst($status) }}</span>

    <div class="rows">
        <div class="row"><span class="lbl">Reference</span><span class="val">{{ $booking->ref }}</span></div>
        <div class="row"><span class="lbl">Lab type</span><span class="val">{{ ucfirst($booking->lab_type) }}</span></div>
        <div class="row"><span class="lbl">Room(s)</span><span class="val">{{ $booking->rooms->pluck('lab.name')->filter()->join(', ') ?: '—' }}</span></div>
        <div class="row"><span class="lbl">Date</span><span class="val">{{ $booking->date_range_label }}</span></div>
        <div class="row"><span class="lbl">Time</span><span class="val">{{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i') }}</span></div>
    </div>

    @if ($roomReassignedFrom)
        <p class="lede" style="margin-top:18px; margin-bottom:0;">Note: your room was reassigned during review, from <strong>{{ $roomReassignedFrom }}</strong> to <strong>{{ $booking->rooms->pluck('lab.name')->filter()->join(', ') ?: 'a new room' }}</strong>.</p>
    @endif

    @if ($booking->admin_remark)
        <div class="note">{{ $booking->admin_remark }}</div>
    @endif

    <a class="btn" href="{{ route('bookings.show', $booking) }}">View your ticket</a>
@endsection
