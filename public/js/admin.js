        // ================================================================
        // Shared admin month calendar + detail drawer
        // Ported from the legacy admin mockup's renderCal/showCalDetail logic.
        // Defined here, above the main page content, so page content can call
        // initAdminCalendar() inline without a "not defined" race.
        // ================================================================
        const ADM_CAL = {};
        const ADM_MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const ADM_DOW = ['Su','Mo','Tu','We','Th','Fr','Sa'];

        function admPad2(n) { return String(n).padStart(2, '0'); }
        function admDateKey(y, m, d) { return `${y}-${admPad2(m + 1)}-${admPad2(d)}`; }
        function admEsc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

        function initAdminCalendar(prefix, events, opts = {}) {
            const now = new Date();
            ADM_CAL[prefix] = { year: now.getFullYear(), month: now.getMonth(), filter: '', events: events || {}, onDayClick: opts.onDayClick || null, colorBy: opts.colorBy || 'type' };

            document.getElementById(prefix + '-prev')?.addEventListener('click', () => { admNav(prefix, -1); });
            document.getElementById(prefix + '-next')?.addEventListener('click', () => { admNav(prefix, 1); });
            document.getElementById(prefix + '-filter')?.addEventListener('change', (e) => {
                ADM_CAL[prefix].filter = e.target.value || '';
                admRenderCalendar(prefix);
            });

            admRenderCalendar(prefix);

            if (opts.showToday) {
                const ts = admDateKey(now.getFullYear(), now.getMonth(), now.getDate());
                admShowDetail(prefix, ts);
            }
        }

        function admNav(prefix, dir) {
            const st = ADM_CAL[prefix];
            st.month += dir;
            if (st.month < 0) { st.month = 11; st.year--; }
            if (st.month > 11) { st.month = 0; st.year++; }
            admRenderCalendar(prefix);
        }

        function admEventsForDate(prefix, ds) {
            const ev = ADM_CAL[prefix].events[ds];
            if (!ev) return { bookings: [], blocks: [] };
            const filter = ADM_CAL[prefix].filter;
            const bookings = (ev.bookings || []).filter(b => !filter || b.type === filter);
            const blocks = (ev.blocks || []).filter(b => !filter || b.type === filter);
            return { bookings, blocks };
        }

        function admRenderCalendar(prefix) {
            const st = ADM_CAL[prefix];
            const label = document.getElementById(prefix + '-label');
            if (label) label.textContent = ADM_MONTHS[st.month] + ' ' + st.year;

            const grid = document.getElementById(prefix + '-grid');
            if (!grid) return;
            grid.innerHTML = '';

            const first = new Date(st.year, st.month, 1).getDay();
            const daysInMonth = new Date(st.year, st.month + 1, 0).getDate();
            const daysInPrev = new Date(st.year, st.month, 0).getDate();
            const today = new Date();

            function makeCell(d, y, m, other) {
                const ds = admDateKey(y, m, d);
                const isToday = d === today.getDate() && m === today.getMonth() && y === today.getFullYear();
                const isSelected = ds === st.selectedDate;
                const cell = document.createElement('div');
                cell.className = 'cal-day' + (other ? ' other-month' : '') + (isToday ? ' today' : '') + (isSelected ? ' selected-day' : '');
                const num = document.createElement('div');
                num.className = 'cal-day-num';
                num.textContent = d;
                cell.appendChild(num);

                const { bookings, blocks } = admEventsForDate(prefix, ds);
                const items = [
                    ...blocks.map(b => ({ label: b.rooms || b.title || 'Blocked', full: (b.title || 'Blocked') + ' — ' + (b.rooms || 'no room'), type: 'block', pending: false })),
                    ...bookings.map(b => ({ label: b.rooms || (b.type.charAt(0).toUpperCase() + b.type.slice(1) + ' lab'), full: (b.rooms || 'Room TBC') + ' — ' + b.name, type: b.type, pending: b.status === 'pending' })),
                ];
                if (items.length) {
                    const bars = document.createElement('div');
                    bars.className = 'cal-bars';
                    items.slice(0, 1).forEach(item => {
                        const bar = document.createElement('span');
                        bar.className = 'cal-bar cal-bar--' + item.type + (item.pending ? ' cal-bar--pending' : '');
                        bar.textContent = item.label;
                        bar.title = item.full;
                        bars.appendChild(bar);
                    });
                    if (items.length > 1) {
                        const more = document.createElement('span');
                        more.className = 'cal-more';
                        more.textContent = `+${items.length - 1} more`;
                        more.title = items.slice(1).map(i => i.label).join(', ');
                        bars.appendChild(more);
                    }
                    cell.appendChild(bars);
                }

                if (!other) {
                    cell.addEventListener('click', () => {
                        if (st.onDayClick) {
                            st.selectedDate = ds;
                            admRenderCalendar(prefix);
                            st.onDayClick(ds);
                        } else {
                            admShowDetail(prefix, ds);
                        }
                    });
                }
                return cell;
            }

            for (let i = first - 1; i >= 0; i--) {
                grid.appendChild(makeCell(daysInPrev - i, st.month === 0 ? st.year - 1 : st.year, st.month === 0 ? 11 : st.month - 1, true));
            }
            for (let d = 1; d <= daysInMonth; d++) {
                grid.appendChild(makeCell(d, st.year, st.month, false));
            }
            const total = first + daysInMonth;
            const rem = total % 7 === 0 ? 0 : 7 - (total % 7);
            for (let d = 1; d <= rem; d++) {
                grid.appendChild(makeCell(d, st.month === 11 ? st.year + 1 : st.year, st.month === 11 ? 0 : st.month + 1, true));
            }
        }

        function admShowDetail(prefix, ds) {
            const dateEl = document.getElementById(prefix + '-detail-date');
            const bodyEl = document.getElementById(prefix + '-detail-body');
            const drawer = document.getElementById(prefix + '-detail');
            if (!bodyEl || !drawer) return;

            const d = new Date(ds + 'T00:00:00');
            if (dateEl) dateEl.textContent = d.toLocaleDateString('en-MY', { weekday: 'long' }) + ', ' + String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();

            const { bookings, blocks } = admEventsForDate(prefix, ds);
            let html = `<div style="padding:10px 15px;"><a class="button button-secondary" href="${window.ADMIN_TIME_BLOCKS_URL}?date=${ds}" style="min-height:32px; padding:0 12px; font-size:0.78rem;">🗓 Block This Date</a></div>`;

            if (blocks.length) {
                blocks.forEach(b => {
                    html += `<div class="drawer-item"><span class="type-dot type-dot--block"></span> <strong>${admEsc(b.rooms || b.title || 'Blocked')}</strong><div class="muted" style="font-size:.76rem; margin-top:3px;">${admEsc(b.start)}–${admEsc(b.end)}${b.title ? ' · 🚫 ' + admEsc(b.title) : ' · Blocked'}</div></div>`;
                });
            }
            if (bookings.length) {
                bookings.forEach(b => {
                    html += `<div class="drawer-item"><span class="type-dot type-dot--${b.status === 'pending' ? 'pending' : b.type}"></span> <strong>${admEsc(b.name)}</strong> <span class="badge badge-${b.status}" style="font-size:.66rem;">${admEsc(b.status)}</span><div class="muted" style="font-size:.76rem; margin-top:3px;">${admEsc(b.start)}–${admEsc(b.end)} · ${admEsc(b.rooms)}</div></div>`;
                });
            }
            if (!bookings.length && !blocks.length) {
                html += `<div class="drawer-item muted">No events on this date.</div>`;
            }

            bodyEl.innerHTML = html;
            drawer.classList.add('open');
        }

        function admCloseDrawer(prefix) {
            document.getElementById(prefix + '-detail')?.classList.remove('open');
        }

        // ================================================================
        // Home-style two-panel calendar (bars + live side detail panel).
        // Ported from home.blade.php's calendar so admin pages match it.
        // ================================================================
        function initAdminPcCalendar(prefix, events, opts = {}) {
            const EVENTS = events || {};
            const $ = (suffix) => document.getElementById(prefix + '-' + suffix);
            const state = { year: (new Date()).getFullYear(), month: (new Date()).getMonth(), filter: '', selected: null };

            function eventsFor(ds) {
                const ev = EVENTS[ds];
                if (!ev) return { bookings: [], blocks: [] };
                const bookings = (ev.bookings || []).filter(b => !state.filter || b.type === state.filter);
                const blocks = (ev.blocks || []).filter(b => !state.filter || b.type === state.filter);
                return { bookings, blocks };
            }

            function render() {
                const label = $('label');
                if (label) label.textContent = ADM_MONTHS[state.month] + ' ' + state.year;
                const grid = $('grid');
                if (!grid) return;
                grid.innerHTML = '';

                const first = new Date(state.year, state.month, 1).getDay();
                const daysInMonth = new Date(state.year, state.month + 1, 0).getDate();
                const daysInPrev = new Date(state.year, state.month, 0).getDate();
                const today = new Date();

                function cell(d, y, m, other) {
                    const ds = admDateKey(y, m, d);
                    const isToday = !other && d === today.getDate() && m === today.getMonth() && y === today.getFullYear();
                    const el = document.createElement('div');
                    el.className = 'pc-day' + (other ? ' other' : '') + (isToday ? ' today' : '') + (ds === state.selected ? ' selected' : '');
                    const num = document.createElement('div');
                    num.className = 'pc-day-num';
                    num.textContent = d;
                    el.appendChild(num);

                    if (!other) {
                        const { bookings, blocks } = eventsFor(ds);
                        const items = [
                            ...blocks.map(b => ({ label: b.rooms || b.title || 'Blocked', type: 'block', pending: false })),
                            ...bookings.map(b => ({ label: b.rooms || (b.type.charAt(0).toUpperCase() + b.type.slice(1)), type: b.type, pending: b.status === 'pending' })),
                        ];

                        if (items.length) {
                            const bars = document.createElement('div');
                            bars.className = 'pc-bars';
                            items.slice(0, 3).forEach(item => {
                                const bar = document.createElement('span');
                                bar.className = 'pc-bar pc-bar--' + item.type + (item.pending ? ' pc-bar--pending' : '');
                                bar.textContent = item.label;
                                bars.appendChild(bar);
                            });
                            if (items.length > 3) {
                                const more = document.createElement('span');
                                more.className = 'pc-more';
                                more.textContent = `+${items.length - 3} more`;
                                bars.appendChild(more);
                            }
                            el.appendChild(bars);
                        }
                        el.addEventListener('click', () => { state.selected = ds; render(); showDetail(ds); });
                    }
                    return el;
                }

                for (let i = first - 1; i >= 0; i--) {
                    grid.appendChild(cell(daysInPrev - i, state.month === 0 ? state.year - 1 : state.year, state.month === 0 ? 11 : state.month - 1, true));
                }
                for (let d = 1; d <= daysInMonth; d++) {
                    grid.appendChild(cell(d, state.year, state.month, false));
                }
                const total = first + daysInMonth;
                const rem = total % 7 === 0 ? 0 : 7 - (total % 7);
                for (let d = 1; d <= rem; d++) {
                    grid.appendChild(cell(d, state.month === 11 ? state.year + 1 : state.year, state.month === 11 ? 0 : state.month + 1, true));
                }
            }

            function showDetail(ds) {
                const dateEl = $('detail-date');
                const body = $('detail-body');
                if (!body) return;
                const d = new Date(ds + 'T00:00:00');
                if (dateEl) dateEl.textContent = d.toLocaleDateString('en-MY', { weekday: 'long' }) + ', ' + String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();

                const { bookings, blocks } = eventsFor(ds);
                let html = '';
                blocks.forEach(b => {
                    html += `<div class="pc-item"><span class="pc-dot pc-dot--block"></span><div><strong>${admEsc(b.rooms || b.title || 'Blocked')}</strong><div class="muted" style="font-size:.8rem; margin-top:2px;">${admEsc(b.start)}–${admEsc(b.end)}${b.title ? ' · 🚫 ' + admEsc(b.title) : ' · Blocked'}</div></div></div>`;
                });
                bookings.forEach(b => {
                    html += `<div class="pc-item"><span class="pc-dot pc-dot--${b.status === 'pending' ? 'pending' : admEsc(b.type)}"></span><div><strong>${admEsc(b.rooms || b.type)}</strong> <span class="pc-status pc-status--${b.status}" style="font-size:.78rem;">${admEsc(b.status)}</span><div class="muted" style="font-size:.8rem; margin-top:2px;">${admEsc(b.start)}–${admEsc(b.end)}${b.subject ? ' · ' + admEsc(b.subject) : ''}</div></div></div>`;
                });
                if (!blocks.length && !bookings.length) {
                    html = '<div class="pc-empty">No bookings on this date.</div>';
                }
                body.innerHTML = html;
            }

            $('prev')?.addEventListener('click', () => { state.month--; if (state.month < 0) { state.month = 11; state.year--; } render(); });
            $('next')?.addEventListener('click', () => { state.month++; if (state.month > 11) { state.month = 0; state.year++; } render(); });
            $('today')?.addEventListener('click', () => {
                const now = new Date();
                state.year = now.getFullYear(); state.month = now.getMonth();
                state.selected = admDateKey(state.year, state.month, now.getDate());
                render(); showDetail(state.selected);
            });
            $('filter')?.addEventListener('change', (e) => {
                state.filter = e.target.value || '';
                render();
                if (state.selected) showDetail(state.selected);
            });

            const todayKey = admDateKey(state.year, state.month, (new Date()).getDate());
            state.selected = todayKey;
            render();
            showDetail(todayKey);
        }

        // Additive only: sidebar collapse (desktop) + drawer (mobile) + header dropdowns.
        // Does not touch any calendar/modal/wizard global defined above.
        //
        // Wrapped in DOMContentLoaded because this file now loads at the top of
        // <body> (for browser caching across admin page navigations) instead of
        // inline further down the page — without this, the elements below
        // (#admShell, #admSidebar, ...) wouldn't exist yet when this ran.
        document.addEventListener('DOMContentLoaded', function () {
        (function () {
            var shell = document.getElementById('admShell');
            var sidebar = document.getElementById('admSidebar');
            var backdrop = document.getElementById('admSidebarBackdrop');
            var collapseBtn = document.getElementById('admCollapseBtn');
            var menuBtn = document.getElementById('admMenuBtn');

            function isMobile() { return window.matchMedia('(max-width: 900px)').matches; }

            collapseBtn?.addEventListener('click', function () {
                if (isMobile()) return;
                var collapsed = shell.classList.toggle('collapsed');
                try { localStorage.setItem('adminSidebarCollapsed', collapsed ? '1' : '0'); } catch (e) {}
            });
            try {
                if (!isMobile() && localStorage.getItem('adminSidebarCollapsed') === '1') shell.classList.add('collapsed');
            } catch (e) {}

            function openDrawer() {
                sidebar.classList.add('drawer-open');
                backdrop.classList.add('open');
                menuBtn?.setAttribute('aria-expanded', 'true');
            }
            function closeDrawer() {
                sidebar.classList.remove('drawer-open');
                backdrop.classList.remove('open');
                menuBtn?.setAttribute('aria-expanded', 'false');
            }
            menuBtn?.addEventListener('click', function () {
                sidebar.classList.contains('drawer-open') ? closeDrawer() : openDrawer();
            });
            backdrop?.addEventListener('click', closeDrawer);
            sidebar?.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', closeDrawer); });

            document.querySelectorAll('.dropdown').forEach(function (dd) {
                var btn = dd.querySelector(':scope > button');
                btn?.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var wasOpen = dd.classList.contains('open');
                    document.querySelectorAll('.dropdown.open').forEach(function (o) { o.classList.remove('open'); });
                    if (!wasOpen) dd.classList.add('open');
                });
            });
            document.addEventListener('click', function () {
                document.querySelectorAll('.dropdown.open').forEach(function (o) { o.classList.remove('open'); });
            });
        })();

        // Additive only: two independent "seen" states, tracked via localStorage
        // timestamps (not a list of refs — the recent-pending list is only the
        // top 8, so approving/rejecting one shifts an older-but-already-seen
        // booking into view; comparing by submission time avoids treating that
        // as "new"). Deliberately separate: just opening the bell to glance at
        // what's new clears the badge/blink (you've noticed it), but the list
        // itself keeps showing those bookings until "Mark all read" is clicked
        // — otherwise opening once to peek would make that button pointless.
        (function () {
            var notifBtn = document.getElementById('admNotifBtn');
            var notifDot = notifBtn?.querySelector('.dot');
            var markReadBtn = document.getElementById('admNotifMarkRead');
            var notifItems = document.getElementById('admNotifItems');
            var notifCaughtUp = document.getElementById('admNotifCaughtUp');
            if (!notifBtn || !notifDot) return;

            var pending = window.ADMIN_RECENT_PENDING || [];
            var pendingByRef = {};
            pending.forEach(function (b) { pendingByRef[b.ref] = b; });

            function readTime(key) {
                var v = null;
                try { v = localStorage.getItem(key); } catch (e) {}
                return v ? new Date(v).getTime() : 0;
            }
            function writeNow(key) {
                try { localStorage.setItem(key, new Date().toISOString()); } catch (e) {}
            }

            var badgeSeenTime = readTime('admNotifSeenAt');
            var listReadTime = readTime('admNotifListReadAt');

            // Swap the pending-bookings list for a "caught up" message — the
            // bookings themselves are still pending/actionable (still reachable
            // via "View all pending" and the sidebar counts), this is just this
            // dropdown's own read/unread view.
            function showCaughtUp() {
                if (notifItems && notifCaughtUp) {
                    notifItems.style.display = 'none';
                    notifCaughtUp.style.display = 'block';
                }
            }

            // Hide individual bookings already cleared via "Mark all read" —
            // otherwise one genuinely new booking arriving would drag every
            // already-read booking back into view alongside it in this list.
            var badgeNewCount = 0;
            var listNewCount = 0;
            if (notifItems) {
                notifItems.querySelectorAll('.notif-booking').forEach(function (el) {
                    var b = pendingByRef[el.dataset.ref];
                    var submittedTime = b && b.submitted_at ? new Date(b.submitted_at).getTime() : 0;
                    if (submittedTime > badgeSeenTime) badgeNewCount++;
                    var isListNew = submittedTime > listReadTime;
                    el.style.display = isListNew ? '' : 'none';
                    if (isListNew) listNewCount++;
                });
            }

            if (badgeNewCount > 0) {
                notifDot.textContent = badgeNewCount > 99 ? '99+' : String(badgeNewCount);
                notifDot.classList.add('blink');
            } else {
                notifDot.style.display = 'none';
            }

            if (listNewCount === 0) {
                // Nothing new since the last mark-read (persisted in localStorage,
                // so this holds across reloads/re-logins) — reopen already caught up
                // instead of showing the same already-seen bookings again.
                showCaughtUp();
            }

            // Bell click just opens the dropdown (handled by the generic .dropdown
            // toggle below) — acknowledge the badge only, leave the list alone.
            notifBtn.addEventListener('click', function () {
                writeNow('admNotifSeenAt');
                notifDot.classList.remove('blink');
                notifDot.style.display = 'none';
            });

            markReadBtn?.addEventListener('click', function (e) {
                e.stopPropagation();
                writeNow('admNotifListReadAt');
                writeNow('admNotifSeenAt');
                notifDot.classList.remove('blink');
                notifDot.style.display = 'none';
                showCaughtUp();
            });
        })();
        });
