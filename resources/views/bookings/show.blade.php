@extends('layouts.app')

@section('content')
    <style>
        .detail-band { padding: 48px 0 64px; }
        .detail-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
        /* The ticket number is the thing people scan for — keep it the first
           and loudest element in the header. */
        .ticket-ref {
            font-family: var(--mono); font-weight: 800; color: var(--brand-2);
            font-size: clamp(1.5rem, 3.2vw, 2.1rem); letter-spacing: 0.02em; line-height: 1.1; margin-bottom: 6px;
        }

        .admin-note {
            display: flex; gap: 14px; align-items: flex-start; padding: 16px 18px; margin-top: 20px;
            border-radius: var(--radius-sm); border: 1px solid var(--line); border-left: 3px solid var(--accent); background: rgba(148, 128, 111, 0.07);
        }
        .admin-note .icon { font-size: 1.15rem; line-height: 1; flex-shrink: 0; }
        .admin-note strong { display: block; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--accent); margin-bottom: 4px; }
        .admin-note p { margin: 0; white-space: pre-line; line-height: 1.6; }
        .admin-note .meta { display: block; margin-top: 6px; font-size: 0.8rem; color: var(--muted); }

        .detail-section { padding: 24px 0; border-top: 1px solid var(--line); }
        .detail-section h3 { margin: 0 0 14px; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--brand-2); font-weight: 800; }
        .detail-rows { display: grid; gap: 12px; }
        .detail-row { display: flex; gap: 18px; align-items: baseline; }
        .detail-row .lbl { flex: 0 0 170px; color: var(--muted); font-weight: 600; font-size: 0.87rem; }
        .detail-row .val { font-weight: 700; word-break: break-word; }
        @media (max-width: 560px) { .detail-row { flex-direction: column; gap: 3px; } .detail-row .lbl { flex: none; font-size: 0.78rem; } }

        .room-entry + .room-entry { margin-top: 14px; }
        .room-entry-name { font-weight: 800; }
        .primary-tag { font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--brand-2); background: rgba(125, 145, 148, 0.12); padding: 3px 8px; border-radius: 999px; margin-left: 8px; }
        .room-entry-equip { margin: 4px 0 0 18px; padding-left: 18px; color: var(--muted); font-size: 0.9rem; line-height: 1.6; }

        .student-list { margin: 0; padding-left: 20px; display: grid; gap: 6px; }
    </style>

    <section class="band band--cream detail-band">
        <div class="band-inner" style="max-width: 860px;">
            <section class="card fade-in" style="padding:34px 34px 30px;">
                <div class="detail-header">
                    <div>
                        <div class="ticket-ref">{{ $booking->ref }}</div>
                        <h1 class="title" style="font-size:clamp(1.6rem,3.4vw,2.3rem);">{{ $booking->applicant_name }}</h1>
                        <p class="lede" style="margin-top:10px; max-width: 60ch;">{{ $booking->purpose }}</p>
                    </div>
                    <span class="status-pill status-pill--{{ $booking->status }}" style="font-size:0.9rem; padding:9px 16px;">{{ ucfirst($booking->status) }}</span>
                </div>

                @if ($booking->admin_remark)
                    <div class="admin-note">
                        <span class="icon">🛡</span>
                        <div>
                            <strong>Note from admin</strong>
                            <p>{{ $booking->admin_remark }}</p>
                            @if ($booking->processedBy || $booking->processed_at)
                                <span class="meta">
                                    {{ $booking->processedBy?->full_name ?? 'Admin' }}
                                    @if ($booking->processed_at) · {{ $booking->processed_at->format('d/m/Y, H:i') }} @endif
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="detail-section">
                    <h3>Schedule</h3>
                    <div class="detail-rows">
                        <div class="detail-row"><span class="lbl">Lab type</span><span class="val">{{ ucfirst($booking->lab_type) }}</span></div>
                        <div class="detail-row"><span class="lbl">Building</span><span class="val">{{ $booking->rooms->firstWhere('is_primary', true)?->lab?->building ?? '—' }}</span></div>
                        <div class="detail-row"><span class="lbl">Date</span><span class="val">{{ $booking->date_range_label }}</span></div>
                        <div class="detail-row"><span class="lbl">Time</span><span class="val">{{ \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i') }}</span></div>
                        @if ($booking->lab_type === 'csl' && $booking->csl_session_type)
                            <div class="detail-row"><span class="lbl">Session type</span><span class="val">{{ $booking->csl_session_type }}</span></div>
                        @endif
                        @if ($booking->lab_type === 'csl' && $booking->csl_discipline)
                            <div class="detail-row"><span class="lbl">Discipline</span><span class="val">{{ $booking->csl_discipline }}</span></div>
                        @endif
                        @if ($booking->lab_type === 'csl' && $booking->csl_procedure)
                            <div class="detail-row"><span class="lbl">Procedure</span><span class="val" style="white-space:pre-line;">{{ $booking->csl_procedure }}</span></div>
                        @endif
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Applicant</h3>
                    <div class="detail-rows">
                        <div class="detail-row"><span class="lbl">Full name</span><span class="val">{{ $booking->applicant_name }}</span></div>
                        <div class="detail-row"><span class="lbl">Staff / Student ID</span><span class="val">{{ $booking->applicant_id }}</span></div>
                        <div class="detail-row"><span class="lbl">Email</span><span class="val">{{ $booking->applicant_email }}</span></div>
                        <div class="detail-row"><span class="lbl">Role</span><span class="val">{{ $booking->applicant_role ?: '—' }}</span></div>
                        @if ($booking->applicant_group)
                            <div class="detail-row"><span class="lbl">Group</span><span class="val">{{ $booking->applicant_group }}</span></div>
                        @endif
                        <div class="detail-row"><span class="lbl">Phone number</span><span class="val">{{ $booking->applicant_phone ?: '—' }}</span></div>
                        <div class="detail-row"><span class="lbl">Department / Programme</span><span class="val">{{ $booking->applicant_department ?: '—' }}</span></div>
                        @if ($booking->applicant_remark)
                            <div class="detail-row"><span class="lbl">Remark</span><span class="val" style="white-space:pre-line;">{{ $booking->applicant_remark }}</span></div>
                        @endif
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Labs &amp; equipment</h3>
                    @if ($booking->rooms->isNotEmpty())
                        @php $equipmentByLab = $booking->equipment->groupBy('lab_id'); @endphp
                        @foreach ($booking->rooms as $room)
                            <div class="room-entry">
                                <span class="room-entry-name">{{ $room->lab?->name ?? 'Unknown room' }}</span>
                                @if ($room->is_primary)<span class="primary-tag">Primary</span>@endif
                                @if ($equipmentByLab->get($room->lab_id, collect())->isNotEmpty())
                                    <ul class="room-entry-equip">
                                        @foreach ($equipmentByLab->get($room->lab_id) as $equipment)
                                            <li>{{ $equipment->equipment_name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="muted" style="margin:0;">No rooms linked to this booking.</p>
                    @endif
                </div>

                @if ($booking->students->isNotEmpty())
                    <div class="detail-section">
                        <h3>Students ({{ $booking->students->count() }})</h3>
                        <ul class="student-list">
                            @foreach ($booking->students as $student)
                                <li><strong>{{ $student->student_name }}</strong> — {{ $student->student_id }}{{ $student->student_year ? ' · Year ' . $student->student_year : '' }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            <div style="text-align:right; margin-top:18px;">
                <a class="button button-secondary" href="{{ route('bookings.lookup') }}">Check another booking</a>
            </div>
        </div>
    </section>
@endsection
