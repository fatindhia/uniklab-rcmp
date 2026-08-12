@extends('layouts.admin')

@section('content')
    <style>
        .cal-page-head { padding: 20px 24px; margin-bottom: 18px; }
        .cal-page-head h2 { margin: 0 0 4px; font-size: 1.2rem; }
    </style>

    <div class="panel cal-page-head">
        <h2>📅 Booking Calendar</h2>
        <p class="muted" style="margin:0; font-size:.88rem;">Every approved and pending booking, plus scheduled blocks, in one month view.</p>
    </div>

    <section class="kpi-grid" style="margin-bottom:18px;">
        <div class="kpi"><strong>{{ number_format($stats['total']) }}</strong><span class="kpi-label">Total</span></div>
        <div class="kpi kpi--ok"><strong>{{ number_format($stats['approved']) }}</strong><span class="kpi-label">Approved</span></div>
        <div class="kpi kpi--warn"><strong>{{ number_format($stats['pending']) }}</strong><span class="kpi-label">Pending</span></div>
        <div class="kpi kpi--bad"><strong>{{ number_format($stats['blocked']) }}</strong><span class="kpi-label">Blocked</span></div>
    </section>

    @include('admin.partials.calendar-widget', ['calPrefix' => 'fc', 'calendarEvents' => $calendarEvents, 'withFilter' => true])
@endsection
