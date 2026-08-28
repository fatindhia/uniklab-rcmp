<?php

namespace App\Support;

use App\Models\Booking;

/**
 * Shape consumed by window.BOOKING_MAP in the admin booking detail modal
 * (resources/views/admin/bookings/_modal.blade.php) — built once here so the
 * initial page-load map (_map.blade.php) and any AJAX response that needs to
 * redraw the modal in place (e.g. reassignRoom) stay in sync.
 */
class BookingModalPayload
{
    public static function build(Booking $booking): array
    {
        $equipmentByLab = $booking->equipment->groupBy('lab_id');
        $primaryRoom = $booking->rooms->firstWhere('is_primary', true);

        return [
            'ref' => $booking->ref,
            'status' => $booking->status,
            'lab_type' => $booking->lab_type,
            'applicant_name' => $booking->applicant_name,
            'applicant_id' => $booking->applicant_id,
            'applicant_email' => $booking->applicant_email,
            'applicant_phone' => $booking->applicant_phone,
            'applicant_department' => $booking->applicant_department,
            'applicant_role' => $booking->applicant_role,
            'applicant_group' => $booking->applicant_group,
            'date' => $booking->date_range_label,
            'start' => optional($booking->start_time)->format('H:i'),
            'end' => optional($booking->end_time)->format('H:i'),
            // The two above are the raw clock values; this is what they
            // actually mean once the booking's span is taken into account —
            // "Continuous run · 24h" rather than a window that repeats.
            'time_label' => $booking->time_range_label,
            'is_continuous' => (bool) $booking->is_continuous,
            'building' => $primaryRoom?->lab?->building,
            'purpose' => $booking->purpose,
            'applicant_remark' => $booking->applicant_remark,
            'admin_remark' => $booking->admin_remark,
            'processed_by' => $booking->processedBy?->full_name,
            'processed_at' => optional($booking->processed_at)->format('d/m/Y, H:i'),
            'submitted_at' => optional($booking->submitted_at)->format('d/m/Y, H:i'),
            'primary_lab_id' => $primaryRoom?->lab_id,
            'rooms' => $booking->rooms->map(fn ($r) => $r->lab?->name)->filter()->implode(', '),
            'research_pax' => $booking->research_pax,
            'has_special_conditions' => (bool) $booking->has_special_conditions,
            'csl_session_type' => $booking->csl_session_type,
            'csl_discipline' => $booking->csl_discipline,
            'csl_procedure' => $booking->csl_procedure,
            'csl_num_students' => $booking->csl_num_students,
            'pharma_primary_lab' => $booking->pharma_primary_lab,
            'pharma_num_students' => $booking->pharma_num_students,
            'roomsDetail' => $booking->rooms->map(fn ($r) => [
                'name' => $r->lab?->name ?? 'Unknown room',
                'primary' => (bool) $r->is_primary,
                'equipment' => $equipmentByLab->get($r->lab_id, collect())->pluck('equipment_name')->values(),
            ])->values(),
            'students' => $booking->students->map(fn ($s) => [
                'name' => $s->student_name,
                'id' => $s->student_id,
                'year' => $s->student_year,
            ])->values(),
            // Public holidays this booking lands on, so an administrator
            // reviewing it can see the labs are normally closed that day. Covers
            // the whole span, not just the two end dates: an extended booking
            // can straddle a holiday without starting or ending on one.
            'holidays' => collect(PublicHolidays::between(
                optional($booking->booking_date_from)->format('Y-m-d') ?? '',
                optional($booking->booking_date_to ?? $booking->booking_date_from)->format('Y-m-d') ?? '',
            ))->map(fn ($holiday, $date) => [
                'date' => $date,
                'name' => $holiday['name'],
                'subject_to_change' => $holiday['subject_to_change'],
            ])->values(),
            'audit' => $booking->auditLogs->map(fn ($a) => [
                'action' => ucfirst($a->action),
                'by' => $a->performedBy?->full_name ?? 'Applicant',
                'at' => optional($a->created_at)->format('d/m/Y H:i'),
                'detail' => $a->detail,
                'type' => $a->action,
                'late' => (bool) $a->is_late,
            ]),
        ];
    }
}
