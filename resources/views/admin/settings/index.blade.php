@extends('layouts.admin')

@section('content')
    <style>
        .set-intro { margin: 4px 0 22px; max-width: 62ch; color: var(--muted); }

        .set-card { padding: 24px 26px; }
        .set-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 6px; }
        .set-card-head h2 { margin: 0; font-size: 1.08rem; display: flex; align-items: center; gap: 10px; }
        .set-card-desc { color: var(--muted); font-size: 0.88rem; max-width: 62ch; margin: 6px 0 0; }

        .set-status-pill { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 800; white-space: nowrap; flex-shrink: 0; }
        .set-status-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
        .set-status-pill--open { background: rgba(47,138,82,.14); color: #1c6b39; }
        .set-status-pill--maint { background: rgba(192,57,43,.14); color: #9c2c22; }

        .set-divider { height: 1px; background: var(--line); margin: 20px 0; }

        .set-toggle-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; padding: 14px 0; }
        .set-toggle-row + .set-toggle-row { border-top: 1px solid var(--line); }
        .set-toggle-text strong { display: block; font-size: 0.92rem; }
        .set-toggle-text span { display: block; font-size: 0.82rem; color: var(--muted); margin-top: 3px; max-width: 54ch; }

        .set-switch { position: relative; display: inline-flex; align-items: center; flex-shrink: 0; cursor: pointer; margin-top: 2px; }
        .set-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
        .set-switch-track { position: relative; width: 46px; height: 26px; border-radius: 999px; background: var(--line); transition: background .18s ease; }
        .set-switch-track::after { content: ''; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .18s ease; }
        .set-switch input:checked + .set-switch-track { background: #2f8a52; }
        .set-switch input:checked + .set-switch-track::after { transform: translateX(20px); }
        .set-switch input:focus-visible + .set-switch-track { outline: 2px solid var(--brand); outline-offset: 2px; }

        .set-fields { display: grid; gap: 14px; margin-top: 16px; }
        .set-field { display: grid; gap: 5px; }
        .set-field span.lbl { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 700; }
        .set-field input, .set-field textarea {
            /* Same latent issue as the labs/staff modals: without these a control
               keeps its intrinsic width and can push past its container. */
            width: 100%; min-width: 0; box-sizing: border-box;
            padding: 11px 13px; border-radius: var(--radius-sm); border: 1.5px solid var(--line);
            font-family: inherit; font-size: .9rem; color: var(--ink); background: var(--bg);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .set-field textarea { min-height: 88px; resize: vertical; line-height: 1.5; }
        .set-field input:focus, .set-field textarea:focus { outline: none; border-color: var(--brand); background: #fff; box-shadow: 0 0 0 3px rgba(125,145,148,.14); }

        .set-card-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--line); }

        .set-future-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; margin-top: 16px; }
        .set-future-card { padding: 18px; opacity: .6; cursor: not-allowed; user-select: none; }
        .set-future-card .fc-icon { width: 38px; height: 38px; border-radius: var(--radius-sm); background: rgba(125,145,148,.12); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: 12px; }
        .set-future-card h4 { margin: 0 0 4px; font-size: .92rem; }
        .set-future-card p { margin: 0; font-size: .8rem; color: var(--muted); line-height: 1.5; }
        .set-future-card .badge { margin-top: 12px; }

        @media (max-width: 640px) {
            .set-toggle-row { flex-direction: column; }
            .set-card-foot { flex-direction: column; align-items: stretch; }
            .set-card-foot .button { width: 100%; }
        }
        /* Below 16px, iOS Safari zooms the page in on focus and never zooms
           back out. This page sets its own control size, and its <style>
           block loads after admin.css, so the override has to live here. */
        @media (max-width: 900px) {
            .set-field input, .set-field textarea { font-size: 16px; }
        }
    </style>

    <p class="set-intro">Central control panel for platform-wide configuration. Changes here take effect immediately across the entire booking system.</p>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PATCH')

        <div class="card set-card">
            <div class="set-card-head">
                <h2>🛠️ Booking Maintenance Mode</h2>
                <span class="set-status-pill {{ $settings['maintenance_mode'] ? 'set-status-pill--maint' : 'set-status-pill--open' }}" id="maintStatusPill">
                    {{ $settings['maintenance_mode'] ? '● Maintenance Active' : '● Bookings Open' }}
                </span>
            </div>
            <p class="set-card-desc">Temporarily close the public booking system. While active, students, lecturers, researchers, and external applicants cannot submit new bookings — they'll see the maintenance message below instead of the booking form.</p>

            <div class="set-divider"></div>

            <div class="set-toggle-row">
                <div class="set-toggle-text">
                    <strong>Enable Maintenance Mode</strong>
                    <span>Turns off new booking submissions for everyone except internal staff (if allowed below).</span>
                </div>
                <label class="set-switch">
                    <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" {{ $settings['maintenance_mode'] ? 'checked' : '' }}>
                    <span class="set-switch-track" aria-hidden="true"></span>
                </label>
            </div>

            <div class="set-toggle-row">
                <div class="set-toggle-text">
                    <strong>Allow Internal Access</strong>
                    <span>Lab Staff and Super Admins can still log in and create bookings while maintenance is active.</span>
                </div>
                <label class="set-switch">
                    <input type="checkbox" name="maintenance_allow_internal" value="1" {{ $settings['maintenance_allow_internal'] ? 'checked' : '' }}>
                    <span class="set-switch-track" aria-hidden="true"></span>
                </label>
            </div>

            <div class="set-divider"></div>

            <div class="set-fields">
                <label class="set-field">
                    <span class="lbl">Maintenance Message — Title</span>
                    <input type="text" name="maintenance_title" maxlength="150" required value="{{ old('maintenance_title', $settings['maintenance_title']) }}">
                </label>
                <label class="set-field">
                    <span class="lbl">Maintenance Message — Description</span>
                    <textarea name="maintenance_message" maxlength="2000" required>{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
                </label>
            </div>

            <div class="set-card-foot">
                <span class="muted" style="font-size:.8rem;">This message is shown to blocked visitors on the booking page.</span>
                <button type="submit" class="button button-primary">💾 Save Changes</button>
            </div>
        </div>
    </form>

    <div class="section-title" style="margin-top:32px;">
        <h2>More settings, coming soon</h2>
    </div>
    <div class="set-future-grid">
        <div class="card set-future-card" title="Coming soon">
            <div class="fc-icon">📧</div>
            <h4>Email Settings</h4>
            <p>Configure outbound notification emails and templates.</p>
            <span class="badge">Coming soon</span>
        </div>
        <div class="card set-future-card" title="Coming soon">
            <div class="fc-icon">📅</div>
            <h4>Outlook Integration</h4>
            <p>Sync approved bookings to Outlook / Exchange calendars.</p>
            <span class="badge">Coming soon</span>
        </div>
        <div class="card set-future-card" title="Coming soon">
            <div class="fc-icon">📋</div>
            <h4>Booking Rules</h4>
            <p>Fine-tune lead times, durations, and blackout rules per lab type.</p>
            <span class="badge">Coming soon</span>
        </div>
        <div class="card set-future-card" title="Coming soon">
            <div class="fc-icon">🕐</div>
            <h4>Operating Hours</h4>
            <p>Set lab operating hours and holiday closures.</p>
            <span class="badge">Coming soon</span>
        </div>
        <div class="card set-future-card" title="Coming soon">
            <div class="fc-icon">🔔</div>
            <h4>Notifications</h4>
            <p>Manage in-app and email notification preferences.</p>
            <span class="badge">Coming soon</span>
        </div>
    </div>

    <script>
        // Additive only: live-preview the status pill as the toggle is
        // flipped (before Save is even clicked), and require an explicit
        // confirmation only when switching maintenance ON — flipping it back
        // off to restore service should never be slowed down by a prompt.
        (function () {
            var toggle = document.getElementById('maintenance_mode');
            var pill = document.getElementById('maintStatusPill');
            if (!toggle || !pill) return;

            var initiallyOn = toggle.checked;

            function paint() {
                if (toggle.checked) {
                    pill.className = 'set-status-pill set-status-pill--maint';
                    pill.textContent = '● Maintenance Active';
                } else {
                    pill.className = 'set-status-pill set-status-pill--open';
                    pill.textContent = '● Bookings Open';
                }
            }

            toggle.addEventListener('change', function () {
                if (toggle.checked && !initiallyOn) {
                    // Untick immediately and only re-tick once confirmed —
                    // the dialog is async, so the switch can't be left showing
                    // "on" while the admin is still deciding.
                    toggle.checked = false;
                    paint();

                    confirmAction({
                        title: 'Enable maintenance mode?',
                        text: 'Normal users will immediately be unable to submit new bookings.',
                        confirmText: 'Enable',
                        danger: true,
                    }).then(function (ok) {
                        if (!ok) return;
                        toggle.checked = true;
                        paint();
                    });

                    return;
                }
                paint();
            });
        })();
    </script>
@endsection
