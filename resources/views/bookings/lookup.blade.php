@extends('layouts.app')

@section('content')
    <style>
        .lookup-hero { padding: 60px 0 44px; }
        .lookup-search { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 28px; max-width: 640px; }
        .lookup-search .input-shell { flex: 1 1 300px; position: relative; display: flex; align-items: center; }
        .lookup-search .input-shell svg { position: absolute; left: 18px; color: var(--muted); pointer-events: none; }
        .lookup-search input {
            width: 100%; min-height: 56px; padding: 0 18px 0 48px; border-radius: 999px; border: 1.5px solid var(--line);
            background: var(--panel-strong); color: var(--ink); font-size: 1rem; font-family: inherit;
            box-shadow: var(--shadow-sm); transition: border-color .15s ease, box-shadow .15s ease;
        }
        .lookup-search input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 4px rgba(125, 145, 148,.14); }
        .lookup-hints { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
        .lookup-hint { font-size: 0.78rem; color: var(--muted); font-weight: 600; padding: 6px 12px; border-radius: 999px; background: var(--panel-strong); border: 1px solid var(--line); }

        .results-band { padding: 0 0 64px; }

        /* ---- Desktop/tablet table ---- */
        .results-table-wrap { display: block; background: var(--panel-strong); border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-sm); }
        .results-table { width: 100%; border-collapse: collapse; }
        .results-table thead th {
            text-align: left; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); font-weight: 800;
            padding: 14px 20px; background: rgba(49, 43, 44, 0.02); border-bottom: 1px solid var(--line); position: sticky; top: 0;
        }
        .results-table tbody td { padding: 16px 20px; border-bottom: 1px solid var(--line); font-size: 0.9rem; vertical-align: middle; }
        .results-table tbody tr:last-child td { border-bottom: none; }
        .results-table tbody tr { transition: background .15s ease; }
        .results-table tbody tr:hover { background: rgba(125, 145, 148, 0.04); }
        /* The booking reference is what people came here for — make it the loudest
   thing in the row, not just another cell. */
        .results-table .ref-cell { padding-right: 8px; }
        .ticket-ref {
            display: inline-block; font-family: var(--mono); font-weight: 800; color: var(--brand-2);
            font-size: clamp(1.15rem, 2.2vw, 1.5rem); letter-spacing: 0.02em; line-height: 1.15;
        }
        .ticket-ref-label { display: block; font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); font-weight: 800; margin-bottom: 4px; }
        .results-table .name-cell strong { display: block; }
        .results-table .name-cell span { display: block; font-size: 0.78rem; color: var(--muted); margin-top: 2px; }
        .results-table .action-cell { text-align: right; }
        .results-table .action-cell a { color: var(--brand-2); text-decoration: none; font-weight: 700; font-size: 0.86rem; white-space: nowrap; }
        .results-table .action-cell a:hover { text-decoration: underline; }

        /* ---- Mobile cards ---- */
        .results-cards { display: none; gap: 14px; }
        .result-card { background: var(--panel-strong); border: 1px solid var(--line); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow-sm); }
        .result-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .result-card .ticket-ref { font-size: 1.4rem; }
        .result-card h3 { font-size: 1.02rem; margin: 6px 0 2px; }
        .result-card-meta { display: grid; gap: 6px; font-size: 0.86rem; color: var(--muted); margin-bottom: 12px; }
        .result-card-meta strong { color: var(--ink); font-weight: 700; }
        .result-card-foot { display: flex; justify-content: flex-end; }

        @media (max-width: 880px) {
            .results-table-wrap { display: none; }
            .results-cards { display: grid; }
        }

        .empty-state { text-align: center; padding: 48px 24px; background: var(--panel-strong); border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow-sm); }
        .empty-state .icon { width: 54px; height: 54px; border-radius: var(--radius-sm); display: grid; place-items: center; margin: 0 auto 16px; background: rgba(125, 145, 148,.1); color: var(--brand); }
        .empty-state h3 { margin: 0 0 6px; font-size: 1.1rem; }
        .empty-state p { margin: 0 auto; max-width: 42ch; color: var(--muted); }

        @media (max-width: 640px) { .lookup-hero { padding: 40px 0 32px; } }
    </style>

    <section class="band band--cream lookup-hero">
        <div class="band-inner">
            <div class="eyebrow">Check a booking</div>
            <h1 class="title" style="font-size:clamp(1.9rem,4vw,3rem);">Find your booking in one search</h1>
            <p class="lede" style="margin-top:16px; max-width: 62ch;">
                Enter your booking reference, email, or Staff / Student ID to see its current status.
            </p>

            <form method="get" action="{{ route('bookings.lookup') }}" class="lookup-search">
                <div class="input-shell">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="text" name="q" value="{{ $query }}" placeholder="e.g. BK-001, name@rcmp.edu.my, or Staff ID" autofocus>
                </div>
                <button class="button button-primary" type="submit" style="padding:0 28px; min-height:56px;">Search</button>
            </form>
            <div class="lookup-hints">
                <span class="lookup-hint">🔖 Booking reference</span>
                <span class="lookup-hint">✉️ Applicant email</span>
                <span class="lookup-hint">🪪 Staff / Student ID</span>
            </div>
        </div>
    </section>

    <section class="band band--cream results-band">
        <div class="band-inner">
            @if ($query !== '' && $bookings->isEmpty())
                <div class="empty-state fade-in">
                    <div class="icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M8 11h6"/></svg>
                    </div>
                    <h3>No booking found</h3>
                    <p>Nothing matched “{{ $query }}”. Double-check the reference or email, or <a href="{{ route('booking.create') }}" style="color:var(--brand); font-weight:700;">start a new booking</a>.</p>
                </div>
            @endif

            @if ($bookings->isNotEmpty())
                <div class="section-title fade-in">
                    <div>
                        <h2>{{ $bookings->count() === 1 ? "Here's your booking" : 'Here are your bookings' }}</h2>
                        <p>{{ $bookings->count() }} booking{{ $bookings->count() === 1 ? '' : 's' }} matched your search, newest first.</p>
                    </div>
                </div>

                <div class="results-table-wrap fade-in">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Ticket no.</th>
                                <th>Applicant</th>
                                <th>Lab type</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td class="ref-cell"><span class="ticket-ref">{{ $booking->ref }}</span></td>
                                    <td class="name-cell"><strong>{{ $booking->applicant_name }}</strong><span>{{ $booking->applicant_email }}</span></td>
                                    <td>{{ ucfirst($booking->lab_type) }}</td>
                                    <td>{{ $booking->date_range_label }}</td>
                                    <td><span class="status-pill status-pill--{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                                    <td class="action-cell"><a href="{{ route('bookings.show', $booking) }}">Open detail →</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Below 880px the table is hidden in favour of these cards. --}}
                <div class="results-cards fade-in">
                    @foreach ($bookings as $booking)
                        <div class="result-card">
                            <div class="result-card-top">
                                <div>
                                    <span class="ticket-ref-label">Ticket no.</span>
                                    <span class="ticket-ref">{{ $booking->ref }}</span>
                                </div>
                                <span class="status-pill status-pill--{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                            </div>
                            <h3>{{ $booking->applicant_name }}</h3>
                            <div class="result-card-meta">
                                <span>{{ $booking->applicant_email }}</span>
                                <span><strong>{{ ucfirst($booking->lab_type) }}</strong> · {{ $booking->date_range_label }}</span>
                            </div>
                            <div class="result-card-foot">
                                <a href="{{ route('bookings.show', $booking) }}" class="button button-secondary" style="min-height:38px; padding:0 16px; font-size:0.84rem;">Open detail →</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
