@php
    $withFilter = $withFilter ?? true;
@endphp

<section class="pc-layout">
    <div class="pc-card">
        <div class="pc-header">
            <button type="button" class="pc-nav" id="{{ $calPrefix }}-prev" aria-label="Previous month">&#8249;</button>
            <button type="button" class="pc-today" id="{{ $calPrefix }}-today">Today</button>
            <button type="button" class="pc-nav" id="{{ $calPrefix }}-next" aria-label="Next month">&#8250;</button>
            <span class="pc-label" id="{{ $calPrefix }}-label">—</span>
            @if ($withFilter)
                <select class="pc-filter" id="{{ $calPrefix }}-filter">
                    <option value="">All categories</option>
                    <option value="research">Research</option>
                    <option value="csl">CSL</option>
                    <option value="pharma">Pharma</option>
                </select>
            @endif
        </div>
        <div class="pc-weekdays"><div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div></div>
        <div class="pc-grid" id="{{ $calPrefix }}-grid"></div>
        <div class="pc-legend">
            <span><span class="pc-dot pc-dot--research"></span>Research</span>
            <span><span class="pc-dot pc-dot--csl"></span>CSL</span>
            <span><span class="pc-dot pc-dot--pharma"></span>Pharma</span>
            <span><span class="pc-dot pc-dot--block"></span>Blocked</span>
            <span><span class="pc-dot pc-dot--pending"></span>Pending</span>
        </div>
    </div>

    <div class="pc-card">
        <div class="pc-detail-header">
            <strong id="{{ $calPrefix }}-detail-date">—</strong>
        </div>
        <div class="pc-detail-body" id="{{ $calPrefix }}-detail-body"></div>
    </div>
</section>

<script>
    initAdminPcCalendar('{{ $calPrefix }}', @json($calendarEvents), { withFilter: @json($withFilter) });
</script>
