@extends('layouts.app')

@section('content')
    <section class="panel hero-copy">
        <div class="eyebrow">{{ $pageTitle }}</div>
        <h1 class="title">{{ $pageTitle }} from the database</h1>
        <p class="lede" style="margin-top:18px; max-width: 70ch;">
            This list is driven by the live bookings table. If the database is empty, the page will stay empty instead of
            inventing placeholder records.
        </p>
    </section>

    <div class="section-title">
        <div>
            <h2>All bookings</h2>
            <p>Paginated booking records with applicant, lab, status, and schedule details.</p>
        </div>
        <a class="button button-secondary" href="{{ route('bookings.lookup') }}">Check a booking</a>
    </div>

    <section class="stack">
        @forelse ($bookings as $booking)
            <article class="booking-row" style="padding:18px;">
                <div class="grid-2" style="align-items:start;">
                    <div>
                        <div class="badge">{{ $booking->ref }}</div>
                        <h3 style="margin:12px 0 6px; font-size:1.06rem;">{{ $booking->applicant_name }}</h3>
                        <p class="muted" style="margin-bottom:12px;">
                            {{ ucfirst($booking->lab_type) }} · {{ strtoupper($booking->lab_block) }} · {{ $booking->applicant_email }}
                        </p>
                        <p class="muted" style="margin:0; line-height:1.65;">{{ $booking->purpose }}</p>
                    </div>

                    <div class="stack" style="gap:10px;">
                        <div class="summary-card"><strong>{{ $booking->booking_date_from?->format('d M Y') ?? $booking->booking_date_from }}</strong><span>Date</span></div>
                        <div class="summary-card"><strong>{{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i') }}</strong><span>Time</span></div>
                    </div>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:14px;">
                    <span class="badge" style="background: {{ $booking->status === 'approved' ? 'rgba(47,138,82,.15)' : ($booking->status === 'rejected' ? 'rgba(212,52,42,.15)' : 'rgba(160,124,31,.15)') }}; color: inherit;">
                        {{ ucfirst($booking->status) }}
                    </span>
                    <a class="button button-secondary" href="{{ route('bookings.show', $booking) }}">Open</a>
                </div>
            </article>
        @empty
            <div class="panel empty">
                There are no booking rows in the database yet.
            </div>
        @endforelse
    </section>

    <div style="margin-top:18px;">
        {{ $bookings->links() }}
    </div>
@endsection