@extends('layouts.admin')

@section('content')
    <style>
        .af-field { display: grid; gap: 5px; }
        .af-field span.lbl { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 700; }
        .af-field input, .af-field select, .af-field textarea {
            min-height: 42px; padding: 0 12px; border-radius: var(--radius-sm); border: 1.5px solid var(--line);
            font-family: inherit; font-size: .9rem; color: var(--ink); background: var(--bg);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .af-field textarea { padding: 10px 12px; min-height: auto; }
        .af-field input:focus, .af-field select:focus, .af-field textarea:focus { outline: none; border-color: var(--brand); background: #fff; box-shadow: 0 0 0 3px rgba(125,145,148,.14); }
        .af-check-row { display: flex; gap: 20px; flex-wrap: wrap; padding: 13px 15px; background: rgba(125,145,148,.06); border: 1px solid var(--line); border-radius: var(--radius-sm); }
        .af-check { display: flex; align-items: center; gap: 9px; font-size: .86rem; cursor: pointer; }
        .af-check input { width: 18px; height: 18px; accent-color: var(--brand); flex-shrink: 0; }
        .af-modal-form-grid { padding: 20px; display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0,1fr)); }
        .af-modal-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--line); }
        .af-modal-head h3 { margin: 0; font-size: 1.02rem; }
        .af-modal-close { width: 32px; height: 32px; min-height: 32px; padding: 0; border-radius: 999px; }
        .af-modal-foot { padding: 15px 22px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 8px; }

        .labs-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
        .labs-search { position: relative; flex: 1; min-width: 220px; max-width: 440px; }
        .labs-search input { width: 100%; min-height: 44px; padding: 0 14px 0 40px; border-radius: var(--radius-sm); border: 1.5px solid var(--line); background: var(--panel-strong); font-family: inherit; font-size: .9rem; }
        .labs-search input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(125,145,148,.14); }
        .labs-search .ic { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); opacity: .5; pointer-events: none; }

        .lab-tabs { display: flex; gap: 4px; flex-wrap: wrap; border-bottom: 2px solid var(--line); margin-top: 20px; }
        .lab-tab { padding: 12px 18px; font-size: .92rem; font-weight: 700; color: var(--muted); background: none; border: none; border-bottom: 3px solid transparent; margin-bottom: -2px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; border-radius: 10px 10px 0 0; transition: background .15s ease, color .15s ease; }
        .lab-tab:hover { color: var(--brand-2); background: rgba(125,145,148,.07); }
        .lab-tab.active { color: var(--brand-2); border-bottom-color: var(--brand); background: rgba(125,145,148,.11); }
        .lab-tab.active .tab-count { background: var(--brand); color: #fff; }
        .lab-tab[data-type="research"].active { color: var(--type-research); border-bottom-color: var(--type-research); background: color-mix(in srgb, var(--type-research) 12%, transparent); }
        .lab-tab[data-type="csl"].active { color: var(--type-csl); border-bottom-color: var(--type-csl); background: color-mix(in srgb, var(--type-csl) 12%, transparent); }
        .lab-tab[data-type="pharma"].active { color: var(--type-pharma); border-bottom-color: var(--type-pharma); background: color-mix(in srgb, var(--type-pharma) 12%, transparent); }
        .lab-tab[data-type="research"].active .tab-count { background: var(--type-research); color: #fff; }
        .lab-tab[data-type="csl"].active .tab-count { background: var(--type-csl); color: #fff; }
        .lab-tab[data-type="pharma"].active .tab-count { background: var(--type-pharma); color: #fff; }

        .lab-panel-head { display: none; align-items: center; gap: 8px; margin: 18px 0 4px; font-size: .8rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--brand-2); }
        body.labs-searching .lab-panel-head { display: flex; }

        /* Labs read as a quiet list, not a wall of cards — one row each, with
           the type colour as a dot and actions kept as small ghost buttons. */
        .lab-list { margin-top: 16px; background: var(--panel-strong); border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
        .lab-row { display: flex; align-items: center; gap: 12px; padding: 11px 16px; border-bottom: 1px solid var(--line); transition: background .12s ease; }
        .lab-row:last-child { border-bottom: none; }
        .lab-row:hover { background: rgba(125, 145, 148, .06); }
        .lr-dot { width: 9px; height: 9px; flex-shrink: 0; }
        .lr-main { flex: 1; min-width: 0; }
        .lr-title { display: flex; align-items: center; gap: 7px; flex-wrap: wrap; font-size: .87rem; font-weight: 700; line-height: 1.3; }
        .lr-tag { font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; padding: 2px 7px; border-radius: 999px; background: rgba(125,145,148,.15); color: var(--brand-2); }
        .lr-tag--warn { background: rgba(160,124,31,.16); color: #7a5f18; }
        .lr-meta { display: flex; flex-wrap: wrap; gap: 4px 14px; margin-top: 2px; font-size: .74rem; color: var(--muted); }
        .lr-meta span { position: relative; }
        .lr-meta span + span::before { content: '·'; position: absolute; left: -9px; }
        .lr-note { font-style: italic; }
        .lr-status { flex-shrink: 0; font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
        /* Active is the norm, so it stays quiet — the exceptions are what the
           eye should catch when scanning the list. */
        .lr-status--active { color: var(--muted); font-weight: 600; opacity: .75; }
        .lr-status--maintenance { color: #a07c1f; }
        .lr-status--inactive { color: #c0392b; }
        .lr-actions { display: flex; gap: 6px; flex-shrink: 0; }
        .lr-actions form { display: flex; }
        .lr-btn { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--line); border-radius: var(--radius-sm); background: #fff; color: var(--muted); font-size: .76rem; cursor: pointer; transition: border-color .15s ease, color .15s ease, background .15s ease; }
        .lr-btn:hover { border-color: var(--brand); color: var(--brand-2); }
        .lr-btn--danger { color: #c0392b; border-color: rgba(192,57,43,.32); }
        .lr-btn--danger:hover { border-color: #c0392b; color: #c0392b; background: rgba(192,57,43,.1); }
        @media (max-width: 640px) {
            .lab-row { flex-wrap: wrap; }
            .lr-main { flex-basis: 100%; order: 1; }
            .lr-dot { order: 0; }
            .lr-status { order: 2; margin-left: 16px; }
            .lr-actions { order: 3; margin-left: auto; }
        }
        .lab-empty { padding: 44px 20px; text-align: center; color: var(--muted); border: 1px dashed var(--line); border-radius: var(--radius); margin-top: 18px; }
        .lab-empty .big { font-size: 1.8rem; display: block; margin-bottom: 8px; }
    </style>

    <section class="kpi-grid">
        <div class="kpi"><strong>{{ $stats['total'] }}</strong><span class="kpi-label">Total Labs</span></div>
        <div class="kpi kpi--ok"><strong>{{ $stats['active'] }}</strong><span class="kpi-label">Active</span></div>
        <div class="kpi kpi--warn"><strong>{{ $stats['maintenance'] }}</strong><span class="kpi-label">Maintenance</span></div>
        <div class="kpi kpi--bad"><strong>{{ $stats['inactive'] }}</strong><span class="kpi-label">Inactive</span></div>
    </section>

    <div class="labs-toolbar">
        <div class="labs-search">
            <span class="ic">🔍</span>
            <input type="text" id="labSearch" placeholder="Search any lab by name…" oninput="filterLabs()" autocomplete="off">
        </div>
        <button type="button" class="button button-primary" id="labAddBtn" onclick="addLab()" style="min-height:44px;">＋ Add Lab</button>
    </div>

    @php
        $typeMeta = [
            'research' => ['label' => 'Research', 'full' => 'Research Labs', 'icon' => '🧪', 'sub' => 'Al-Zahrawi & Avicenna buildings'],
            'csl' => ['label' => 'CSL', 'full' => 'CSL Labs', 'icon' => '🏥', 'sub' => 'Clinical Skills — CSL 1 & CSL 2'],
            'pharma' => ['label' => 'Pharma', 'full' => 'Pharma Labs', 'icon' => '⚗️', 'sub' => 'Avicenna Building, Level 1'],
        ];
        $grouped = $labs->groupBy('lab_type');
        $defaultTab = collect($typeMeta)->keys()->first(fn ($t) => $grouped->get($t, collect())->isNotEmpty()) ?? 'research';
    @endphp

    <div class="lab-tabs" role="tablist">
        @foreach ($typeMeta as $type => $meta)
            <button type="button" class="lab-tab {{ $type === $defaultTab ? 'active' : '' }}" data-type="{{ $type }}" onclick="switchLabTab('{{ $type }}')">
                <span>{{ $meta['icon'] }}</span> {{ $meta['label'] }}
                <span class="tab-count">{{ $grouped->get($type, collect())->count() }}</span>
            </button>
        @endforeach
    </div>

    @foreach ($typeMeta as $type => $meta)
        @php $rows = $grouped->get($type, collect()); @endphp
        <div class="lab-panel" data-type="{{ $type }}" style="{{ $type === $defaultTab ? '' : 'display:none;' }}">
            <div class="lab-panel-head"><span>{{ $meta['icon'] }}</span> {{ $meta['full'] }} · <span class="muted">{{ $meta['sub'] }}</span></div>

            @if ($rows->isEmpty())
                <div class="lab-empty" data-role="none">
                    <span class="big">{{ $meta['icon'] }}</span>
                    No {{ $meta['full'] }} yet.
                    <div style="margin-top:12px;"><button type="button" class="button button-primary" onclick="openLabModal(null, '{{ $type }}')">＋ Add {{ $meta['label'] }} Lab</button></div>
                </div>
            @else
                {{-- One compact row per lab: identity on the left, the few
                     details that actually vary in the middle, actions on the
                     right. Anything uniform across every lab (e.g. "not
                     pax-limited") is left out rather than repeated 30 times. --}}
                <div class="lab-list">
                    @foreach ($rows as $lab)
                        @php
                            $labData = $lab->only(['id', 'name', 'lab_type', 'location', 'capacity', 'status', 'is_room_only', 'weekends_allowed', 'requires_special_conditions', 'notes']);
                            $labData['equipment'] = $lab->equipment->map(fn ($e) => ['name' => $e->equipment_name, 'note' => $e->special_conditions_note])->values();
                            $labJson = json_encode($labData);
                        @endphp
                        <div class="lab-row" data-name="{{ strtolower($lab->name) }}">
                            <span class="type-dot type-dot--{{ $type }} lr-dot"></span>
                            <div class="lr-main">
                                <div class="lr-title">
                                    {{ $lab->name }}
                                    @if ($lab->is_room_only)<span class="lr-tag">Room-only</span>@endif
                                    @if ($lab->requires_special_conditions)<span class="lr-tag lr-tag--warn">Special conditions</span>@endif
                                    @unless ($lab->weekends_allowed)<span class="lr-tag">Weekdays only</span>@endunless
                                </div>
                                <div class="lr-meta">
                                    <span>{{ $lab->location ?: 'No location set' }}</span>
                                    @if ($lab->capacity)<span>{{ $lab->capacity }} pax</span>@endif
                                    {{-- Only worth a line when there is actually equipment on the lab. --}}
                                    @if (! $lab->is_room_only && $lab->equipment_count)
                                        <span>{{ $lab->equipment_count }} {{ \Illuminate\Support\Str::plural('item', $lab->equipment_count) }}</span>
                                    @endif
                                    @if ($lab->notes)<span class="lr-note" title="{{ $lab->notes }}">{{ \Illuminate\Support\Str::limit($lab->notes, 48) }}</span>@endif
                                </div>
                            </div>
                            <span class="lr-status lr-status--{{ $lab->status }}">{{ ucfirst($lab->status) }}</span>
                            <div class="lr-actions">
                                <button type="button" class="lr-btn" title="Edit {{ $lab->name }}" onclick="openLabModal({{ $labJson }})">✏️</button>
                                <form method="POST" action="{{ route('admin.labs.destroy', $lab) }}" onsubmit="return confirm('Remove “{{ $lab->name }}”?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lr-btn lr-btn--danger" title="Remove {{ $lab->name }}">✕</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    @endforeach

    <div class="lab-empty" id="labNoResults" style="display:none;">
        <span class="big">🔍</span>No labs match “<span id="labNoResultsQ"></span>”.
    </div>

    <!-- Add/Edit Lab Modal -->
    <div class="modal-overlay" id="labModalOverlay" onclick="closeLabModal(event)">
        <div class="card" onclick="event.stopPropagation()" style="width:92%; max-width:620px; max-height:88vh; overflow-y:auto; border-radius:var(--radius);">
            <div class="af-modal-head">
                <h3 id="labModalTitle">Add Lab</h3>
                <button type="button" onclick="closeLabModal()" class="button button-secondary af-modal-close">✕</button>
            </div>
            <form id="labModalForm" method="POST" action="{{ route('admin.labs.store') }}">
                @csrf
                <div id="labModalMethod"></div>
                <div class="af-modal-form-grid">
                    <label class="af-field" style="grid-column:1/-1;">
                        <span class="lbl">Lab Name *</span>
                        <input type="text" name="name" id="lab-name" required>
                    </label>
                    <label class="af-field">
                        <span class="lbl">Type *</span>
                        <select name="lab_type" id="lab-type" required>
                            <option value="research">Research</option>
                            <option value="csl">CSL</option>
                            <option value="pharma">Pharma</option>
                        </select>
                    </label>
                    <label class="af-field">
                        <span class="lbl">Status *</span>
                        <select name="status" id="lab-status" required>
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </label>
                    <label class="af-field" style="grid-column:1/-1;">
                        <span class="lbl">Location</span>
                        <input type="text" name="location" id="lab-location" placeholder="e.g. Al-Zahrawi, Block A, Level 2">
                    </label>
                    <label class="af-field">
                        <span class="lbl">Capacity</span>
                        <input type="number" name="capacity" id="lab-capacity" min="0" placeholder="0 = not pax-limited">
                    </label>
                    <div class="af-check-row" style="grid-column:1/-1;">
                        <label class="af-check">
                            <input type="checkbox" name="is_room_only" id="lab-room-only" value="1" onchange="toggleEquipmentSection()">
                            Room-only <span class="muted" style="font-size:.75rem;">(no bookable equipment)</span>
                        </label>
                        <label class="af-check">
                            <input type="checkbox" name="requires_special_conditions" id="lab-special" value="1">
                            Requires special conditions
                        </label>
                        <label class="af-check">
                            <input type="checkbox" name="weekends_allowed" id="lab-weekends" value="1">
                            Bookable on weekends
                        </label>
                    </div>

                    <div id="lab-equipment-section" style="grid-column:1/-1; border:1px solid var(--line); border-radius:var(--radius-sm); padding:14px 16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px;">
                            <div>
                                <div style="font-size:.82rem; font-weight:700;">🔧 Equipment</div>
                                <div class="muted" style="font-size:.72rem;">Items users can reserve when booking this lab.</div>
                            </div>
                            <button type="button" class="button button-secondary" style="min-height:34px; padding:0 12px; font-size:.82rem;" onclick="addEquipmentRow()">＋ Add item</button>
                        </div>
                        <div id="lab-equipment-list" style="display:grid; gap:8px;"></div>
                        <div id="lab-equipment-empty" class="muted" style="font-size:.8rem; padding:6px 2px;">No equipment added yet — click “Add item” to list what can be booked here.</div>
                    </div>

                    <label class="af-field" style="grid-column:1/-1;">
                        <span class="lbl">Notes</span>
                        <textarea name="notes" id="lab-notes" rows="3"></textarea>
                    </label>
                </div>
                <div class="af-modal-foot">
                    <button type="button" class="button button-secondary" onclick="closeLabModal()">Cancel</button>
                    <button type="submit" class="button button-primary" id="labModalSubmit">💾 Save Lab</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const LAB_STORE_URL = @json(route('admin.labs.store'));
        const LAB_UPDATE_URL = @json(route('admin.labs.update', ['lab' => '__ID__']));
        const LAB_TAB_LABELS = { research: 'Research', csl: 'CSL', pharma: 'Pharma' };
        let currentLabTab = @json($defaultTab);

        function switchLabTab(type) {
            currentLabTab = type;
            document.getElementById('labSearch').value = '';
            document.body.classList.remove('labs-searching');
            document.getElementById('labNoResults').style.display = 'none';
            document.querySelectorAll('.lab-tab').forEach(t => t.classList.toggle('active', t.dataset.type === type));
            document.querySelectorAll('.lab-panel').forEach(p => {
                p.style.display = p.dataset.type === type ? '' : 'none';
                p.querySelectorAll(".lab-row").forEach(c => c.style.display = '');
            });
            const btn = document.getElementById('labAddBtn');
            if (btn) btn.textContent = '＋ Add ' + (LAB_TAB_LABELS[type] || '') + ' Lab';
        }

        function filterLabs() {
            const q = (document.getElementById('labSearch').value || '').trim().toLowerCase();

            if (!q) {
                switchLabTab(currentLabTab);
                return;
            }

            // Search mode: reveal every panel, filter cards by name across all types.
            document.body.classList.add('labs-searching');
            document.querySelectorAll('.lab-tab').forEach(t => t.classList.remove('active'));
            let total = 0;
            document.querySelectorAll('.lab-panel').forEach(panel => {
                let shown = 0;
                panel.querySelectorAll(".lab-row").forEach(card => {
                    const ok = (card.dataset.name || '').includes(q);
                    card.style.display = ok ? '' : 'none';
                    if (ok) shown++;
                });
                const noneState = panel.querySelector('[data-role="none"]');
                if (noneState) noneState.style.display = 'none';
                panel.style.display = shown > 0 ? '' : 'none';
                total += shown;
            });
            const noRes = document.getElementById('labNoResults');
            document.getElementById('labNoResultsQ').textContent = document.getElementById('labSearch').value.trim();
            noRes.style.display = total === 0 ? '' : 'none';
        }

        document.addEventListener('DOMContentLoaded', () => switchLabTab(currentLabTab));

        function addLab() {
            openLabModal(null, currentLabTab);
        }

        function openLabModal(lab, presetType) {
            const isEdit = !!lab;
            document.getElementById('labModalTitle').textContent = isEdit ? 'Edit Lab' : 'Add Lab';
            document.getElementById('labModalSubmit').textContent = isEdit ? '💾 Update' : '💾 Save Lab';
            document.getElementById('labModalForm').action = isEdit ? LAB_UPDATE_URL.replace('__ID__', lab.id) : LAB_STORE_URL;
            document.getElementById('labModalMethod').innerHTML = isEdit ? '<input type="hidden" name="_method" value="PATCH">' : '';

            document.getElementById('lab-name').value = lab?.name || '';
            document.getElementById('lab-type').value = lab?.lab_type || presetType || 'research';
            document.getElementById('lab-type').disabled = isEdit;
            document.getElementById('lab-location').value = lab?.location || '';
            document.getElementById('lab-capacity').value = (lab?.capacity ?? '') === 0 ? '0' : (lab?.capacity || '');
            document.getElementById('lab-status').value = lab?.status || 'active';
            document.getElementById('lab-room-only').checked = !!(lab?.is_room_only);
            document.getElementById('lab-special').checked = !!(lab?.requires_special_conditions);
            // New labs default to weekend-bookable; only an existing weekday-only room starts unticked.
            document.getElementById('lab-weekends').checked = lab ? !!lab.weekends_allowed : true;
            document.getElementById('lab-notes').value = lab?.notes || '';

            const list = document.getElementById('lab-equipment-list');
            list.innerHTML = '';
            (lab?.equipment || []).forEach(e => addEquipmentRow(e.name, e.note));
            updateEquipmentEmpty();
            toggleEquipmentSection();

            document.getElementById('labModalOverlay').classList.add('open');
        }

        function eqEsc(s) {
            return String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function addEquipmentRow(name, note) {
            const list = document.getElementById('lab-equipment-list');
            const row = document.createElement('div');
            row.className = 'eq-row';
            row.style.cssText = 'display:grid; grid-template-columns:1.2fr 1fr auto; gap:8px; align-items:center;';
            row.innerHTML = `
                <input name="equipment_names[]" placeholder="Equipment name" value="${eqEsc(name)}" style="min-height:38px; padding:0 10px; border-radius:9px; border:1px solid var(--line);">
                <input name="equipment_notes[]" placeholder="Special note (optional)" value="${eqEsc(note)}" style="min-height:38px; padding:0 10px; border-radius:9px; border:1px solid var(--line);">
                <button type="button" onclick="removeEquipmentRow(this)" class="button button-danger" style="min-height:38px; padding:0 11px;" title="Remove item">✕</button>`;
            list.appendChild(row);
            updateEquipmentEmpty();
            if (!name) row.querySelector('input')?.focus();
        }

        function removeEquipmentRow(btn) {
            btn.closest('.eq-row')?.remove();
            updateEquipmentEmpty();
        }

        function updateEquipmentEmpty() {
            const has = document.querySelectorAll('#lab-equipment-list .eq-row').length > 0;
            document.getElementById('lab-equipment-empty').style.display = has ? 'none' : '';
        }

        function toggleEquipmentSection() {
            const roomOnly = document.getElementById('lab-room-only').checked;
            document.getElementById('lab-equipment-section').style.display = roomOnly ? 'none' : '';
        }

        function closeLabModal(e) {
            if (!e || e.target === document.getElementById('labModalOverlay')) {
                document.getElementById('labModalOverlay').classList.remove('open');
            }
        }
    </script>
@endsection
