<style>
    .bm-modal-card { width: 92%; max-width: 640px; max-height: 88vh; overflow-y: auto; border-radius: 12px; border: 1px solid var(--line); box-shadow: 0 16px 32px -14px rgba(34,29,30,.22); animation: bmFadeIn .15s ease both; }
    @keyframes bmFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    .bm-modal-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 22px 26px; border-bottom: 1px solid var(--line); position: sticky; top: 0; background: var(--panel-strong); z-index: 1; }
    .bm-modal-head h3 { margin: 0; font-size: 1.22rem; font-family: 'Sora', sans-serif; font-weight: 800; letter-spacing: -.01em; }
    .bm-modal-close { width: 32px; height: 32px; min-height: 32px; padding: 0; border-radius: 999px; flex-shrink: 0; }
    .bm-modal-body { padding: 8px 26px 24px; }
    .bm-modal-foot { padding: 18px 26px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 10px; position: sticky; bottom: 0; background: var(--panel-strong); }

    .bm-section { padding: 26px 0; border-top: 1px solid var(--line); }
    .bm-section:first-child { border-top: none; padding-top: 6px; }
    .bm-section h4 { margin: 0 0 18px; font-size: .8rem; text-transform: uppercase; letter-spacing: .07em; color: var(--brand-2); font-family: 'Sora', sans-serif; font-weight: 800; display: flex; align-items: center; gap: 8px; }
    .bm-section h4 .ic { font-size: .95rem; }

    .bm-block-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); font-weight: 600; margin-bottom: 10px; }
    .bm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px; }
    .bm-field { padding: 0; background: none; border: none; border-radius: 0; min-width: 0; }
    .bm-field-lbl { display: block; font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); margin-bottom: 4px; font-weight: 600; }
    .bm-field-val { font-size: .93rem; font-weight: 700; color: var(--ink); word-break: break-word; }
    .bm-text { font-size: .9rem; line-height: 1.65; white-space: pre-line; background: none; border: none; border-top: 1px solid var(--line); border-radius: 0; padding: 12px 0 0; }
    .bm-room { padding: 12px 0; border: none; border-bottom: 1px solid var(--line); border-radius: 0; margin-bottom: 0; background: none; }
    .bm-room:last-child { border-bottom: none; }
    .bm-room-name { font-weight: 800; font-size: .95rem; font-family: 'Sora', sans-serif; }
    .bm-primary { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--brand-2); background: none; border: 1px solid var(--line); padding: 2px 8px; border-radius: 999px; margin-left: 8px; }
    .bm-equip { margin: 6px 0 0; padding-left: 18px; color: var(--muted); font-size: .82rem; line-height: 1.7; }
    .bm-students { margin: 8px 0 0; padding-left: 18px; display: grid; gap: 6px; font-size: .86rem; }
    .bm-subcard { margin: 18px 0; padding: 0; background: none; border: none; border-radius: 0; }
    .bm-subcard-title { font-size: .72rem; font-weight: 800; font-family: 'Sora', sans-serif; text-transform: uppercase; letter-spacing: .05em; color: var(--brand-2); margin-bottom: 10px; }

    /* Room reassign picker: one tickable row per room, with its live
       Available / Not Available state. Unavailable rows can't be selected. */
    .bm-room-picker { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 8px; max-height: 230px; overflow-y: auto; }
    .bm-room-opt { display: flex; align-items: flex-start; gap: 8px; padding: 9px 11px; border: 1.5px solid var(--line); border-radius: 8px; background: #fff; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
    .bm-room-opt:hover:not(.is-unavailable) { border-color: var(--brand); }
    .bm-room-opt:has(input:checked) { border-color: var(--brand); background: rgba(125,145,148,.08); }
    .bm-room-opt input { width: 16px; height: 16px; margin-top: 2px; accent-color: var(--brand); flex-shrink: 0; }
    .bm-room-opt.is-unavailable { opacity: .55; cursor: not-allowed; background: rgba(192,57,43,.04); border-color: rgba(192,57,43,.28); }
    .bm-room-opt-text { display: grid; gap: 2px; min-width: 0; }
    .bm-room-opt-name { font-size: .82rem; font-weight: 700; }
    .bm-room-opt-tag { font-size: .58rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--brand-2); border: 1px solid var(--line); border-radius: 999px; padding: 1px 6px; margin-left: 4px; }
    .bm-room-opt-state { font-size: .7rem; font-weight: 600; color: var(--muted); }
    .bm-room-opt.is-unavailable .bm-room-opt-state { color: #a03027; }

    .bm-audit-item { padding: 12px 0; border-top: 1px solid var(--line); font-size: .84rem; }
    .bm-audit-item:first-child { border-top: none; padding-top: 0; }

    /* Decision taken after the booking date had already passed. */
    .bm-audit-item.is-late strong { color: #c0392b; }
    .bm-audit-late { display: inline-block; margin-left: 6px; padding: 1px 7px; border-radius: 999px; font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #c0392b; background: rgba(192,57,43,.1); border: 1px solid rgba(192,57,43,.28); vertical-align: 1px; }
    .bm-audit-late-note { color: #c0392b; font-size: .72rem; margin-top: 3px; }

    @media (max-width: 560px) {
        .bm-grid { grid-template-columns: 1fr; }
        .bm-modal-head, .bm-modal-body, .bm-modal-foot { padding-left: 20px; padding-right: 20px; }
    }
</style>

<div id="bookingModalOverlay" class="modal-overlay" onclick="closeBookingModal(event)" style="display:none;">
    <div onclick="event.stopPropagation()" class="card bm-modal-card">
        <div class="bm-modal-head">
            <h3 id="bmTitle">Booking Details</h3>
            <button type="button" onclick="closeBookingModal()" class="button button-secondary bm-modal-close">✕</button>
        </div>
        <div id="bmBody" class="bm-modal-body"></div>
        <div id="bmFooter" class="bm-modal-foot"></div>
    </div>
</div>

<script>
    const BM_UPDATE_URL = @json(route('admin.bookings.update', ['booking' => '__REF__']));
    const BM_REASSIGN_URL = @json(route('admin.bookings.reassign', ['booking' => '__REF__']));
    const BM_AVAILABILITY_URL = @json(route('admin.bookings.room-availability', ['booking' => '__REF__']));
    const BM_CANCEL_URL = @json(route('admin.bookings.cancel', ['booking' => '__REF__']));
    const BM_CSRF = @json(csrf_token());
    const BM_LABS_BY_TYPE = window.ADMIN_LABS_BY_TYPE || {};

    function bmEsc(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // multiline: keep the applicant's own line breaks (e.g. a CSL procedure
    // listed one item per line) instead of collapsing them into one run-on line.
    function bmField(label, value, multiline) {
        return `<div class="bm-field"><span class="bm-field-lbl">${label}</span>`
            + `<span class="bm-field-val"${multiline ? ' style="white-space:pre-line;"' : ''}>`
            + `${value || value === 0 ? bmEsc(value) : '—'}</span></div>`;
    }

    function bmCap(s) {
        s = String(s || '');
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    function openBookingModal(ref, presetDecision) {
        const data = (window.BOOKING_MAP || {})[ref];
        if (!data) return;

        document.getElementById('bmTitle').textContent = `${data.ref} — ${data.applicant_name}`;

        // ---------- Section 1: Applicant Details ----------
        const typeLabel = { research: 'Research', csl: 'CSL', pharma: 'Pharma' }[data.lab_type] || bmCap(data.lab_type);

        const applicantFields = [
            bmField('Applicant', data.applicant_name),
            bmField('Staff / Student ID', data.applicant_id),
            bmField('Email', data.applicant_email),
            bmField('Role', data.applicant_role),
            ...(data.applicant_group ? [bmField('Group', data.applicant_group)] : []),
            bmField('Phone number', data.applicant_phone),
            bmField('Department', data.applicant_department),
        ].join('');

        const schedule = [
            bmField('Lab type', typeLabel),
            bmField('Building', data.building),
            bmField('Date', data.date),
            bmField('Time', `${data.start || '—'} – ${data.end || '—'}`),
        ];
        if (data.lab_type === 'research') {
            if (data.research_pax) schedule.push(bmField('Pax', data.research_pax));
            schedule.push(bmField('Special conditions', data.has_special_conditions ? 'Yes' : 'No'));
        } else if (data.lab_type === 'csl') {
            if (data.csl_session_type) schedule.push(bmField('Session type', data.csl_session_type));
            if (data.csl_discipline) schedule.push(bmField('Discipline', data.csl_discipline));
            if (data.csl_procedure) schedule.push(bmField('Procedure', data.csl_procedure, true));
            if (data.csl_num_students) schedule.push(bmField('Group members', data.csl_num_students));
        } else if (data.lab_type === 'pharma') {
            if (data.pharma_primary_lab) schedule.push(bmField('Primary lab', data.pharma_primary_lab));
            if (data.pharma_num_students) schedule.push(bmField('No. of students', data.pharma_num_students));
        }
        if (data.submitted_at) schedule.push(bmField('Submitted', data.submitted_at));

        let rooms = '<div class="muted" style="font-size:.82rem;">No rooms linked.</div>';
        if (data.roomsDetail && data.roomsDetail.length) {
            rooms = data.roomsDetail.map(r =>
                `<div class="bm-room"><div><span class="bm-room-name">${bmEsc(r.name)}</span>`
                + `${r.primary ? '<span class="bm-primary">Primary</span>' : ''}</div>`
                + `${r.equipment && r.equipment.length ? `<ul class="bm-equip">${r.equipment.map(e => `<li>${bmEsc(e)}</li>`).join('')}</ul>` : ''}</div>`
            ).join('');
        }

        let students = '';
        if (data.students && data.students.length) {
            students = `<div class="bm-block-label" style="margin-top:14px;">Students (${data.students.length})</div>`
                + `<ul class="bm-students">${data.students.map(s =>
                    `<li><strong>${bmEsc(s.name)}</strong> — ${bmEsc(s.id)}${s.year ? ' · Year ' + bmEsc(s.year) : ''}</li>`).join('')}</ul>`;
        }

        const section1 = `<div class="bm-section">
            <h4><span class="ic">👤</span> Applicant Details</h4>
            <div class="bm-block-label">Applicant</div>
            <div class="bm-grid">${applicantFields}</div>
            <div class="bm-block-label" style="margin-top:14px;">Schedule</div>
            <div class="bm-grid">${schedule.join('')}</div>
            <div class="bm-block-label" style="margin-top:14px;">Purpose</div>
            <div class="bm-text">${data.purpose ? bmEsc(data.purpose) : '—'}</div>
            ${data.applicant_remark ? `<div class="bm-block-label" style="margin-top:14px;">Applicant remark</div><div class="bm-text">${bmEsc(data.applicant_remark)}</div>` : ''}
            <div class="bm-block-label" style="margin-top:14px;">Labs &amp; Equipment</div>
            ${rooms}
            ${students}
        </div>`;

        // ---------- Section 2: Admin Response ----------
        let admin = `<div style="margin-bottom:12px;">Status: <span class="badge badge-${data.status}">${bmEsc(bmCap(data.status))}</span></div>`;

        // Room reassignment: CSL bookings can hold several rooms, so the picker
        // is a multi-select grid. Each room's Available / Not Available state is
        // fetched live for this booking's own date + time (see bmLoadRoomAvailability)
        // — unavailable rooms are shown with their reason and can't be ticked.
        if (data.lab_type === 'csl') {
            admin += `<div class="bm-subcard">
                <div class="bm-subcard-title">Reassign Room(s)</div>
                <div class="muted" style="font-size:.76rem; margin-bottom:8px;">
                    Availability is checked against ${bmEsc(data.date || 'this date')}, ${bmEsc(data.start || '')}–${bmEsc(data.end || '')}. Tick one or more available rooms.
                </div>
                <div id="bmRoomPicker" class="bm-room-picker"><div class="muted" style="font-size:.8rem;">Checking room availability…</div></div>
                <div style="display:flex; gap:8px; align-items:center; margin-top:10px;">
                    <button type="button" id="bmReassignBtn" class="button button-secondary" onclick="submitReassign('${ref}')" disabled>Reassign</button>
                    <span class="muted" id="bmRoomPickerCount" style="font-size:.76rem;"></span>
                </div>
                <div id="bmReassignMsg" style="display:none; margin-top:8px; font-size:.8rem;"></div>
            </div>`;
        }

        if (data.status === 'pending') {
            admin += `<label class="bm-block-label" for="bmRemark">Decision remark (required)</label>
                <textarea id="bmRemark" rows="3" style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid var(--line); font-family:inherit;" placeholder="Reason / notes shown to the applicant…"></textarea>
                <div class="muted" style="font-size:.76rem; margin-top:6px;">Write a remark, then use Approve / Reject below.</div>`;
        } else {
            admin += data.admin_remark
                ? `<div class="bm-block-label">Remark</div><div class="bm-text">${bmEsc(data.admin_remark)}</div>`
                : `<div class="muted" style="font-size:.82rem;">No admin remark recorded.</div>`;
            if (data.processed_by || data.processed_at) {
                admin += `<div class="muted" style="font-size:.76rem; margin-top:8px;">Processed by ${bmEsc(data.processed_by || 'Admin')}${data.processed_at ? ' · ' + bmEsc(data.processed_at) : ''}</div>`;
            }
        }

        if (data.status !== 'cancelled') {
            admin += `<div id="bmCancelSection" style="display:none; margin-top:18px; padding-top:16px; border-top:1px solid var(--line);">
                <div class="bm-subcard-title" style="color:#c0392b;">Cancel Booking — Reason (required)</div>
                <textarea id="bmCancelReason" rows="2" style="width:100%; padding:9px 11px; border-radius:8px; border:1px solid var(--line); font-family:inherit;" placeholder="Why is this booking being cancelled?"></textarea>
                <div style="margin-top:8px; display:flex; justify-content:flex-end;">
                    <button type="button" class="button button-danger" onclick="submitCancel('${ref}')">Confirm Cancel</button>
                </div>
            </div>`;
        }

        const section2 = `<div class="bm-section"><h4><span class="ic">🛡</span> Admin Response</h4>${admin}</div>`;

        // ---------- Section 3: Audit Trail ----------
        const audit = (data.audit && data.audit.length)
            ? data.audit.map(a =>
                `<div class="bm-audit-item${a.late ? ' is-late' : ''}"><strong>${bmEsc(a.action)}</strong>`
                + `${a.late ? '<span class="bm-audit-late">Late response</span>' : ''}`
                + `<div class="muted" style="font-size:.72rem;">By ${bmEsc(a.by)}${a.at ? ' · ' + bmEsc(a.at) : ''}</div>`
                + `${a.late ? '<div class="bm-audit-late-note">Answered after the booking date had passed.</div>' : ''}`
                + `${a.detail ? `<div class="muted" style="font-size:.76rem; font-style:italic; margin-top:2px;">${bmEsc(a.detail)}</div>` : ''}</div>`
            ).join('')
            : '<div class="muted" style="font-size:.82rem;">No records yet.</div>';

        const section3 = `<div class="bm-section"><h4><span class="ic">🕓</span> Audit Trail</h4>${audit}</div>`;

        document.getElementById('bmBody').innerHTML = section1 + section2 + section3;

        if (data.lab_type === 'csl') bmLoadRoomAvailability(ref);

        const footer = document.getElementById('bmFooter');
        let footerHtml = '';
        if (data.status === 'pending') {
            footerHtml += `<button type="button" class="button button-primary" onclick="submitDecision('${ref}','approved')">Approve</button>
                <button type="button" class="button button-danger" onclick="submitDecision('${ref}','rejected')">Reject</button>`;
        } else {
            footerHtml += `<button type="button" class="button button-secondary" onclick="closeBookingModal()">Close</button>`;
        }
        if (data.status !== 'cancelled') {
            footerHtml = `<button type="button" class="button button-secondary" onclick="bmToggleCancel()" style="margin-right:auto;">🚫 Cancel Booking</button>` + footerHtml;
        }
        footer.innerHTML = footerHtml;
        footer.style.justifyContent = data.status !== 'cancelled' ? 'space-between' : 'flex-end';

        document.getElementById('bookingModalOverlay').style.display = 'flex';

        if (presetDecision) {
            setTimeout(() => { document.getElementById('bmRemark')?.focus(); }, 50);
        }
    }

    function closeBookingModal(e) {
        if (!e || e.target === document.getElementById('bookingModalOverlay')) {
            document.getElementById('bookingModalOverlay').style.display = 'none';
        }
    }

    function bmSubmitForm(url, fields) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        const tokenInput = document.createElement('input');
        tokenInput.name = '_token';
        tokenInput.value = BM_CSRF;
        form.appendChild(tokenInput);

        const methodInput = document.createElement('input');
        methodInput.name = '_method';
        methodInput.value = 'PATCH';
        form.appendChild(methodInput);

        Object.entries(fields).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function submitDecision(ref, status) {
        const remark = document.getElementById('bmRemark')?.value?.trim();
        if (!remark) {
            document.getElementById('bmRemark')?.focus();
            return;
        }
        bmSubmitForm(BM_UPDATE_URL.replace('__REF__', ref), { status, admin_remark: remark });
    }

    // Pulls the live Available / Not Available list for this booking's slot and
    // renders the picker. Taken rooms are disabled with the reason spelled out,
    // so an unavailable room can never be selected in the first place; the
    // server re-checks on submit in case the list went stale.
    function bmLoadRoomAvailability(ref) {
        const picker = document.getElementById('bmRoomPicker');
        if (!picker) return;

        fetch(BM_AVAILABILITY_URL.replace('__REF__', ref), { headers: { Accept: 'application/json' } })
            .then(async (res) => {
                const body = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(body.message || 'Could not load room availability.');

                const rooms = body.rooms || [];
                if (!rooms.length) {
                    picker.innerHTML = '<div class="muted" style="font-size:.8rem;">No rooms available for this lab type.</div>';
                    return;
                }

                picker.innerHTML = rooms.map(r => `
                    <label class="bm-room-opt${r.available ? '' : ' is-unavailable'}"${r.reason ? ` title="${bmEsc(r.reason)}"` : ''}>
                        <input type="checkbox" value="${r.id}" ${r.assigned ? 'checked' : ''} ${r.available ? '' : 'disabled'} onchange="bmRoomPickerChanged()">
                        <span class="bm-room-opt-text">
                            <span class="bm-room-opt-name">${bmEsc(r.name)}${r.assigned ? ' <span class="bm-room-opt-tag">current</span>' : ''}</span>
                            <span class="bm-room-opt-state">${r.available ? '✅ Available' : '❌ Not Available'}${r.reason ? ' — ' + bmEsc(r.reason) : ''}</span>
                        </span>
                    </label>`).join('');

                bmRoomPickerChanged();
            })
            .catch((err) => {
                picker.innerHTML = `<div style="font-size:.8rem; color:#a03027;">${bmEsc(err.message)}</div>`;
            });
    }

    function bmSelectedRoomIds() {
        return Array.from(document.querySelectorAll('#bmRoomPicker input[type="checkbox"]:checked')).map(cb => cb.value);
    }

    function bmRoomPickerChanged() {
        const selected = bmSelectedRoomIds();
        const current = Array.from(document.querySelectorAll('#bmRoomPicker input[type="checkbox"]'))
            .filter(cb => cb.closest('label').querySelector('.bm-room-opt-tag')).map(cb => cb.value);
        const unchanged = selected.length === current.length && selected.every(id => current.includes(id));

        const btn = document.getElementById('bmReassignBtn');
        if (btn) btn.disabled = selected.length === 0 || unchanged;

        const count = document.getElementById('bmRoomPickerCount');
        if (count) {
            count.textContent = selected.length === 0
                ? 'Select at least one room.'
                : unchanged ? 'Same as the current assignment.'
                : selected.length + ' room' + (selected.length > 1 ? 's' : '') + ' selected';
        }
    }

    function submitReassign(ref) {
        const labIds = bmSelectedRoomIds();
        if (!labIds.length) return;

        const btn = document.getElementById('bmReassignBtn');
        // openBookingModal() below replaces #bmBody's innerHTML wholesale on
        // success, so #bmReassignMsg is a fresh node afterwards — look it up
        // live each call rather than caching the (soon detached) reference.
        const setMsg = (text, isError) => {
            const el = document.getElementById('bmReassignMsg');
            if (!el) return;
            el.style.display = text ? 'block' : 'none';
            el.style.color = isError ? '#a03027' : '#1e6b3b';
            el.textContent = text || '';
        };

        if (btn) { btn.disabled = true; btn.textContent = 'Reassigning…'; }
        setMsg('', false);

        // Keep the in-progress decision remark (if any) across the redraw —
        // reassigning doesn't submit it, and losing it would be annoying.
        const pendingRemark = document.getElementById('bmRemark')?.value ?? '';

        fetch(BM_REASSIGN_URL.replace('__REF__', ref), {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': BM_CSRF,
            },
            body: JSON.stringify({ lab_ids: labIds }),
        })
            .then(async (res) => {
                const body = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(body.message || 'Reassign failed.');

                // Modal stays open — refresh the cached data and redraw in
                // place instead of the old full-page form submit, so the
                // admin can go straight on to Approve / Reject / Cancel.
                window.BOOKING_MAP[ref] = body.booking;
                openBookingModal(ref);

                const remarkEl = document.getElementById('bmRemark');
                if (remarkEl && pendingRemark) remarkEl.value = pendingRemark;

                setMsg(body.status || 'Room reassigned.', false);
            })
            .catch((err) => {
                setMsg(err.message || 'Reassign failed.', true);
                if (btn) { btn.disabled = false; btn.textContent = 'Reassign'; }
            });
    }

    function bmToggleCancel() {
        const section = document.getElementById('bmCancelSection');
        if (!section) return;
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
        if (section.style.display === 'block') {
            document.getElementById('bmCancelReason')?.focus();
        }
    }

    function submitCancel(ref) {
        const reason = document.getElementById('bmCancelReason')?.value?.trim();
        if (!reason) {
            document.getElementById('bmCancelReason')?.focus();
            return;
        }
        bmSubmitForm(BM_CANCEL_URL.replace('__REF__', ref), { reason });
    }
</script>
