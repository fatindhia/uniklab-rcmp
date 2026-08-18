@extends('emails.layout')

@section('subject', 'Booking submitted — ' . $booking->ref)

@section('content')
    <h1>Booking submitted</h1>
    <p class="lede">Hi {{ $booking->applicant_name }}, we've received your booking request. Here's your ticket.</p>

    <span class="pill pill--pending">Pending review</span>

    <div class="rows">
        <div class="row"><span class="lbl">Reference</span><span class="val">{{ $booking->ref }}</span></div>
        <div class="row"><span class="lbl">Lab type</span><span class="val">{{ ucfirst($booking->lab_type) }}</span></div>
        <div class="row"><span class="lbl">Date</span><span class="val">{{ $booking->date_range_label }}</span></div>
        <div class="row"><span class="lbl">Time</span><span class="val">{{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i') }}</span></div>
        <div class="row"><span class="lbl">Purpose</span><span class="val">{{ $booking->purpose }}</span></div>
    </div>

    @include('emails.bookings._rooms')

    <p class="lede" style="margin-top:22px;">We'll email you again once an admin reviews this request.</p>

    {{-- There is no self-service cancel: the applicant has to go through an
         admin, so the email has to say so and give them the reference to quote.
         The 24-hour rule matches the booking form's terms. --}}
    <div class="note">Need to change or cancel this booking? Please contact the lab administrator and quote your reference <strong>{{ $booking->ref }}</strong>. Cancellations should be made at least 24 hours before the booking starts.</div>

    <a class="btn" href="{{ route('bookings.show', $booking) }}">View your ticket</a>
@endsection
