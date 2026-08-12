@extends('layouts.app')

@section('content')
    <section class="panel hero-copy">
        <div class="eyebrow">Book a lab</div>
        <h1 class="title">Booking entry will be moved here next</h1>
        <p class="lede" style="margin-top:18px; max-width: 70ch;">
            The form scaffold exists, but the booking workflow is still being migrated into Laravel. For now this page
            shows the live lab inventory that will feed the future booking form.
        </p>
    </section>

    <div class="section-title">
        <div>
            <h2>Available labs</h2>
            <p>Grouped from the real labs table and ready for the booking form later.</p>
        </div>
    </div>

    @forelse ($labsByType as $type => $labs)
        <div class="section-title">
            <div>
                <h2>{{ ucfirst($type) }} labs</h2>
                <p>{{ $labs->count() }} active lab(s)</p>
            </div>
        </div>

        <section class="grid-3">
            @foreach ($labs as $lab)
                <article class="summary-card" style="padding:18px;">
                    <strong>{{ $lab->name }}</strong>
                    <span>{{ strtoupper($lab->lab_block) }} · Room {{ $lab->room_code }}</span>
                    <div class="muted" style="margin-top:10px; line-height:1.6;">{{ $lab->location ?: 'No location stored yet.' }}</div>
                </article>
            @endforeach
        </section>
    @empty
        <div class="panel empty">
            No active labs were found in the database.
        </div>
    @endforelse
@endsection