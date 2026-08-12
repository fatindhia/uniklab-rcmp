{{-- Research / CSL / pharma split shown under a report KPI figure.
     Expects $counts as ['research' => int, 'csl' => int, 'pharma' => int].
     Optionally takes $late in the same shape (decided-outcome cards only):
     each type that has late responses gets a nested line under it, and a type
     with none gets nothing at all. --}}
@php $late = $late ?? []; @endphp

<div class="kpi-split">
    @foreach (['research' => 'Research', 'csl' => 'CSL', 'pharma' => 'Pharma'] as $type => $label)
        <span class="{{ ($counts[$type] ?? 0) === 0 ? 'is-zero' : '' }}">
            <span class="type-dot type-dot--{{ $type }}"></span>{{ $label }}<b>{{ number_format($counts[$type] ?? 0) }}</b>
        </span>
        @if (($late[$type] ?? 0) > 0)
            <span class="ks-late">↳ late response<b>{{ number_format($late[$type]) }}</b></span>
        @endif
    @endforeach
</div>
