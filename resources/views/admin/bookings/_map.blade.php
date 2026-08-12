{{-- Builds window.BOOKING_MAP for the booking detail modal.
     Expects $mapBookings — a collection of Booking models with
     rooms.lab, equipment, students, auditLogs.performedBy, processedBy loaded. --}}
@php
    $__bookingMap = ($mapBookings ?? collect())
        ->mapWithKeys(fn ($b) => [$b->ref => \App\Support\BookingModalPayload::build($b)]);
@endphp

<script>
    window.BOOKING_MAP = Object.assign(window.BOOKING_MAP || {}, @json($__bookingMap));
</script>
