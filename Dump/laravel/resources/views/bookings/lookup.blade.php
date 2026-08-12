@extends('layouts.app')

@section('content')
    <section class="panel hero-copy">
        <div class="eyebrow">Check booking</div>
        <h1 class="title">Find a booking by reference or applicant data</h1>
        <p class="lede" style="margin-top:18px; max-width: 70ch;">
            This replaces the old lookup page with a database search. Enter a booking reference, applicant email, or staff ID.
        </p>

        <form method="get" action="{{ route('bookings.lookup') }}" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:22px;">
            <input
                type="text"
                name="q"
                value="{{ $query }}"
                placeholder="BK-001 or applicant email"
                style="flex:1 1 280px; min-height:46px; padding:0 14px; border-radius:14px; border:1px solid var(--line); background:#fff; color:var(--ink);"
            >
            <button class="button button-primary" type="submit">Search</button>
        </form>
    </section>

    @if ($query !== '' && ! $booking)
        <div class="panel empty" style="margin-top:16px;">No booking matched "{{ $query }}".</div>
    @endif

    @if ($booking)
        <div class="section-title">
            <div>
                <h2>Result</h2>
                <p>One live booking matched your search.</p>
            </div>
            <a class="button button-secondary" href="{{ route('bookings.show', $booking) }}">Open detail</a>
        </div>

        <article class="booking-row" style="padding:18px;">
            <div class="grid-2" style="align-items:start;">
                <div>
                    <div class="badge">{{ $booking->ref }}</div>
                    <h3 style="margin:12px 0 6px; font-size:1.06rem;">{{ $booking->applicant_name }}</h3>
                    <p class="muted" style="margin-bottom:12px;">{{ $booking->applicant_email }}</p>
                    <p class="muted" style="margin:0;">{{ $booking->purpose }}</p>
                </div>
                <div class="stack" style="gap:10px;">
                    <div class="summary-card"><strong>{{ ucfirst($booking->status) }}</strong><span>Status</span></div>
                    <div class="summary-card"><strong>{{ $booking->booking_date_from?->format('d M Y') ?? $booking->booking_date_from }}</strong><span>Date</span></div>
                </div>
            </div>
        </article>
    @endif
@endsection