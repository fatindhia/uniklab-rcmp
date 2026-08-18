@extends('layouts.admin')

@section('content')
    <style>
        .al-hint { display: flex; align-items: center; gap: 10px; padding: 13px 16px; margin-bottom: 16px; font-size: .85rem; color: var(--muted); border-left: 3px solid var(--brand); }

        .al-list { overflow: hidden; }
        .al-item { display: flex; gap: 13px; padding: 14px 18px; border-bottom: 1px solid var(--line); }
        .al-item:last-child { border-bottom: none; }
        .al-item:hover { background: rgba(125, 145, 148, .04); }
        .al-icon { width: 34px; height: 34px; flex-shrink: 0; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: .82rem; font-weight: 800; }
        .al-icon--created { background: rgba(47,138,82,.14); color: #2f8a52; }
        .al-icon--updated { background: rgba(125,145,148,.16); color: var(--brand-2); }
        .al-icon--deleted { background: rgba(192,57,43,.13); color: #c0392b; }
        .al-body { flex: 1; min-width: 0; }
        .al-head { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: .86rem; }
        .al-who { font-weight: 800; }
        .al-what { color: var(--muted); }
        .al-subject { font-weight: 700; }
        .al-area { font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; padding: 2px 8px; border-radius: 999px; background: rgba(125,145,148,.14); color: var(--brand-2); }
        .al-time { font-size: .73rem; color: var(--muted); font-family: var(--mono); margin-top: 3px; }
        .al-changes { display: grid; gap: 3px; margin-top: 8px; padding: 8px 11px; border-left: 2px solid var(--line); background: rgba(125,145,148,.05); border-radius: 0 var(--radius-sm) var(--radius-sm) 0; }
        .al-change { font-size: .74rem; color: var(--muted); }
        .al-change b { color: var(--ink); font-weight: 700; }
        .al-from { text-decoration: line-through; opacity: .7; }
        .al-to { color: var(--brand-2); font-weight: 700; }
    </style>

    <div class="card al-hint">
        <span>🗂️</span>
        <span>Every change made in <strong>Manage Labs</strong> and <strong>Manage Staff</strong> — who did it, what they touched, and what the value was before. Booking approvals are tracked separately in <a href="{{ route('admin.history') }}" style="color:var(--brand-2); font-weight:700;">History</a>.</span>
    </div>

    <div class="tab-bar">
        <a href="{{ route('admin.activity-log') }}" class="{{ $area === 'all' ? 'active' : '' }}">All <span class="tab-count">{{ $counts['all'] }}</span></a>
        <a href="{{ route('admin.activity-log', ['area' => 'labs']) }}" class="{{ $area === 'labs' ? 'active' : '' }}">🏠 Manage Labs <span class="tab-count">{{ $counts['labs'] }}</span></a>
        <a href="{{ route('admin.activity-log', ['area' => 'staff']) }}" class="{{ $area === 'staff' ? 'active' : '' }}">👥 Manage Staff <span class="tab-count">{{ $counts['staff'] }}</span></a>
    </div>

    @php
        $actionMeta = [
            'created' => ['icon' => '＋', 'verb' => 'added'],
            'updated' => ['icon' => '✎', 'verb' => 'updated'],
            'deleted' => ['icon' => '✕', 'verb' => 'removed'],
        ];
        $areaLabels = ['labs' => 'Manage Labs', 'staff' => 'Manage Staff'];
        // Column names as they read to a human, so the diff doesn't leak
        // database spelling into the log.
        $fieldLabels = [
            'name' => 'Name', 'full_name' => 'Name', 'lab_type' => 'Type', 'location' => 'Location',
            'capacity' => 'Capacity', 'status' => 'Status', 'is_room_only' => 'Room-only',
            'weekends_allowed' => 'Weekends allowed', 'requires_special_conditions' => 'Special conditions',
            'notes' => 'Notes', 'equipment' => 'Equipment', 'role' => 'Role', 'is_active' => 'Active',
            'lab_types' => 'Lab types',
            'phone_number' => 'Phone', 'email' => 'Email',
        ];
    @endphp

    <div class="card al-list">
        @forelse ($entries as $entry)
            @php $meta = $actionMeta[$entry->action] ?? ['icon' => '•', 'verb' => $entry->action]; @endphp
            <div class="al-item">
                <span class="al-icon al-icon--{{ $entry->action }}">{{ $meta['icon'] }}</span>
                <div class="al-body">
                    <div class="al-head">
                        <span class="al-who">{{ $entry->performedBy?->full_name ?? 'Unknown user' }}</span>
                        <span class="al-what">{{ $meta['verb'] }}</span>
                        <span class="al-subject">{{ $entry->subject_label }}</span>
                        <span class="al-area">{{ $areaLabels[$entry->area] ?? $entry->area }}</span>
                    </div>
                    <div class="al-time">
                        {{ $entry->created_at?->format('d/m/Y H:i') }}
                        @if ($entry->performedBy?->staff_id) · {{ $entry->performedBy->staff_id }} @endif
                        @if ($entry->performedBy?->role?->label) · {{ $entry->performedBy->role->label }} @endif
                    </div>
                    @if ($entry->changes)
                        <div class="al-changes">
                            @foreach ($entry->changes as $field => $pair)
                                <div class="al-change">
                                    <b>{{ $fieldLabels[$field] ?? \Illuminate\Support\Str::headline($field) }}:</b>
                                    <span class="al-from">{{ \Illuminate\Support\Str::limit($pair[0] ?? '—', 60) }}</span>
                                    →
                                    <span class="al-to">{{ \Illuminate\Support\Str::limit($pair[1] ?? '—', 60) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty">No activity recorded yet — changes made in Manage Labs and Manage Staff will appear here.</div>
        @endforelse
    </div>

    @if ($entries->hasPages())
        <div style="margin-top:16px;">{{ $entries->links() }}</div>
    @endif
@endsection
