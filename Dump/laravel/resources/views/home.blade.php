@extends('layouts.app')

@section('content')
    <section class="panel hero-copy">
        <div class="eyebrow">Live Laravel dashboard</div>
        <h1 class="title">Book labs and equipment from the <span style="color:var(--brand)">real database</span>.</h1>
        <p class="lede" style="margin-top:18px; max-width: 70ch;">
            The demo arrays are gone here. This page now reads from the existing booking tables, so counts and
            upcoming entries reflect actual records or an empty state if the database has nothing yet.
        </p>

        <div class="summary-grid">
            <div class="summary-card">
                <strong>{{ number_format($bookingCount) }}</strong>
                <span>Total bookings</span>
            </div>
            <div class="summary-card">
                <strong>{{ number_format($activeLabCount) }}</strong>
                <span>Active labs</span>
            </div>
            <div class="summary-card">
                <strong>{{ number_format($timeBlockCount) }}</strong>
                <span>Blocked time slots</span>
            </div>
        </div>
    </section>

    <div class="section-title">
        <div>
            <h2>Upcoming bookings</h2>
            <p>Live rows from the bookings table, including booking reference, applicant, lab type, and schedule.</p>
        </div>
        <a class="button button-secondary" href="{{ route('bookings.index') }}">View all bookings</a>
    </div>

    <section class="stack">
        @forelse ($upcomingBookings as $booking)
            <article class="booking-row" style="padding:18px;">
                <div class="grid-2" style="align-items:start;">
                    <div>
                        <div class="badge">{{ $booking->ref }}</div>
                        <h3 style="margin:12px 0 6px; font-size:1.06rem;">{{ $booking->applicant_name }}</h3>
                        <p class="muted" style="margin-bottom:12px;">
                            {{ ucfirst($booking->lab_type) }} · {{ strtoupper($booking->lab_block) }} · {{ $booking->applicant_role }}
                        </p>

                        <p class="muted" style="margin-bottom:0; line-height:1.65;">
                            {{ $booking->purpose }}
                        </p>
                    </div>

                    <div class="stack" style="gap:10px;">
                        <div class="summary-card">
                            <strong>{{ $booking->booking_date_from?->format('d M Y') ?? $booking->booking_date_from }}</strong>
                            <span>Booking date</span>
                        </div>
                        <div class="summary-card">
                            <strong>{{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i') }}</strong>
                            <span>Time slot</span>
                        </div>
                    </div>
                </div>

                <div class="stack" style="margin-top:14px; gap:10px;">
                    @if ($booking->rooms->isNotEmpty())
                        <div class="muted"><strong>Rooms:</strong> {{ $booking->rooms->map(fn ($room) => $room->lab?->name ?? 'Unknown')->implode(', ') }}</div>
                    @endif

                    @if ($booking->equipment->isNotEmpty())
                        <div class="muted"><strong>Equipment:</strong> {{ $booking->equipment->pluck('equipment_name')->implode(', ') }}</div>
                    @endif

                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:4px;">
                        <span class="badge" style="background: {{ $booking->status === 'approved' ? 'rgba(47,138,82,.15)' : ($booking->status === 'rejected' ? 'rgba(212,52,42,.15)' : 'rgba(160,124,31,.15)') }}; color: inherit;">
                            {{ ucfirst($booking->status) }}
                        </span>
                        <a class="button button-secondary" href="{{ route('bookings.show', $booking) }}">Open booking</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="panel empty">
                No bookings are stored yet. Once records exist in the database, they will appear here automatically.
            </div>
        @endforelse
    </section>

    <div class="section-title">
        <div>
            <h2>Database snapshot</h2>
            <p>Counts are grouped from the live schema so the dashboard stays tied to real records.</p>
        </div>
    </div>

    <section class="grid-3">
        <article class="summary-card">
            <strong>{{ number_format((int) ($labCounts['research'] ?? 0)) }}</strong>
            <span>Research labs</span>
        </article>
        <article class="summary-card">
            <strong>{{ number_format((int) ($labCounts['csl'] ?? 0)) }}</strong>
            <span>CSL labs</span>
        </article>
        <article class="summary-card">
            <strong>{{ number_format((int) ($labCounts['pharma'] ?? 0)) }}</strong>
            <span>Pharma labs</span>
        </article>
    </section>

    <div class="section-title">
        <div>
            <h2>Booking statuses</h2>
            <p>Current distribution of booking states from the live booking table.</p>
        </div>
    </div>

    <section class="grid-3">
        <article class="summary-card"><strong>{{ number_format((int) ($statusCounts['pending'] ?? 0)) }}</strong><span>Pending</span></article>
        <article class="summary-card"><strong>{{ number_format((int) ($statusCounts['approved'] ?? 0)) }}</strong><span>Approved</span></article>
        <article class="summary-card"><strong>{{ number_format((int) ($statusCounts['rejected'] ?? 0)) }}</strong><span>Rejected</span></article>
    </section>

    <div class="section-title">
        <div>
            <h2>Time blocks</h2>
            <p>Upcoming blocked slots or admin-managed schedule items from the live schedule table.</p>
        </div>
    </div>

    <section class="stack">
        @forelse ($recentBlocks as $block)
            <article class="block-row" style="padding:18px;">
                <div class="grid-2" style="align-items:start;">
                    <div>
                        <div class="badge">{{ strtoupper($block->lab_type) }} · {{ $block->category }}</div>
                        <h3 style="margin:12px 0 6px; font-size:1.02rem;">{{ $block->title }}</h3>
                        <p class="muted" style="margin:0;">{{ $block->pic ?: 'No PIC specified' }}</p>
                    </div>
                    <div class="stack" style="gap:10px;">
                        <div class="summary-card"><strong>{{ $block->block_date?->format('d M Y') ?? $block->block_date }}</strong><span>Block date</span></div>
                        <div class="summary-card"><strong>{{ $block->start_time }} - {{ $block->end_time }}</strong><span>Time window</span></div>
                    </div>
                </div>
            </article>
        @empty
            <div class="panel empty">
                No time blocks are stored yet. This section will populate automatically when the admin schedule table has rows.
            </div>
        @endforelse
    </section>
@endsection