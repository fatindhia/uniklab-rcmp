@extends('emails.layout')

@section('subject', 'Room reassigned — ' . $booking->ref)

@section('content')
    <h1>Room reassigned</h1>
    <p class="lede">Hi {{ $booking->applicant_name }}, the room for your booking has been changed by an admin.</p>

    <div class="rows">
        <div class="row"><span class="lbl">Reference</span><span class="val">{{ $booking->ref }}</span></div>
        <div class="row"><span class="lbl">Previous room</span><span class="val">{{ $oldRoomName }}</span></div>
        <div class="row"><span class="lbl">New room</span><span class="val">{{ $newRoomName }}</span></div>
        <div class="row"><span class="lbl">Date</span><span class="val">{{ $booking->date_range_label }}</span></div>
        <div class="row"><span class="lbl">Time</span><span class="val">{{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i') }}</span></div>
    </div>

    <p class="lede" style="margin-top:22px;">Everything else about your booking stays the same.</p>

    <a class="btn" href="{{ route('bookings.show', $booking) }}">View your ticket</a>
@endsection
