@extends('layouts.admin')

@section('content')
    <style>
        .af-field { display: grid; gap: 5px; }
        .af-field span.lbl { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 700; }
        .af-field input, .af-field select, .af-field textarea {
            /* Without width/min-width/box-sizing a control keeps its intrinsic
               width and pushes out of its grid cell — invisible on a wide modal,
               obvious once the column narrows on a phone. */
            width: 100%; min-width: 0; box-sizing: border-box;
            min-height: 42px; padding: 0 12px; border-radius: var(--radius-sm); border: 1.5px solid var(--line);
            font-family: inherit; font-size: .9rem; color: var(--ink); background: var(--bg);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .af-field input:focus, .af-field select:focus, .af-field textarea:focus { outline: none; border-color: var(--brand); background: #fff; box-shadow: 0 0 0 3px rgba(125,145,148,.14); }
        .af-modal-form-grid { padding: 20px; display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
        .af-modal-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--line); }
        .af-modal-head h3 { margin: 0; font-size: 1.02rem; }
        .af-modal-close { width: 32px; height: 32px; min-height: 32px; padding: 0; border-radius: 999px; }
        .af-modal-foot { padding: 15px 22px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 8px; }

        .stf-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin: 20px 0 18px; }
        .stf-search { position: relative; flex: 1; min-width: 220px; max-width: 380px; }
        .stf-search input { width: 100%; min-height: 44px; padding: 0 14px 0 40px; border-radius: var(--radius-sm); border: 1.5px solid var(--line); background: var(--panel-strong); font-family: inherit; font-size: .9rem; }
        .stf-search input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(125,145,148,.14); }
        .stf-search .ic { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); opacity: .5; pointer-events: none; }
        .stf-person { display: flex; align-items: center; gap: 10px; }
        .stf-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--grad); color: #fff; display: flex; align-items: center; justify-content: center; font-family: 'Sora', sans-serif; font-weight: 800; font-size: .72rem; flex-shrink: 0; }
        .stf-name { font-weight: 700; font-size: .88rem; }
        .stf-id { font-size: .74rem; color: var(--muted); }

        .stf-table-wrap { overflow: auto; }
        .stf-cards { display: none; gap: 12px; }
        .stf-card { background: var(--panel-strong); border: 1px solid var(--line); border-radius: var(--radius); padding: 16px; box-shadow: var(--shadow-sm); }
        .stf-card-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
        .stf-card-meta { display: grid; gap: 5px; font-size: .84rem; color: var(--muted); margin-bottom: 12px; }
        .stf-card-meta strong { color: var(--ink); font-weight: 700; }
        .stf-card-foot { display: flex; justify-content: flex-end; }

        /* Tick boxes, not switches: the lab types are a pick-many list rather
           than on/off settings. Drawn the same way .stf-switch below is — the
           native input hidden, the box painted on a span — so the two controls
           still share their sizing, colours and focus ring. */
        .stf-labtypes { display: grid; gap: 10px; }
        .stf-labtypes label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .stf-labtypes input { position: absolute; opacity: 0; width: 0; height: 0; }
        .stf-check-box {
            position: relative; width: 20px; height: 20px; flex-shrink: 0;
            /* var(--radius-sm) is 10px — a pill on a box this small. */
            border: 1.5px solid var(--line); border-radius: 6px; background: var(--bg);
            transition: background .15s ease, border-color .15s ease;
        }
        .stf-check-box::after {
            content: ''; position: absolute; left: 6px; top: 2.5px; width: 5px; height: 10px;
            border: solid #fff; border-width: 0 2px 2px 0; transform: rotate(45deg);
            opacity: 0; transition: opacity .15s ease;
        }
        .stf-labtypes input:checked + .stf-check-box { background: var(--brand); border-color: var(--brand); }
        .stf-labtypes input:checked + .stf-check-box::after { opacity: 1; }
        .stf-labtypes input:focus-visible + .stf-check-box { outline: 2px solid var(--brand); outline-offset: 2px; }

        .stf-switch { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; margin-top: 22px; }
        .stf-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
        .stf-switch-track {
            position: relative; width: 42px; height: 24px; border-radius: 999px;
            background: var(--line); transition: background .18s ease; flex-shrink: 0;
        }
        .stf-switch-track::after {
            content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px;
            border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25);
            transition: transform .18s ease;
        }
        .stf-switch input:checked + .stf-switch-track { background: var(--brand); }
        .stf-switch input:checked + .stf-switch-track::after { transform: translateX(18px); }
        .stf-switch input:focus-visible + .stf-switch-track { outline: 2px solid var(--brand); outline-offset: 2px; }
        .stf-switch-label { font-size: .85rem; color: var(--muted); font-weight: 600; }

        @media (max-width: 780px) {
            .stf-table-wrap { display: none; }
            .stf-cards { display: grid; }
        }
        /* Below 16px, iOS Safari zooms the page in on focus and never zooms
           back out. This page sets its own control size, and its <style>
           block loads after admin.css, so the override has to live here. */
        @media (max-width: 640px) {
            /* Two columns leave each field ~120px on a 375px screen — too narrow
               for a name or a role select. One field per row instead. */
            .af-modal-form-grid { grid-template-columns: 1fr; padding: 16px; }
            /* The Active switch's top margin exists to line it up beside a field
               in the two-column layout; stacked, it just leaves a gap. */
            .stf-switch { margin-top: 4px; }
        }
        @media (max-width: 900px) {
            .af-field input, .af-field select, .af-field textarea, .stf-search input { font-size: 16px; }
        }
    </style>

    @php
        $stfTotal = $staff->count();
        $stfActive = $staff->where('is_active', true)->count();
        $stfInactive = $stfTotal - $stfActive;
        $stfRoles = $staff->pluck('role_id')->unique()->count();
        $initialsFor = function ($name) {
            return collect(preg_split('/\s+/', trim($name ?? '')))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('') ?: '?';
        };

        // Which new-booking tickets this account receives, mirroring
        // User::scopeBookingTicketRecipients(). Labels match the Manage Labs
        // tabs. Only lab staff are scoped by lab type — and they may cover
        // more than one area — while admins get every ticket and super admins
        // get none.
        $labTypeLabels = \App\Models\User::LAB_TYPE_LABELS;
        $labTypeFor = fn ($user) => match ($user->role?->name) {
            'lab_staff' => $user->labTypesLabel() ?: 'Not assigned',
            'admin' => 'All labs',
            default => 'No tickets',
        };
    @endphp

    <section class="kpi-grid">
        <div class="kpi"><strong>{{ $stfTotal }}</strong><span class="kpi-label">Total Staff</span></div>
        <div class="kpi kpi--ok"><strong>{{ $stfActive }}</strong><span class="kpi-label">Active</span></div>
        <div class="kpi kpi--bad"><strong>{{ $stfInactive }}</strong><span class="kpi-label">Inactive</span></div>
        <div class="kpi"><strong>{{ $stfRoles }}</strong><span class="kpi-label">Roles in use</span></div>
    </section>

    <div class="stf-toolbar">
        <div class="stf-search">
            <span class="ic">🔍</span>
            <input type="text" id="staffSearch" placeholder="Search by name or Staff ID…" oninput="filterStaff()" autocomplete="off">
        </div>
        <button type="button" class="button button-primary" onclick="openStaffModal()" style="min-height:44px;">＋ Add Staff</button>
    </div>

    <div class="card stf-table-wrap">
        <table>
            <thead><tr><th>Staff</th><th>Role</th><th>Last Login</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($staff as $user)
                    @php $staffJson = json_encode($user->only(['staff_id', 'full_name', 'phone_number', 'role_id', 'lab_types', 'is_active'])); @endphp
                    <tr class="staff-row-item" data-name="{{ strtolower($user->full_name . ' ' . $user->staff_id) }}">
                        <td>
                            <div class="stf-person">
                                <span class="stf-avatar">{{ $initialsFor($user->full_name) }}</span>
                                <div>
                                    <div class="stf-name">{{ $user->full_name }}</div>
                                    <div class="stf-id">{{ $user->staff_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge">{{ $user->role?->label }}</span>
                            <div class="stf-id" style="margin-top:4px;">📧 {{ $labTypeFor($user) }}</div>
                        </td>
                        <td class="muted">{{ $user->last_login_at?->format('d/m/Y, H:i') ?? 'Never' }}</td>
                        <td class="muted">{{ $user->email }}</td>
                        <td><span class="badge badge-{{ $user->is_active ? 'approved' : 'rejected' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <button type="button" class="button button-secondary" style="min-height:32px; padding:0 10px;"
                                onclick="openStaffModal({{ $staffJson }})">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No staff accounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="stf-cards">
        @forelse ($staff as $user)
            @php $staffJson2 = json_encode($user->only(['staff_id', 'full_name', 'phone_number', 'role_id', 'lab_types', 'is_active'])); @endphp
            <article class="stf-card staff-row-item" data-name="{{ strtolower($user->full_name . ' ' . $user->staff_id) }}">
                <div class="stf-card-top">
                    <div class="stf-person">
                        <span class="stf-avatar">{{ $initialsFor($user->full_name) }}</span>
                        <div>
                            <div class="stf-name">{{ $user->full_name }}</div>
                            <div class="stf-id">{{ $user->staff_id }}</div>
                        </div>
                    </div>
                    <span class="badge badge-{{ $user->is_active ? 'approved' : 'rejected' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="stf-card-meta">
                    <span><strong>Role:</strong> {{ $user->role?->label }}</span>
                    <span><strong>Booking emails:</strong> {{ $labTypeFor($user) }}</span>
                    <span><strong>Last Login:</strong> {{ $user->last_login_at?->format('d/m/Y, H:i') ?? 'Never' }}</span>
                    <span><strong>Email:</strong> {{ $user->email }}</span>
                </div>
                <div class="stf-card-foot">
                    <button type="button" class="button button-secondary" style="min-height:36px; padding:0 16px;" onclick="openStaffModal({{ $staffJson2 }})">Edit</button>
                </div>
            </article>
        @empty
            <div class="card empty">No staff accounts yet.</div>
        @endforelse
    </div>

    <div class="empty card" id="staffNoResults" style="display:none; margin-top:12px;">🔍 No staff match your search.</div>

    <!-- Add/Edit Staff Modal -->
    <div class="modal-overlay" id="staffModalOverlay" onclick="closeStaffModal(event)">
        <div class="card" onclick="event.stopPropagation()" style="width:92%; max-width:560px; max-height:88vh; overflow-y:auto; border-radius:var(--radius);">
            <div class="af-modal-head">
                <h3 id="staffModalTitle">Add Staff</h3>
                <button type="button" onclick="closeStaffModal()" class="button button-secondary af-modal-close">✕</button>
            </div>
            <form id="staffModalForm" method="POST" action="{{ route('admin.staff.store') }}">
                @csrf
                <div id="staffModalMethod"></div>
                <div class="af-modal-form-grid">
                    <label class="af-field">
                        <span class="lbl">Staff ID *</span>
                        <input type="text" name="staff_id" id="staff-id" required>
                    </label>
                    <label class="af-field">
                        <span class="lbl">Role *</span>
                        <select name="role_id" id="staff-role" required onchange="syncStaffLabTypeField()">
                            @foreach ($roles as $role)
                                {{-- super_admin is granted in the database only, never pickable here; --}}
                                {{-- kept as a hidden option so it can still display for an existing super admin's own record --}}
                                <option value="{{ $role->id }}" data-role-name="{{ $role->name }}" @if ($role->name === 'super_admin') hidden @endif>{{ $role->label }}</option>
                            @endforeach
                        </select>
                    </label>
                    {{-- Only lab staff are scoped by lab type: admins receive every booking --}}
                    {{-- ticket and super admins receive none, so the field is hidden for both. --}}
                    {{-- Checkboxes rather than a dropdown because some staff look after --}}
                    {{-- more than one area (e.g. Pharma and CSL). --}}
                    <div class="af-field" id="staff-lab-type-field" style="grid-column:1/-1;">
                        <span class="lbl">Lab Types</span>
                        <div class="stf-labtypes">
                            @foreach ($labTypeLabels as $value => $label)
                                <label>
                                    <input type="checkbox" name="lab_types[]" value="{{ $value }}" class="staff-lab-type-input">
                                    <span class="stf-check-box" aria-hidden="true"></span>
                                    <span class="stf-switch-label">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <span class="stf-id">No booking emails when all are off.</span>
                    </div>
                    <label class="af-field" style="grid-column:1/-1;">
                        <span class="lbl">Full Name *</span>
                        <input type="text" name="full_name" id="staff-name" required>
                    </label>
                    <label class="af-field">
                        <span class="lbl">Email *</span>
                        <input type="email" name="email" id="staff-email" required>
                    </label>
                    <label class="af-field">
                        <span class="lbl">Phone</span>
                        <input type="text" name="phone_number" id="staff-phone">
                    </label>
                    <label id="staff-password-field" class="af-field">
                        <span class="lbl">Password *</span>
                        <input type="password" name="password" id="staff-password">
                    </label>
                    <label id="staff-active-field" class="stf-switch" style="display:none;">
                        <input type="checkbox" name="is_active" id="staff-active" value="1">
                        <span class="stf-switch-track" aria-hidden="true"></span>
                        <span class="stf-switch-label">Active account</span>
                    </label>
                </div>
                <div class="af-modal-foot">
                    <button type="button" class="button button-secondary" onclick="closeStaffModal()">Cancel</button>
                    <button type="submit" class="button button-primary" id="staffModalSubmit">💾 Save Staff</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Additive only: simple client-side name/ID filter, mirrors the same
        // pattern already used on the Manage Labs page (filterLabs()).
        function filterStaff() {
            const q = (document.getElementById('staffSearch').value || '').trim().toLowerCase();
            let visible = 0;
            document.querySelectorAll('.staff-row-item').forEach(function (el) {
                const match = !q || (el.dataset.name || '').includes(q);
                el.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            const noRes = document.getElementById('staffNoResults');
            if (noRes) noRes.style.display = (q && visible === 0) ? '' : 'none';
        }
    </script>

    <script>
        const STAFF_STORE_URL = @json(route('admin.staff.store'));
        const STAFF_UPDATE_URL = @json(route('admin.staff.update', ['user' => '__ID__']));

        function openStaffModal(staff) {
            const isEdit = !!staff;
            document.getElementById('staffModalTitle').textContent = isEdit ? 'Edit Staff' : 'Add Staff';
            document.getElementById('staffModalSubmit').textContent = isEdit ? '💾 Update' : '💾 Save Staff';
            document.getElementById('staffModalForm').action = isEdit ? STAFF_UPDATE_URL.replace('__ID__', staff.staff_id) : STAFF_STORE_URL;
            document.getElementById('staffModalMethod').innerHTML = isEdit ? '<input type="hidden" name="_method" value="PATCH">' : '';

            document.getElementById('staff-id').value = staff?.staff_id || '';
            document.getElementById('staff-id').disabled = isEdit;
            document.getElementById('staff-role').value = staff?.role_id || '';
            document.getElementById('staff-name').value = staff?.full_name || '';
            document.getElementById('staff-email').value = staff?.email || '';
            document.getElementById('staff-email').required = !isEdit;
            document.getElementById('staff-email').closest('label').style.display = isEdit ? 'none' : 'grid';
            document.getElementById('staff-phone').value = staff?.phone_number || '';

            document.getElementById('staff-password').required = !isEdit;
            document.getElementById('staff-password-field').style.display = isEdit ? 'none' : 'grid';

            document.getElementById('staff-active-field').style.display = isEdit ? 'flex' : 'none';
            document.getElementById('staff-active').checked = staff?.is_active ?? true;

            const labTypes = staff?.lab_types || [];
            document.querySelectorAll('.staff-lab-type-input').forEach(function (cb) {
                cb.checked = labTypes.includes(cb.value);
            });
            syncStaffLabTypeField();

            document.getElementById('staffModalOverlay').classList.add('open');
        }

        // Lab Types only mean something for lab staff — admins get every booking
        // ticket and super admins get none — so hide the field for the other roles
        // rather than showing one that has no effect. Unticking on the way out keeps
        // stale lab types from being submitted after a role change.
        function syncStaffLabTypeField() {
            const role = document.getElementById('staff-role');
            const isLabStaff = role.selectedOptions[0]?.dataset.roleName === 'lab_staff';
            document.getElementById('staff-lab-type-field').style.display = isLabStaff ? 'grid' : 'none';
            if (!isLabStaff) document.querySelectorAll('.staff-lab-type-input').forEach(cb => { cb.checked = false; });
        }

        function closeStaffModal(e) {
            if (!e || e.target === document.getElementById('staffModalOverlay')) {
                document.getElementById('staffModalOverlay').classList.remove('open');
            }
        }
    </script>
@endsection
