/* ============================================================
   UniKLAB RCMP — Admin Panel JS
   admin/assets/js/admin.js
   ============================================================ */

'use strict';

/* ── Sidebar mobile toggle ── */
function toggleSidebar() {
  document.querySelector('.sidebar').classList.toggle('open');
}
document.addEventListener('click', function(e) {
  const sb = document.querySelector('.sidebar');
  if (!sb) return;
  if (sb.classList.contains('open') && !sb.contains(e.target) && !e.target.closest('.topbar-menu-btn')) {
    sb.classList.remove('open');
  }
});

/* ── Toast ── */
function showToast(msg, type = '') {
  let t = document.getElementById('adminToast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'adminToast';
    document.body.appendChild(t);
  }
  const ICONS = { success: '✓', danger: '✕', warning: '!', info: 'i' };
  const kind = type || 'info';
  t.className = 'toast ' + kind;
  t.innerHTML =
    '<span class="toast-icon">' + (ICONS[kind] || 'i') + '</span>' +
    '<span class="toast-msg"></span>' +
    '<span class="toast-bar"></span>';
  t.querySelector('.toast-msg').textContent = msg;
  void t.offsetWidth;                       // restart slide-in + progress bar
  requestAnimationFrame(() => t.classList.add('show'));
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3200);
}

/* ── Table row search/filter ── */
function filterTable(inputId, tableId, colIndex) {
  const q = document.getElementById(inputId)?.value?.toLowerCase() ?? '';
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
  rows.forEach(row => {
    const cell = row.cells[colIndex];
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(q) ? '' : 'none';
  });
}

function filterTableBySelect(selectId, tableId, colIndex) {
  const val = document.getElementById(selectId)?.value?.toLowerCase() ?? '';
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
  rows.forEach(row => {
    if (!val) { row.style.display = ''; return; }
    const cell = row.cells[colIndex];
    const text = (cell?.textContent ?? '').toLowerCase();
    row.style.display = text.includes(val) ? '' : 'none';
  });
}

/* ── Custom dialog (confirm / prompt) — replaces native browser popups ── */
function uiDialog(opts) {
  const o = Object.assign({
    title: 'Are you sure?', message: '', icon: '', variant: 'primary',
    confirmText: 'Confirm', cancelText: 'Cancel',
    prompt: false, placeholder: '', defaultValue: '', required: false,
    onConfirm: () => {}, onCancel: () => {},
  }, opts);

  let ov = document.getElementById('uiDialogOverlay');
  if (!ov) {
    ov = document.createElement('div');
    ov.id = 'uiDialogOverlay';
    ov.className = 'ui-dialog-overlay';
    ov.innerHTML =
      '<div class="ui-dialog" role="dialog" aria-modal="true">' +
        '<div class="ui-dialog-icon"></div>' +
        '<h3 class="ui-dialog-title"></h3>' +
        '<p class="ui-dialog-msg"></p>' +
        '<textarea class="ui-dialog-input" rows="3"></textarea>' +
        '<div class="ui-dialog-actions">' +
          '<button type="button" class="ui-dialog-btn ui-dialog-cancel"></button>' +
          '<button type="button" class="ui-dialog-btn ui-dialog-confirm"></button>' +
        '</div>' +
      '</div>';
    document.body.appendChild(ov);
  }

  const iconEl   = ov.querySelector('.ui-dialog-icon');
  const titleEl  = ov.querySelector('.ui-dialog-title');
  const msgEl    = ov.querySelector('.ui-dialog-msg');
  const inputEl  = ov.querySelector('.ui-dialog-input');
  const okBtn    = ov.querySelector('.ui-dialog-confirm');
  const cancelBtn= ov.querySelector('.ui-dialog-cancel');

  const ICONS = { danger: '!', warning: '!', primary: '?', success: '✓' };
  ov.querySelector('.ui-dialog').className = 'ui-dialog ' + o.variant;
  iconEl.textContent  = o.icon || ICONS[o.variant] || '?';
  titleEl.textContent = o.title;
  msgEl.textContent   = o.message;
  msgEl.style.display = o.message ? '' : 'none';
  inputEl.style.display = o.prompt ? '' : 'none';
  inputEl.value = o.defaultValue || '';
  inputEl.placeholder = o.placeholder || '';
  inputEl.classList.remove('err');
  okBtn.textContent = o.confirmText;
  cancelBtn.textContent = o.cancelText;

  function close() {
    ov.classList.remove('open');
    okBtn.onclick = cancelBtn.onclick = ov.onclick = null;
    document.removeEventListener('keydown', onKey);
  }
  function doConfirm() {
    const val = inputEl.value.trim();
    if (o.prompt && o.required && !val) { inputEl.classList.add('err'); inputEl.focus(); return; }
    close();
    o.onConfirm(o.prompt ? val : true);
  }
  function doCancel() { close(); o.onCancel(); }
  function onKey(e) {
    if (e.key === 'Escape') doCancel();
    else if (e.key === 'Enter' && !o.prompt) doConfirm();
  }

  okBtn.onclick = doConfirm;
  cancelBtn.onclick = doCancel;
  ov.onclick = e => { if (e.target === ov) doCancel(); };
  document.addEventListener('keydown', onKey);

  ov.classList.add('open');
  setTimeout(() => (o.prompt ? inputEl : okBtn).focus(), 60);
}
function uiConfirm(opts) { uiDialog(Object.assign({ prompt: false }, opts)); }
function uiPrompt(opts)  { uiDialog(Object.assign({ prompt: true  }, opts)); }

/* ── Confirm before destructive action (styled dialog) ── */
function confirmAction(msg, fn) {
  uiDialog({ title: 'Please confirm', message: msg, variant: 'danger', confirmText: 'Yes, continue', onConfirm: fn });
}

/* ── Simple modal open/close ── */
function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  }
});

/* ── UI-only CRUD stubs (no DB) ── */
function uiEditLab(id) { showToast('Edit lab #' + id + ' — DB not connected yet.', ''); }
function uiDeleteLab(id) { confirmAction('Delete lab #' + id + '? (UI only — no DB)', () => showToast('Lab #' + id + ' removed (UI only).', 'danger')); }
function uiAddLab() { showToast('Add lab form — DB not connected yet.', ''); }

function uiRemoveBlock(id) { confirmAction('Remove block #' + id + '? (UI only)', () => showToast('Block #' + id + ' removed (UI only).', 'danger')); }

/* ── Format date for display ── */
function fmtDate(str) {
  if (!str) return '—';
  const d = new Date(str + 'T00:00:00');
  return d.toLocaleDateString('en-MY', { day: '2-digit', month: 'short', year: 'numeric' });
}

/* ── Calendar builder ── */
const CAL_MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const CAL_DAYS   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

function buildCalendar(year, month, bookings, blocks) {
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const daysInPrev  = new Date(year, month, 0).getDate();
  const today = new Date();
  const todayStr = today.getFullYear() + '-' + pad2(today.getMonth()+1) + '-' + pad2(today.getDate());

  // Build date maps
  const bookingMap = {};
  (bookings || []).forEach(b => {
    if (!bookingMap[b.date]) bookingMap[b.date] = [];
    bookingMap[b.date].push(b);
  });
  const blockMap = {};
  (blocks || []).forEach(bl => {
    if (!blockMap[bl.date]) blockMap[bl.date] = [];
    blockMap[bl.date].push(bl);
  });

  let html = '<table class="cal-grid"><thead><tr>';
  CAL_DAYS.forEach(d => { html += `<th>${d}</th>`; });
  html += '</tr></thead><tbody>';

  let day = 1;
  let nextMonthDay = 1;
  for (let row = 0; row < 6; row++) {
    if (day > daysInMonth) break;
    html += '<tr>';
    for (let col = 0; col < 7; col++) {
      const cellNum = row * 7 + col;
      let dayNum, dateStr, isOther = false;
      if (cellNum < firstDay) {
        dayNum = daysInPrev - firstDay + cellNum + 1;
        isOther = true;
        const m = month === 0 ? 12 : month;
        const y = month === 0 ? year - 1 : year;
        dateStr = y + '-' + pad2(m) + '-' + pad2(dayNum);
      } else if (day > daysInMonth) {
        dayNum = nextMonthDay++;
        isOther = true;
        const m = month === 11 ? 1 : month + 2;
        const y = month === 11 ? year + 1 : year;
        dateStr = y + '-' + pad2(m) + '-' + pad2(dayNum);
      } else {
        dayNum = day++;
        dateStr = year + '-' + pad2(month + 1) + '-' + pad2(dayNum);
      }

      const bks  = bookingMap[dateStr] || [];
      const blks = blockMap[dateStr]   || [];
      const isToday   = dateStr === todayStr;
      const hasBook   = bks.length  > 0;
      const hasBlock  = blks.length > 0;

      let cls = 'cal-day';
      if (isOther)  cls += ' other-month';
      if (isToday)  cls += ' today';
      if (hasBook)  cls += ' has-booking';
      if (hasBlock) cls += ' has-block';

      let dotsHtml = '';
      if (hasBook || hasBlock) {
        dotsHtml = '<div class="cal-dots">';
        bks.forEach(b => {
          const dc = b.status === 'pending' ? 'cal-dot-pending' : 'cal-dot-booking';
          dotsHtml += `<span class="cal-dot ${dc}" title="${b.ref} — ${b.lab}"></span>`;
        });
        blks.forEach(() => { dotsHtml += '<span class="cal-dot cal-dot-block" title="Blocked"></span>'; });
        dotsHtml += '</div>';
      }

      html += `<td class="cal-cell"><div class="${cls}"><div class="cal-day-num">${dayNum}</div>${dotsHtml}</div></td>`;
    }
    html += '</tr>';
  }
  html += '</tbody></table>';
  return html;
}

function pad2(n) { return String(n).padStart(2, '0'); }

/* ── Init page-specific behaviour ── */
document.addEventListener('DOMContentLoaded', () => {
  // Highlight current sidebar link (PHP sets data-page on body)
  const page = document.body.dataset.page || 'dashboard';
  document.querySelectorAll('.sb-link[data-page]').forEach(el => {
    if (el.dataset.page === page) el.classList.add('active');
  });
});
