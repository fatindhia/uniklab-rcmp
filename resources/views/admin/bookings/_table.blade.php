@include('admin.bookings._map', ['mapBookings' => $bookings->getCollection()])

@php
    $curSort = request()->query('sort', 'date');
    $curDir = request()->query('dir') === 'desc' ? 'desc' : 'asc';
    $sortHead = function ($key, $label) use ($curSort, $curDir) {
        $active = $curSort === $key;
        $nextDir = $active && $curDir === 'asc' ? 'desc' : 'asc';
        $arrow = $active ? ($curDir === 'asc' ? '▲' : '▼') : '⇅';
        // 'open' (the admin-email deep link) is one-shot — drop it so sorting
        // or paging doesn't keep reopening the same booking's modal.
        $url = request()->fullUrlWithQuery(['sort' => $key, 'dir' => $nextDir, 'page' => 1, 'open' => null]);
        $color = $active ? 'var(--brand-2)' : 'inherit';
        return '<a href="'.e($url).'" style="display:inline-flex; align-items:center; gap:5px; color:'.$color.'; text-decoration:none;">'
            .e($label).'<span style="font-size:.7em; opacity:'.($active ? '1' : '.4').';">'.$arrow.'</span></a>';
    };
    $accent = ['research' => 'var(--type-research)', 'csl' => 'var(--type-csl)', 'pharma' => 'var(--type-pharma)'][$tab ?? null] ?? null;
@endphp

<style>
    .rb-table th, .rb-table td { padding: 13px 16px; }
    .rb-table thead th { background: rgba(49, 43, 44, 0.02); }
    .rb-table tbody tr { transition: background .12s ease; }
    .rb-table tbody tr:hover { background: rgba(125, 145, 148, .05); }
    .rb-ref { font-family: var(--mono); font-size: .76rem; font-weight: 700; color: var(--brand-2); background: rgba(125, 145, 148, .1); padding: 3px 8px; border-radius: 7px; white-space: nowrap; }
    .rb-type { display: inline-flex; align-items: center; gap: 6px; font-size: .84rem; }
    .rb-table-wrap { overflow: auto; }

    .rb-cards { display: none; gap: 12px; }
    .rb-card { background: var(--panel-strong); border: 1px solid var(--line); border-radius: var(--radius); padding: 16px; box-shadow: var(--shadow-sm); }
    .rb-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
    .rb-card-meta { display: grid; gap: 5px; font-size: .84rem; color: var(--muted); margin-bottom: 12px; }
    .rb-card-meta strong { color: var(--ink); font-weight: 700; }
    .rb-card-foot { display: flex; justify-content: flex-end; }

    @media (max-width: 860px) {
        .rb-table-wrap { display: none; }
        .rb-cards { display: grid; }
    }
</style>

<div class="card rb-table-wrap" style="{{ $accent ? 'border-top:3px solid '.$accent.';' : '' }}">
    <table class="rb-table">
        <thead>
            <tr>
                <th>{!! $sortHead('ref', 'Ref') !!}</th>
                <th>{!! $sortHead('applicant', 'Applicant') !!}</th>
                @if ($showType) <th>{!! $sortHead('type', 'Type') !!}</th> @endif
                <th>{!! $sortHead('date', 'Date') !!}</th>
                <th>Time</th>
                <th>Rooms</th>
                <th>{!! $sortHead('status', 'Status') !!}</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bookings as $booking)
                <tr>
                    <td><span class="rb-ref">{{ $booking->ref }}</span></td>
                    <td>
                        <strong style="font-size:.86rem;">{{ $booking->applicant_name }}</strong><br>
                        <span class="muted" style="font-size:.74rem;">{{ $booking->applicant_id }}</span>
                    </td>
                    @if ($showType)
                        <td><span class="rb-type"><span class="type-dot type-dot--{{ $booking->lab_type }}"></span>{{ ucfirst($booking->lab_type) }}</span></td>
                    @endif
                    <td>{{ $booking->date_range_label }}</td>
                    <td>{{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</td>
                    <td>{{ $booking->rooms->map(fn ($r) => $r->lab?->name)->filter()->implode(', ') ?: '—' }}</td>
                    <td><span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                    <td>
                        <button type="button" class="button button-secondary" style="min-height:32px; padding:0 12px;" onclick="openBookingModal('{{ $booking->ref }}')">View</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $showType ? 8 : 7 }}" class="empty">🎉 Nothing to show — no pending or upcoming approved bookings.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="rb-cards">
    @forelse ($bookings as $booking)
        <article class="rb-card">
            <div class="rb-card-top">
                <div>
                    <span class="rb-ref">{{ $booking->ref }}</span>
                    <strong style="display:block; margin-top:6px; font-size:.94rem;">{{ $booking->applicant_name }}</strong>
                </div>
                <span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
            </div>
            <div class="rb-card-meta">
                @if ($showType)
                    <span><span class="type-dot type-dot--{{ $booking->lab_type }}"></span> <strong>{{ ucfirst($booking->lab_type) }}</strong></span>
                @endif
                <span><strong>Date:</strong> {{ $booking->date_range_label }}</span>
                <span><strong>Time:</strong> {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</span>
                <span><strong>Rooms:</strong> {{ $booking->rooms->map(fn ($r) => $r->lab?->name)->filter()->implode(', ') ?: '—' }}</span>
            </div>
            <div class="rb-card-foot">
                <button type="button" class="button button-secondary" style="min-height:36px; padding:0 16px;" onclick="openBookingModal('{{ $booking->ref }}')">View</button>
            </div>
        </article>
    @empty
        <div class="card empty">🎉 Nothing to show — no pending or upcoming approved bookings.</div>
    @endforelse
</div>
