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
        // admEsc leaves quotes alone, which is fine for text nodes but breaks a
        // double-quoted attribute — JSON payloads are full of them.
        function admEscAttr(s) { return admEsc(s).replace(/"/g, '&quot;'); }

        /**
         * Hover card for calendar bookings.
         *
         * The card lives on <body> rather than inside the row, because the
         * detail panel is its own scroll container and would otherwise clip it.
         * Bound per render since showDetail() replaces the list wholesale.
         *
         * Pointer-only: on touch there is no hover, and the row already shows
         * room, time, subject and status, so the card is suppressed there
         * (see the max-width rule on .pc-hovercard).
         */
        const admHoverCapable = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        let admHovercardEl = null;
        // Bumped on every hover so a slow response for a row the pointer has
        // already left can be discarded instead of overwriting the current card.
        let admHoverToken = 0;
        const admDetailsCache = {};

        /**
         * Full booking record for the hover card. Cached per ref for the life of
         * the page: the same booking is hovered repeatedly while scanning a day,
         * and the record can't change without a page action anyway. Resolves
         * null on any failure — the card keeps showing the calendar-only fields
         * rather than erroring in the admin's face over a tooltip.
         */
        function admFetchBookingDetails(ref) {
            if (!ref || !window.ADMIN_BOOKING_DETAILS_URL) return Promise.resolve(null);
            if (admDetailsCache[ref] !== undefined) return Promise.resolve(admDetailsCache[ref]);

            return fetch(window.ADMIN_BOOKING_DETAILS_URL.replace('__REF__', encodeURIComponent(ref)), {
                headers: { Accept: 'application/json' },
            })
                .then(function (res) { return res.ok ? res.json() : null; })
                .catch(function () { return null; })
                .then(function (data) { admDetailsCache[ref] = data; return data; });
        }

        /** Same contract as admFetchBookingDetails(), for a time block. */
        function admFetchBlockDetails(id) {
            if (!id || !window.ADMIN_BLOCK_DETAILS_URL) return Promise.resolve(null);
            const cacheKey = 'block:' + id;
            if (admDetailsCache[cacheKey] !== undefined) return Promise.resolve(admDetailsCache[cacheKey]);

            return fetch(window.ADMIN_BLOCK_DETAILS_URL.replace('__ID__', encodeURIComponent(id)), {
                headers: { Accept: 'application/json' },
            })
                .then(function (res) { return res.ok ? res.json() : null; })
                .catch(function () { return null; })
                .then(function (data) { admDetailsCache[cacheKey] = data; return data; });
        }

        const ADM_RECUR_LABELS = { none: 'One-off', weekly: 'Weekly', fortnightly: 'Fortnightly' };

        function admBlockHovercardHtml(basic, full) {
            const kv = (k, v) => v ? `<div class="pc-hovercard-row"><span class="k">${admEsc(k)}</span><span class="v">${admEsc(v)}</span></div>` : '';
            const type = basic.type ? basic.type.charAt(0).toUpperCase() + basic.type.slice(1) : '';

            // rooms/equipment arrive as arrays from the endpoint but as a
            // pre-joined string in the calendar payload.
            const asList = (v) => Array.isArray(v) ? v : (v ? String(v).split(',').map(s => s.trim()).filter(Boolean) : []);

            let html = `<div class="pc-hovercard-head">`
                + `<span class="pc-hovercard-ref">${admEsc((full && full.title) || basic.title || 'Blocked')}</span>`
                + `<span class="pc-status pc-status--cancelled" style="font-size:.76rem;">🚫 Blocked</span>`
                + `</div>`;

            html += kv('Lab type', type);
            html += kv('Date', full && full.date);
            html += kv('Time', `${basic.start}–${basic.end}`);
            html += kv('PIC', (full && full.pic) || basic.pic);
            if (full) {
                html += kv('Repeats', ADM_RECUR_LABELS[full.recurring] || full.recurring);
                html += kv('Blocked by', full.created_by);
            }

            const rooms = asList(full ? full.rooms : basic.rooms);
            if (rooms.length) {
                html += `<div class="pc-hovercard-sec">Rooms</div>`;
                rooms.forEach(function (r) { html += `<div class="pc-hovercard-room">${admEsc(r)}</div>`; });
            }

            const equipment = asList(full && full.equipment);
            if (equipment.length) {
                html += `<div class="pc-hovercard-sec">Equipment</div>`;
                equipment.forEach(function (e) { html += `<div class="pc-hovercard-eq">• ${admEsc(e)}</div>`; });
            }

            const purpose = (full && full.purpose) || basic.purpose;
            if (purpose) {
                html += `<div class="pc-hovercard-sec">Purpose</div><div class="pc-hovercard-text">${admEsc(purpose)}</div>`;
            }
            if (full && full.notes) {
                html += `<div class="pc-hovercard-sec">Notes</div><div class="pc-hovercard-text">${admEsc(full.notes)}</div>`;
            }

            return html;
        }

        function admHovercardHtml(basic, full) {
            const kv = (k, v) => v ? `<div class="pc-hovercard-row"><span class="k">${admEsc(k)}</span><span class="v">${admEsc(v)}</span></div>` : '';
            const type = basic.type ? basic.type.charAt(0).toUpperCase() + basic.type.slice(1) : '';

            let html = `<div class="pc-hovercard-head">`
                + `<span class="pc-hovercard-ref">${admEsc((full && full.ref) || basic.ref || 'Booking')}</span>`
                + `<span class="pc-status pc-status--${admEsc(basic.status)}" style="font-size:.76rem;">${admEsc(basic.status)}</span>`
                + `</div>`;

            html += kv('Applicant', (full && full.applicant_name) || basic.name);

            if (full) {
                html += kv('Role', full.applicant_role);
                html += kv('ID', full.applicant_id);
                html += kv('Email', full.applicant_email);
                html += kv('Phone', full.applicant_phone);
                html += kv('Dept', full.applicant_department);
            }

            html += kv('Lab type', type);
            html += kv('Date', (full && full.date) || '');
            html += kv('Time', `${basic.start}–${basic.end}`);

            if (full && full.roomsDetail && full.roomsDetail.length) {
                html += `<div class="pc-hovercard-sec">Rooms &amp; equipment</div>`;
                full.roomsDetail.forEach(function (r) {
                    html += `<div class="pc-hovercard-room">${admEsc(r.name)}`
                        + (r.primary ? `<span class="pc-hovercard-tag">Primary</span>` : '')
                        + `</div>`;
                    if (r.equipment && r.equipment.length) {
                        r.equipment.forEach(function (e) {
                            html += `<div class="pc-hovercard-eq">• ${admEsc(e)}</div>`;
                        });
                    } else {
                        html += `<div class="pc-hovercard-eq is-none">No equipment</div>`;
                    }
                });
            } else {
                html += kv('Room(s)', basic.rooms);
            }

            if (full) {
                if (full.students && full.students.length) {
                    html += `<div class="pc-hovercard-sec">Students (${full.students.length})</div>`;
                    full.students.slice(0, 4).forEach(function (s) {
                        html += `<div class="pc-hovercard-eq">• ${admEsc(s.name)}${s.id ? ' (' + admEsc(s.id) + ')' : ''}</div>`;
                    });
                    if (full.students.length > 4) {
                        html += `<div class="pc-hovercard-eq is-none">+${full.students.length - 4} more</div>`;
                    }
                }
                if (full.csl_discipline) html += kv('Discipline', full.csl_discipline);
            }

            const purpose = (full && full.purpose) || basic.subject;
            if (purpose) {
                html += `<div class="pc-hovercard-sec">Purpose</div><div class="pc-hovercard-text">${admEsc(purpose)}</div>`;
            }
            if (full && full.applicant_remark) {
                html += `<div class="pc-hovercard-sec">Applicant remark</div><div class="pc-hovercard-text">${admEsc(full.applicant_remark)}</div>`;
            }
            if (full && full.admin_remark) {
                html += `<div class="pc-hovercard-sec">Admin remark</div><div class="pc-hovercard-text">${admEsc(full.admin_remark)}</div>`;
            }

            return html;
        }

        function admHovercard() {
            if (!admHovercardEl) {
                admHovercardEl = document.createElement('div');
                admHovercardEl.className = 'pc-hovercard';
                document.body.appendChild(admHovercardEl);
            }
            return admHovercardEl;
        }

        function admHideHovercard() {
            if (admHovercardEl) admHovercardEl.classList.remove('is-open');
        }

        function admBindCalendarHovercards(container) {
            if (!admHoverCapable || !container) return;

            // Bookings and blocks behave identically here — only the payload
            // attribute, the fetcher and the renderer differ.
            const kinds = [
                { attr: 'booking', key: 'ref', fetch: admFetchBookingDetails, render: admHovercardHtml },
                { attr: 'block', key: 'id', fetch: admFetchBlockDetails, render: admBlockHovercardHtml },
            ];

            kinds.forEach(function (kind) {
                container.querySelectorAll('.pc-item[data-' + kind.attr + ']').forEach(function (row) {
                    let data;
                    try { data = JSON.parse(row.dataset[kind.attr]); } catch (e) { return; }

                    row.addEventListener('mouseenter', function () {
                        const card = admHovercard();
                        admHoverToken++;
                        const token = admHoverToken;

                        // Paint what the calendar already knows straight away,
                        // so the card never appears empty while the request is
                        // in flight, then fill in the rest when it lands.
                        card.innerHTML = kind.render(data, null);
                        card.classList.add('is-open');
                        admPositionHovercard(card, row);

                        kind.fetch(data[kind.key]).then(function (full) {
                            // A different row may have been hovered in the
                            // meantime — only the newest hover may repaint.
                            if (!full || token !== admHoverToken) return;
                            if (!admHovercardEl || !admHovercardEl.classList.contains('is-open')) return;
                            admHovercardEl.innerHTML = kind.render(data, full);
                            admPositionHovercard(admHovercardEl, row);
                        });
                    });

                    row.addEventListener('mousemove', function () {
                        if (admHovercardEl && admHovercardEl.classList.contains('is-open')) {
                            admPositionHovercard(admHovercardEl, row);
                        }
                    });

                    row.addEventListener('mouseleave', admHideHovercard);
                });
            });
        }

        // Anchored to the row, flipped to whichever side has room so the card
        // never runs off screen.
        function admPositionHovercard(card, row) {
            const r = row.getBoundingClientRect();
            const w = card.offsetWidth || 280;
            const h = card.offsetHeight || 160;
            const gap = 10;

            let left = r.left - w - gap;
            if (left < 8) left = r.right + gap;
            if (left + w > window.innerWidth - 8) left = Math.max(8, window.innerWidth - w - 8);

            let top = r.top + (r.height / 2) - (h / 2);
            top = Math.max(8, Math.min(top, window.innerHeight - h - 8));

            card.style.left = left + 'px';
            card.style.top = top + 'px';
        }

        window.addEventListener('scroll', admHideHovercard, true);

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

                // Room, time, subject and status each get their own line, the
                // same shape the public homepage calendar uses.
                const line = (text, cls) => text ? `<span class="${cls}">${admEsc(text)}</span>` : '';

                blocks.forEach(b => {
                    html += `<div class="pc-item" data-block="${admEscAttr(JSON.stringify(b))}"><span class="pc-dot pc-dot--block"></span><div class="pc-item-lines">`
                        + line(b.rooms || b.title || 'Blocked', 'pc-item-room')
                        + line(`${b.start}–${b.end}`, 'pc-item-meta')
                        + line(b.title, 'pc-item-meta')
                        + `<span class="pc-status pc-status--cancelled">🚫 Blocked</span>`
                        + `</div></div>`;
                });
                bookings.forEach(b => {
                    // The whole event rides along on the element so the hover
                    // card can read it without another lookup.
                    html += `<div class="pc-item" data-booking="${admEscAttr(JSON.stringify(b))}"><span class="pc-dot pc-dot--${b.status === 'pending' ? 'pending' : admEsc(b.type)}"></span><div class="pc-item-lines">`
                        + line(b.rooms || b.type, 'pc-item-room')
                        + line(`${b.start}–${b.end}`, 'pc-item-meta')
                        + line(b.subject, 'pc-item-meta')
                        + `<span class="pc-status pc-status--${admEsc(b.status)}">${admEsc(b.status)}</span>`
                        + `</div></div>`;
                });
                if (!blocks.length && !bookings.length) {
                    html = '<div class="pc-empty">No bookings on this date.</div>';
                }
                body.innerHTML = html;
                admBindCalendarHovercards(body);
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
