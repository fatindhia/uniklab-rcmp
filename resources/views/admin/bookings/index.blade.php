@extends('layouts.admin')

@section('content')
    <style>
        .tab-bar a[data-type="research"].active { color: var(--type-research); border-bottom-color: var(--type-research); background: color-mix(in srgb, var(--type-research) 12%, transparent); }
        .tab-bar a[data-type="csl"].active { color: var(--type-csl); border-bottom-color: var(--type-csl); background: color-mix(in srgb, var(--type-csl) 12%, transparent); }
        .tab-bar a[data-type="pharma"].active { color: var(--type-pharma); border-bottom-color: var(--type-pharma); background: color-mix(in srgb, var(--type-pharma) 12%, transparent); }
        .tab-bar a[data-type="research"].active .tab-count { background: var(--type-research); color: #fff; }
        .tab-bar a[data-type="csl"].active .tab-count { background: var(--type-csl); color: #fff; }
        .tab-bar a[data-type="pharma"].active .tab-count { background: var(--type-pharma); color: #fff; }

        .rb-hint { display: flex; align-items: center; gap: 10px; padding: 13px 18px; margin: 18px 0; font-size: 0.85rem; color: var(--muted); border-left: 3px solid var(--brand); }
        .rb-hint a { color: var(--brand-2); font-weight: 700; text-decoration: none; }
        .rb-hint a:hover { text-decoration: underline; }
    </style>

    <div class="tab-bar">
        <a href="{{ route('admin.bookings.index') }}" class="{{ $tab === 'all' ? 'active' : '' }}">All Pending <span class="tab-count tab-count--warn">{{ $stats['pending'] }}</span></a>
        <a href="{{ route('admin.bookings.research') }}" data-type="research" class="{{ $tab === 'research' ? 'active' : '' }}">🧪 Research <span class="tab-count tab-count--warn">{{ $stats['pending_research'] }}</span></a>
        <a href="{{ route('admin.bookings.csl') }}" data-type="csl" class="{{ $tab === 'csl' ? 'active' : '' }}">🏥 CSL <span class="tab-count tab-count--warn">{{ $stats['pending_csl'] }}</span></a>
        <a href="{{ route('admin.bookings.pharma') }}" data-type="pharma" class="{{ $tab === 'pharma' ? 'active' : '' }}">⚗️ Pharma <span class="tab-count tab-count--warn">{{ $stats['pending_pharma'] }}</span></a>
    </div>

    @if (in_array($tab, ['research', 'csl', 'pharma']))
        <div class="section-title">
            <h2>{{ $pageTitle }}</h2>
        </div>
    @endif

    <div class="card rb-hint">
        <span>⏳</span>
        <span>Showing <strong>pending</strong> bookings awaiting your decision plus <strong>upcoming approved</strong> bookings, soonest date first. Past, rejected &amp; cancelled bookings are in <a href="{{ route('admin.history') }}">History</a>.</span>
    </div>

    @include('admin.bookings._table', ['bookings' => $bookings, 'showType' => $showType, 'tab' => $tab])

    <div style="margin-top:16px;">{{ $bookings->links() }}</div>

    @include('admin.bookings._modal')

    @if ($openBooking ?? null)
        {{-- Deep link from the "new booking ticket" admin email (?open=REF) —
             the target may not be on this page/tab, so add it to the map
             separately and open it straight away. --}}
        @include('admin.bookings._map', ['mapBookings' => collect([$openBooking])])
        <script>
            openBookingModal(@json($openBooking->ref));
            const bmUrl = new URL(location.href);
            bmUrl.searchParams.delete('open');
            history.replaceState(null, '', bmUrl.pathname + bmUrl.search + bmUrl.hash);
        </script>
    @endif
@endsection
