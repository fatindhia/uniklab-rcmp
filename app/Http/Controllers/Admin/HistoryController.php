<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingAuditLog;
use App\Models\TimeBlock;

class HistoryController extends Controller
{
    protected const PURPOSE_LABELS = [
        'class' => 'Class',
        'practical' => 'Practical',
        'maintenance' => 'Maintenance',
        'reserved' => 'Reserved',
        'exam' => 'Exam/OSCE',
        'event' => 'Event',
    ];

    public function index()
    {
        $pageTitle = 'History';

        // History is an audit trail: every logged booking action (submitted,
        // approved, rejected, cancelled, reassigned) plus every block created,
        // as its own timeline entry — newest first.
        $logs = BookingAuditLog::with([
            'booking.rooms.lab', 'booking.equipment', 'booking.students',
            'booking.auditLogs.performedBy', 'performedBy',
        ])->get();

        $blocks = TimeBlock::with('creator')->get();

        $events = $logs->map(function ($log) {
            $b = $log->booking;
            $equipmentByLab = $b ? $b->equipment->groupBy('lab_id') : collect();

            return [
                'kind' => 'booking',
                'action' => $log->action,
                'at' => $log->created_at,
                'by' => $log->performedBy?->full_name ?? ($log->action === 'created' ? 'Applicant' : 'System'),
                'detail' => $log->detail,
                'late' => (bool) $log->is_late,
                'ref' => $b?->ref ?? '—',
                'title' => $b?->applicant_name ?? 'Deleted booking',
                'type' => $b?->lab_type ?? 'research',
                'status' => $b?->status,
                'booking_date' => $b?->booking_date_from?->format('d/m/Y'),
                'time' => $b ? $b->start_time->format('H:i').'–'.$b->end_time->format('H:i') : null,
                'sub' => $b ? $b->rooms->map(fn ($r) => $r->lab?->name)->filter()->implode(', ') : '',
                'applicant_email' => $b?->applicant_email,
                'applicant_id' => $b?->applicant_id,
                'applicant_phone' => $b?->applicant_phone,
                'applicant_department' => $b?->applicant_department,
                'applicant_role' => $b?->applicant_role,
                'admin_remark' => $b?->admin_remark,
                'rooms_equipment' => $b ? $b->rooms->map(fn ($r) => [
                    'name' => $r->lab?->name ?? 'Unknown room',
                    'equipment' => $equipmentByLab->get($r->lab_id, collect())->pluck('equipment_name')->all(),
                ])->values()->all() : [],
                'students' => $b ? $b->students->map(fn ($s) => $s->student_name.' ('.$s->student_id.')')->all() : [],
                // The booking's whole action history — each action keeps its own
                // remark, so an approve note isn't lost when a later cancel
                // overwrites booking.admin_remark. Oldest first, to read as a story.
                'audit_trail' => $b ? $b->auditLogs->sortBy('created_at')->map(fn ($l) => [
                    'action' => $l->action,
                    'detail' => $l->detail,
                    'by' => $l->performedBy?->full_name ?? ($l->action === 'created' ? 'Applicant' : 'System'),
                    'at' => $l->created_at?->format('d/m/Y, H:i'),
                    'late' => (bool) $l->is_late,
                ])->values()->all() : [],
            ];
        })->concat($blocks->map(fn ($bl) => [
            'kind' => 'block',
            'action' => 'blocked',
            'at' => $bl->created_at,
            'by' => $bl->creator?->full_name ?? 'Admin',
            'detail' => $bl->notes,
            'ref' => $bl->id,
            'title' => $bl->title,
            'type' => $bl->lab_type,
            'status' => 'blocked',
            'booking_date' => $bl->block_date->format('d/m/Y'),
            'time' => substr((string) $bl->start_time, 0, 5).'–'.substr((string) $bl->end_time, 0, 5),
            'sub' => implode(', ', $bl->rooms ?? []),
            'purpose' => self::PURPOSE_LABELS[$bl->purpose] ?? $bl->purpose,
            'pic' => $bl->pic,
            'recurring' => $bl->recurring,
            'notes' => $bl->notes,
            'equipment' => array_map(fn ($e) => \Illuminate\Support\Str::afterLast($e, '::'), $bl->equipment ?? []),
        ]))
            ->sortByDesc(fn ($e) => optional($e['at'])->timestamp ?? 0)
            ->map(function ($e) {
                // Week bucket (Mon–Sun) for the week-by-week pager.
                $e['week'] = $e['at'] ? $e['at']->copy()->startOfWeek()->format('Y-m-d') : 'na';

                return $e;
            })
            ->values();

        // Distinct weeks, newest first (events are already sorted desc, so
        // groupBy preserves that order), each with a label + event count.
        $weeks = $events->groupBy('week')->map(function ($group, $weekStart) {
            if ($weekStart === 'na') {
                return ['key' => 'na', 'label' => 'Undated', 'count' => $group->count()];
            }

            $start = \Illuminate\Support\Carbon::parse($weekStart);

            return [
                'key' => $weekStart,
                'label' => $start->format('d/m/Y').' – '.$start->copy()->endOfWeek()->format('d/m/Y'),
                'count' => $group->count(),
            ];
        })->values();

        $stats = [
            'total' => $events->count(),
            'submitted' => $logs->where('action', 'created')->count(),
            'approved' => $logs->where('action', 'approved')->count(),
            'rejected' => $logs->where('action', 'rejected')->count(),
            // Decisions logged after the booking date had already gone by —
            // the same flag the timeline marks with a red "Late response".
            'late' => $logs->where('is_late', true)->count(),
            'blocked' => $blocks->count(),
        ];

        return view('admin.history', compact('pageTitle', 'events', 'stats', 'weeks'));
    }
}
