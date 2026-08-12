<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin Dashboard — UniKLAB RCMP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet"/>
  <style>
    :root{--teal:#0b3a66;--teal-mid:#072a4a;--teal-light:#e8f0fa;--gray:#15a39d;--blue:#1565a0;--blue-light:#e3eef8;--violet:#5b3fa0;--violet-light:#f0ecf9;--white:#fff;--off:#f3f7fc;--border:#d3e0ee;--text:#10253d;--text-mid:#3f5a77;--text-light:#6f8aa5;--success:#1a7a4a;--success-bg:#e6f4ed;--danger:#b91c1c;--danger-bg:#fef2f2;--warn-bg:#fef9ec;--warn:#7a5c0a;--block-bg:#fff7ed;--block:#c2410c;--block-border:#fed7aa;--radius-sm:4px;--radius:8px;--radius-lg:14px;--shadow-sm:0 2px 8px rgba(9,34,57,.07);--shadow:0 12px 28px rgba(8,34,58,.12);--font-serif:'Libre Baskerville',Georgia,serif;--font-sans:'DM Sans',system-ui,sans-serif;--sidebar-w:224px;--topbar-h:56px}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{font-size:15px}
    body{font-family:var(--font-sans);color:var(--text);background:linear-gradient(135deg,#f8fbff 0%,#f2f7fc 100%);display:flex;min-height:100vh}
    a{color:inherit;text-decoration:none}
    button{font-family:var(--font-sans)}
    /* Sidebar */
    .sidebar{width:var(--sidebar-w);background:linear-gradient(180deg,#07223d 0%,#0a3157 50%,#0b3a66 100%);display:flex;flex-direction:column;flex-shrink:0;position:fixed;top:0;left:0;height:100vh;z-index:200;overflow-y:auto}
    .sb-brand{padding:18px 16px 14px;border-bottom:1px solid rgba(255,255,255,.1)}
    .sb-brand-row{display:flex;align-items:center;gap:10px}
    .sb-icon{width:34px;height:34px;background:rgba(255,255,255,.15);border-radius:6px;display:flex;align-items:center;justify-content:center;font-family:var(--font-serif);font-weight:700;color:#fff;font-size:.8rem;flex-shrink:0}
    .sb-name{font-family:var(--font-serif);font-size:.92rem;font-weight:700;color:#fff}
    .sb-sub{font-size:.62rem;color:rgba(255,255,255,.45);letter-spacing:.1em;text-transform:uppercase;margin-top:2px}
    .sb-section{padding:14px 14px 4px;font-size:.6rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.35)}
    .sb-link{display:flex;align-items:center;gap:9px;padding:8px 16px;font-size:.82rem;font-weight:500;color:rgba(255,255,255,.65);border-radius:0;transition:all .15s;cursor:pointer;border:none;background:none;width:100%;text-align:left}
    .sb-link:hover{background:rgba(255,255,255,.09);color:#fff}
    .sb-link.active{background:rgba(255,255,255,.16);color:#fff;font-weight:600}
    .sb-link-icon{width:18px;text-align:center;font-size:.9rem;opacity:.8;flex-shrink:0}
    .sb-badge{margin-left:auto;background:rgba(250,204,21,.25);color:#fde68a;font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:10px}
    .sb-bottom{margin-top:auto;padding:10px 12px;border-top:1px solid rgba(255,255,255,.1)}
    .sb-logout{display:flex;align-items:center;gap:8px;padding:8px 12px;font-size:.78rem;color:rgba(255,255,255,.55);cursor:pointer;border-radius:var(--radius-sm);transition:all .15s}
    .sb-logout:hover{background:rgba(255,255,255,.09);color:#fff}
    /* Main */
    .main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh}
    .topbar{height:var(--topbar-h);background:rgba(255,255,255,.92);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 26px;position:sticky;top:0;z-index:100;box-shadow:var(--shadow-sm);backdrop-filter:blur(10px)}
    .topbar-title{font-family:var(--font-serif);font-size:1rem;font-weight:700;color:var(--teal)}
    .topbar-right{display:flex;align-items:center;gap:12px}
    .admin-chip{display:flex;align-items:center;gap:7px;background:var(--teal-light);padding:5px 12px;border-radius:20px;font-size:.76rem;font-weight:600;color:var(--teal)}
    .content{padding:24px;flex:1}
    /* Views */
    .view{display:none}.view.active{display:block}
    /* Stats */
    .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:24px}
    .stat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px 18px;box-shadow:var(--shadow-sm);position:relative;overflow:hidden}
    .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--teal)}
    .stat-card--blue::before{background:var(--blue)}.stat-card--violet::before{background:var(--violet)}.stat-card--success::before{background:var(--success)}.stat-card--block::before{background:var(--block)}
    .stat-icon{position:absolute;top:14px;right:14px;font-size:1.4rem;opacity:.15}
    .stat-card-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-light);margin-bottom:6px}
    .stat-card-num{font-family:var(--font-serif);font-size:2rem;font-weight:700;color:var(--teal);line-height:1}
    .stat-card--blue .stat-card-num{color:var(--blue)}.stat-card--violet .stat-card-num{color:var(--violet)}.stat-card--success .stat-card-num{color:var(--success)}.stat-card--block .stat-card-num{color:var(--block)}
    .stat-card-sub{font-size:.73rem;color:var(--text-light);margin-top:4px}
    /* Dashboard grid */
    .dash-overview-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(320px,.75fr);gap:18px;align-items:start;margin-bottom:22px}
    @media(max-width:1100px){.dash-overview-grid{grid-template-columns:1fr}}
    /* Tabs */
    .tab-bar{display:flex;gap:2px;margin-bottom:18px;border-bottom:2px solid var(--border)}
    .tab-btn{padding:9px 16px;font-size:.81rem;font-weight:600;color:var(--text-light);background:none;border:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s;display:flex;align-items:center;gap:6px}
    .tab-btn:hover{color:var(--teal)}.tab-btn.active{color:var(--teal);border-bottom-color:var(--teal)}
    .tab-count{display:inline-flex;align-items:center;justify-content:center;min-width:17px;height:17px;padding:0 4px;border-radius:9px;font-size:.62rem;font-weight:700;background:var(--teal-light);color:var(--teal)}
    .tab-count--warn{background:var(--warn-bg);color:var(--warn)}
    .tab-panel{display:none}.tab-panel.active{display:block}
    /* Tables */
    .tbl-wrap{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm)}
    .tbl-toolbar{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border);gap:10px;flex-wrap:wrap}
    .tbl-toolbar-title{font-family:var(--font-serif);font-size:.9rem;font-weight:700;color:var(--teal)}
    .tbl-search{padding:6px 11px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:var(--font-sans);font-size:.8rem;width:210px;outline:none;transition:border .15s}
    .tbl-search:focus{border-color:var(--teal)}
    .tbl-filter{padding:6px 9px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:var(--font-sans);font-size:.79rem;outline:none;background:var(--white);cursor:pointer}
    table{width:100%;border-collapse:collapse;font-size:.81rem}
    thead th{background:var(--off);color:var(--text-light);font-weight:700;font-size:.66rem;letter-spacing:.07em;text-transform:uppercase;padding:9px 13px;text-align:left;border-bottom:1px solid var(--border)}
    tbody td{padding:10px 13px;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle}
    tbody tr:last-child td{border-bottom:none}
    tbody tr:hover{background:var(--off)}
    .ref-code{font-size:.7rem;color:var(--text-light);font-family:monospace}
    .type-chip{display:inline-flex;align-items:center;gap:4px;font-size:.67rem;font-weight:700;padding:2px 7px;border-radius:8px;border:1px solid transparent}
    .type-chip--research{background:var(--teal-light);color:var(--teal);border-color:rgba(11,58,102,.15)}
    .type-chip--csl{background:var(--blue-light);color:var(--blue);border-color:rgba(21,101,160,.15)}
    .type-chip--pharma{background:var(--violet-light);color:var(--violet);border-color:rgba(91,63,160,.15)}
    .type-chip--block{background:var(--block-bg);color:var(--block);border-color:var(--block-border)}
    .status-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:.68rem;font-weight:700}
    .status-approved{background:var(--success-bg);color:var(--success)}.status-pending{background:var(--warn-bg);color:var(--warn)}.status-rejected{background:var(--danger-bg);color:var(--danger)}.status-cancelled,.status-inactive{background:var(--off);color:var(--text-light)}.status-blocked{background:var(--block-bg);color:var(--block)}.status-active{background:var(--success-bg);color:var(--success)}.status-maintenance{background:var(--warn-bg);color:var(--warn)}
    .action-btns{display:flex;gap:5px;flex-wrap:wrap}
    .btn-xs{padding:3px 9px;font-size:.7rem;font-weight:600;border-radius:var(--radius-sm);cursor:pointer;border:1.5px solid;transition:all .15s}
    .btn-approve{background:var(--success-bg);color:var(--success);border-color:var(--success)}.btn-approve:hover{background:var(--success);color:#fff}
    .btn-reject{background:var(--danger-bg);color:var(--danger);border-color:var(--danger)}.btn-reject:hover{background:var(--danger);color:#fff}
    .btn-view{background:var(--teal-light);color:var(--teal);border-color:rgba(11,58,102,.2)}.btn-view:hover{border-color:var(--teal)}
    .btn-delete{background:var(--danger-bg);color:var(--danger);border-color:var(--danger)}.btn-delete:hover{background:var(--danger);color:#fff}
    /* Section header */
    .section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
    .section-title{font-family:var(--font-serif);font-size:1rem;font-weight:700;color:var(--text)}
    .btn-primary{padding:8px 16px;background:var(--teal);color:#fff;border:none;border-radius:var(--radius);font-family:var(--font-sans);font-size:.8rem;font-weight:600;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center;gap:6px}
    .btn-primary:hover{background:var(--teal-mid)}.btn-primary:disabled{opacity:.5;cursor:not-allowed}
    .btn-secondary{padding:7px 14px;background:var(--white);color:var(--teal);border:1.5px solid var(--border);border-radius:var(--radius);font-family:var(--font-sans);font-size:.8rem;font-weight:600;cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;gap:6px}
    .btn-secondary:hover{border-color:var(--teal);background:var(--teal-light)}
    /* Calendar */
    .cal-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm)}
    .cal-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border);background:var(--off)}
    .cal-month-label{font-family:var(--font-serif);font-size:.9rem;font-weight:700;color:var(--teal)}
    .cal-nav{background:none;border:1.5px solid var(--border);border-radius:var(--radius-sm);width:26px;height:26px;font-size:1rem;cursor:pointer;color:var(--text-mid);display:flex;align-items:center;justify-content:center;transition:all .15s;line-height:1}
    .cal-nav:hover{border-color:var(--teal);color:var(--teal)}
    .cal-weekdays{display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid var(--border)}
    .cal-weekdays>div{padding:5px 2px;text-align:center;font-size:.6rem;font-weight:700;color:var(--text-light);text-transform:uppercase}
    .cal-grid{display:grid;grid-template-columns:repeat(7,1fr)}
    .cal-day{min-height:52px;padding:4px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s}
    .cal-day:nth-child(7n){border-right:none}
    .cal-day:hover{background:var(--teal-light)}
    .cal-day.today{background:var(--teal-light)}
    .cal-day.today .cal-day-num{background:var(--teal);color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center}
    .cal-day.other-month{background:var(--off)}.cal-day.other-month .cal-day-num{color:var(--border)}
    .cal-day.selected-day{background:#dbeafe!important;outline:2px solid var(--blue);outline-offset:-2px}
    .cal-day.selected-day .cal-day-num{color:var(--blue);font-weight:700}
    .cal-day-num{font-size:.7rem;font-weight:600;color:var(--text);width:20px;height:20px;display:flex;align-items:center;justify-content:center;margin-bottom:2px}
    .cal-day-dots{display:flex;flex-wrap:wrap;gap:2px}
    .cal-day-dot{width:6px;height:6px;border-radius:50%}
    .cal-day-dot--teal{background:var(--teal)}.cal-day-dot--blue{background:var(--blue)}.cal-day-dot--violet{background:var(--violet)}.cal-day-dot--block{background:var(--block)}.cal-day-dot--pending{opacity:.4}
    .cal-legend{display:flex;gap:10px;padding:9px 14px;border-top:1px solid var(--border);flex-wrap:wrap}
    .cal-leg-item{display:flex;align-items:center;gap:4px;font-size:.7rem;color:var(--text-mid)}
    .cal-dot{width:7px;height:7px;border-radius:50%}
    .cal-dot--teal{background:var(--teal)}.cal-dot--blue{background:var(--blue)}.cal-dot--violet{background:var(--violet)}.cal-dot--block{background:var(--block)}
    /* Detail drawer */
    .detail-drawer{display:none;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);margin-top:10px;overflow:hidden;box-shadow:var(--shadow-sm)}
    .detail-drawer.open{display:block}
    .detail-drawer-header{display:flex;align-items:center;justify-content:space-between;padding:11px 15px;background:var(--teal);color:#fff}
    .detail-drawer-header span{font-family:var(--font-serif);font-size:.86rem;font-weight:700}
    .detail-drawer--dashboard{margin-top:0;height:100%}.detail-drawer--dashboard.open{display:flex;flex-direction:column}.detail-drawer--dashboard #dc-detail-body{flex:1}
    .drawer-close{background:rgba(255,255,255,.15);border:none;border-radius:50%;width:21px;height:21px;color:#fff;cursor:pointer;font-size:.75rem;display:flex;align-items:center;justify-content:center}
    .booking-item{padding:10px 15px;border-bottom:1px solid var(--border);font-size:.8rem}
    .booking-item:last-child{border-bottom:none}
    .bi-type{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-bottom:2px}
    .bi-type--teal{color:var(--teal)}.bi-type--blue{color:var(--blue)}.bi-type--violet{color:var(--violet)}.bi-type--block{color:var(--block)}
    .bi-name{font-weight:600;color:var(--text)}.bi-time{color:var(--text-mid);font-size:.76rem;margin-top:1px}.bi-rooms{color:var(--text-light);font-size:.7rem;margin-top:1px}
    .status-inline{font-size:.64rem;font-weight:700;padding:1px 5px;border-radius:7px}
    .status-inline.status-approved{background:var(--success-bg);color:var(--success)}.status-inline.status-pending{background:var(--warn-bg);color:var(--warn)}.status-inline.status-rejected{background:var(--danger-bg);color:var(--danger)}.status-inline.status-blocked{background:var(--block-bg);color:var(--block)}
    /* Modal */
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center}
    .modal-overlay.open{display:flex}
    .modal{background:var(--white);border-radius:var(--radius-lg);box-shadow:0 20px 60px rgba(0,0,0,.2);width:92%;max-width:660px;max-height:90vh;overflow-y:auto}
    .modal-header{display:flex;align-items:center;justify-content:space-between;padding:15px 20px;border-bottom:1px solid var(--border);background:var(--off)}
    .modal-header h3{font-family:var(--font-serif);font-size:.97rem;font-weight:700;color:var(--teal)}
    .modal-close{background:none;border:none;font-size:1.1rem;cursor:pointer;color:var(--text-light)}
    .modal-body{padding:20px}.modal-section{margin-bottom:18px}
    .modal-section-title{font-size:.66rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-light);border-bottom:1.5px solid var(--border);padding-bottom:5px;margin-bottom:11px}
    .modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px}
    .modal-field{display:flex;flex-direction:column;gap:2px;padding:8px 11px;background:var(--off);border-radius:var(--radius-sm)}
    .modal-field span{font-size:.62rem;color:var(--text-light);font-weight:700;text-transform:uppercase;letter-spacing:.05em}
    .modal-field strong{font-size:.81rem;color:var(--text)}.modal-field.full{grid-column:1/-1}
    .room-reassign-section{background:var(--blue-light);border:1.5px solid #bcd5f0;border-radius:var(--radius);padding:13px 15px;margin-bottom:14px}
    .room-reassign-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--blue);margin-bottom:9px}
    .room-current{font-size:.8rem;color:var(--text-mid);margin-bottom:7px}
    .room-select-wrap{display:flex;align-items:center;gap:7px;flex-wrap:wrap}
    .room-select{padding:6px 9px;border:1.5px solid #bcd5f0;border-radius:var(--radius-sm);font-family:var(--font-sans);font-size:.8rem;outline:none;background:var(--white);color:var(--text);flex:1;min-width:160px}
    .room-select:focus{border-color:var(--blue)}
    .btn-reassign{padding:6px 13px;font-size:.76rem;font-weight:700;background:var(--blue);color:#fff;border:none;border-radius:var(--radius-sm);cursor:pointer}
    .reassign-note{font-size:.7rem;color:var(--blue);margin-top:5px;opacity:.8}
    .remark-wrap{margin-top:4px}.remark-label{display:block;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-light);margin-bottom:5px}
    .remark-input{width:100%;min-height:72px;padding:9px 11px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:var(--font-sans);font-size:.81rem;color:var(--text);background:var(--white);resize:vertical;outline:none}
    .remark-input:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(11,58,102,.1)}
    .audit-trail{background:var(--off);border-radius:var(--radius);padding:11px 13px}
    .audit-row{display:flex;align-items:flex-start;gap:9px;font-size:.76rem;padding:6px 0;border-bottom:1px solid var(--border)}
    .audit-row:last-child{border-bottom:none}
    .audit-dot{width:7px;height:7px;border-radius:50%;margin-top:4px;flex-shrink:0}
    .audit-dot--approved{background:var(--success)}.audit-dot--rejected{background:var(--danger)}.audit-dot--pending{background:var(--warn)}.audit-dot--reassigned{background:var(--blue)}.audit-dot--created{background:var(--gray,#15a39d)}.audit-dot--blocked{background:var(--block)}
    .audit-content{flex:1}.audit-action{font-weight:600;color:var(--text)}.audit-meta{color:var(--text-light);font-size:.7rem;margin-top:1px}.audit-detail{color:var(--text-mid);font-size:.73rem;margin-top:2px;font-style:italic}
    .modal-footer{padding:13px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:7px}
    /* Forms */
    .block-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .form-group-block{display:flex;flex-direction:column;gap:4px}.form-group-block.full{grid-column:1/-1}
    .form-label-block{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-light)}
    .form-ctrl{padding:7px 11px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:var(--font-sans);font-size:.82rem;outline:none;background:var(--white);color:var(--text);transition:border .15s}
    .form-ctrl:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(11,58,102,.08)}
    .form-ctrl-lg{min-height:68px;resize:vertical}
    /* Block entries */
    .block-entry{display:flex;align-items:flex-start;gap:10px;padding:10px 13px;border-bottom:1px solid var(--border);font-size:.79rem}
    .block-entry:last-child{border-bottom:none}
    .block-entry-icon{width:30px;height:30px;border-radius:6px;background:var(--block-bg);display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;margin-top:1px}
    .block-entry-body{flex:1}.block-entry-title{font-weight:600;color:var(--text)}.block-entry-meta{color:var(--text-light);font-size:.72rem;margin-top:2px}.block-entry-rooms{color:var(--text-mid);font-size:.72rem;margin-top:1px}
    .block-entry-actions{display:flex;gap:4px;align-items:center}
    .recurring-badge{display:inline-flex;align-items:center;gap:3px;font-size:.62rem;font-weight:700;padding:1px 6px;border-radius:8px;background:var(--violet-light);color:var(--violet);margin-left:6px}
    /* Misc */
    .pending-section-note{background:var(--warn-bg);border:1px solid rgba(122,92,10,.15);border-radius:var(--radius);padding:9px 14px;font-size:.77rem;color:var(--warn);margin-bottom:14px;display:flex;align-items:center;gap:7px}
    .empty-state{padding:40px 20px;text-align:center;color:var(--text-light);font-size:.84rem}
    .empty-state strong{display:block;font-size:.92rem;color:var(--text-mid);margin-bottom:6px}
    .alert-info-sm{background:var(--teal-light);border:1px solid rgba(11,58,102,.15);color:var(--teal);border-radius:var(--radius);padding:9px 12px;font-size:.77rem;margin-bottom:12px}
    /* Toast */
    .toast{position:fixed;bottom:22px;right:22px;background:var(--text);color:#fff;padding:9px 16px;border-radius:var(--radius);font-size:.8rem;font-weight:600;box-shadow:var(--shadow);z-index:9999;transform:translateY(70px);opacity:0;transition:all .28s}
    .toast.show{transform:translateY(0);opacity:1}.toast.toast-success{background:var(--success)}.toast.toast-info{background:var(--blue)}.toast.toast-warn{background:var(--block)}
    /* Report */
    .report-wrap{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);padding:18px 20px;margin-top:20px}
    .report-header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap}
    .report-title{font-family:var(--font-serif);font-size:1rem;font-weight:700;color:var(--text)}
    .report-meta{font-size:.72rem;color:var(--text-light);letter-spacing:.08em;text-transform:uppercase}
    .report-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
    .report-card{border:1px solid var(--border);border-radius:var(--radius);padding:12px 14px;background:var(--off)}
    .report-card h4{font-size:.66rem;letter-spacing:.08em;text-transform:uppercase;color:var(--text-light);margin-bottom:8px}
    .report-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .report-metric{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 9px}
    .report-metric span{display:block;font-size:.64rem;color:var(--text-light);letter-spacing:.05em;text-transform:uppercase}
    .report-metric strong{display:block;margin-top:4px;font-size:1rem;color:var(--text);font-family:var(--font-serif)}
    .report-list{display:flex;flex-direction:column;gap:8px}
    .report-item{display:flex;flex-direction:column;gap:2px;padding:8px 10px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--white);font-size:.78rem}
    .report-item-title{font-weight:600;color:var(--text)}.report-item-meta{color:var(--text-light);font-size:.7rem}
    .report-status{display:grid;gap:6px}
    .report-status-chip{display:flex;justify-content:space-between;gap:8px;padding:7px 9px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--white);font-size:.75rem}
    .report-status-chip strong{color:var(--text)}

    /* ======================================================
       SCHEDULE & BLOCK — NEW CALENDAR-FIRST FLOW
       ====================================================== */
    .sched-layout{display:grid;grid-template-columns:290px 1fr;gap:16px;align-items:start}
    @media(max-width:960px){.sched-layout{grid-template-columns:1fr}}

    /* Left: calendar col */
    .sched-cal-col{}
    .sched-existing-panel{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);margin-top:12px;overflow:hidden;box-shadow:var(--shadow-sm)}
    .sched-existing-hdr{padding:9px 14px;background:var(--block-bg);border-bottom:1px solid var(--block-border);font-size:.75rem;font-weight:700;color:var(--block)}

    /* Right: steps panel */
    .sched-steps-panel{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);min-height:420px;display:flex;flex-direction:column}

    /* Step indicator */
    .sched-step-bar{display:flex;align-items:center;padding:14px 20px;border-bottom:1px solid var(--border);background:var(--off);gap:0;flex-shrink:0}
    .s-step{display:flex;align-items:center;gap:6px}
    .s-step-num{width:22px;height:22px;border-radius:50%;background:var(--border);color:var(--text-light);font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;transition:all .2s;flex-shrink:0}
    .s-step.active .s-step-num{background:var(--teal);color:#fff}
    .s-step.done .s-step-num{background:var(--success);color:#fff}
    .s-step-lbl{font-size:.72rem;font-weight:600;color:var(--text-light);transition:color .2s;white-space:nowrap}
    .s-step.active .s-step-lbl{color:var(--teal)}.s-step.done .s-step-lbl{color:var(--success)}
    .s-line{flex:1;height:2px;background:var(--border);margin:0 6px;min-width:10px;transition:background .2s}
    .s-line.done{background:var(--success)}

    /* Step panels */
    .s-panel{padding:26px 24px;flex:1;display:flex;flex-direction:column}
    .s-panel-empty{align-items:center;justify-content:center;text-align:center}
    .s-empty-icon{font-size:2.6rem;opacity:.5;margin-bottom:12px}
    .s-empty-title{font-family:var(--font-serif);font-size:1.05rem;font-weight:700;color:var(--text);margin-bottom:6px}
    .s-empty-sub{font-size:.82rem;color:var(--text-light);line-height:1.55;max-width:320px}
    .s-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px;flex-wrap:wrap}
    .s-panel-title{font-family:var(--font-serif);font-size:.95rem;font-weight:700;color:var(--text);margin-bottom:2px}
    .s-panel-sub{font-size:.78rem;color:var(--text-light)}
    .s-date-chip{background:var(--teal);color:#fff;padding:4px 11px;border-radius:11px;font-size:.74rem;font-weight:700;white-space:nowrap}

    /* Lab category cards */
    .sched-lab-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:9px;margin-bottom:4px}
    @media(max-width:680px){.sched-lab-grid{grid-template-columns:1fr}}
    .sched-lab-card{border:2px solid var(--border);border-radius:var(--radius-lg);padding:16px 10px;text-align:center;cursor:pointer;background:var(--off);transition:all .18s;font-family:var(--font-sans)}
    .sched-lab-card:hover{border-color:var(--teal);background:var(--teal-light)}
    .sched-lab-card.selected{border-color:var(--teal);background:var(--teal-light);box-shadow:0 0 0 3px rgba(11,58,102,.1)}
    .sched-lab-card-icon{font-size:1.55rem;margin-bottom:5px}
    .sched-lab-card-name{font-size:.82rem;font-weight:700;color:var(--text);margin-bottom:2px}
    .sched-lab-card-sub{font-size:.67rem;color:var(--text-light)}

    /* Room checkboxes */
    .sched-rooms-section{margin-top:16px}
    .sched-rooms-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-light);margin-bottom:9px}
    .sched-rooms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:7px}
    .sched-room-item{display:flex;align-items:center;gap:8px;padding:8px 11px;border:1.5px solid var(--border);border-radius:var(--radius);cursor:pointer;font-size:.78rem;color:var(--text);transition:all .15s;background:var(--white);user-select:none}
    .sched-room-item:hover{border-color:var(--teal);background:var(--teal-light)}
    .sched-room-item.checked{border-color:var(--teal);background:var(--teal-light)}
    .sched-room-item input{accent-color:var(--teal);width:13px;height:13px;flex-shrink:0;pointer-events:none}
    .s-next-btn{margin-top:14px;align-self:flex-end}

    /* Duration bar */
    .sched-dur-bar{display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap}
    .sched-dur-lbl{font-size:.74rem;font-weight:600;color:var(--text-mid);flex-shrink:0}
    .dur-btn{padding:4px 12px;border:1.5px solid var(--border);border-radius:20px;font-size:.74rem;font-weight:600;color:var(--text-mid);background:var(--white);cursor:pointer;transition:all .14s;font-family:var(--font-sans)}
    .dur-btn:hover{border-color:var(--teal);color:var(--teal)}
    .dur-btn.active{background:var(--teal);color:#fff;border-color:var(--teal)}

    /* Time grid */
    .tg-legend{display:flex;gap:14px;margin-bottom:10px;flex-wrap:wrap}
    .tg-legend-item{display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--text-mid)}
    .tg-dot{width:13px;height:13px;border-radius:50%;flex-shrink:0}
    .tg-dot--avail{background:var(--white);border:2px solid #bcd5f0}
    .tg-dot--booked{background:var(--text-light)}
    .tg-dot--blocked{background:var(--block)}
    .tg-dot--selected{background:var(--blue)}
    .tg-dot--range{background:rgba(21,101,160,.18);border:2px solid var(--blue)}

    .tg-wrap{overflow-x:auto;border:1px solid var(--border);border-radius:var(--radius);background:#fafcff}
    .tg-inner{display:inline-block;min-width:100%}
    .tg-header-row{display:flex;background:var(--off);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:3}
    .tg-corner{width:170px;min-width:170px;padding:6px 10px;font-size:.64rem;font-weight:700;color:var(--text-light);border-right:1px solid var(--border);flex-shrink:0;display:flex;align-items:center;position:sticky;left:0;background:var(--off);z-index:4}
    .tg-time-hdr{width:62px;min-width:62px;padding:5px 4px;text-align:center;font-size:.62rem;font-weight:700;color:var(--text-light);border-right:1px solid var(--border);flex-shrink:0;line-height:1.3}
    .tg-time-hdr:last-child{border-right:none}
    .tg-row{display:flex;border-bottom:1px solid var(--border)}
    .tg-row:last-child{border-bottom:none}
    .tg-room-lbl{width:170px;min-width:170px;padding:7px 10px;font-size:.73rem;font-weight:600;color:var(--text);border-right:1px solid var(--border);flex-shrink:0;display:flex;align-items:center;background:var(--white);position:sticky;left:0;z-index:2;line-height:1.3}
    .tg-cell{width:62px;min-width:62px;height:48px;border-right:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;cursor:default;position:relative}
    .tg-cell:last-child{border-right:none}
    .slot-btn{width:34px;height:34px;border-radius:50%;border:2px solid #bcd5f0;background:var(--white);display:flex;align-items:center;justify-content:center;font-size:.56rem;font-weight:700;color:var(--text-light);transition:all .14s;cursor:pointer;position:relative}
    .slot-btn:hover:not(.slot-blocked):not(.slot-booked){border-color:var(--blue);background:var(--blue-light);color:var(--blue)}
    .slot-btn.slot-blocked{background:var(--block);border-color:var(--block);color:#fff;cursor:not-allowed}
    .slot-btn.slot-booked{background:var(--text-light);border-color:var(--text-light);color:#fff;cursor:not-allowed}
    .slot-btn.slot-selected{background:var(--blue);border-color:var(--blue);color:#fff}
    .slot-btn.slot-range{background:rgba(21,101,160,.15);border-color:var(--blue);color:var(--blue)}

    /* Selection bar */
    .tg-selection-bar{display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding:10px 14px;background:var(--blue-light);border:1px solid #bcd5f0;border-radius:var(--radius);font-size:.8rem;font-weight:600;color:var(--blue);flex-wrap:wrap;gap:8px}

    /* Step 4 summary strip */
    .step4-summary{background:var(--teal-light);border:1px solid rgba(11,58,102,.15);border-radius:var(--radius);padding:10px 14px;font-size:.78rem;color:var(--teal);margin-bottom:16px;line-height:1.5}
    .step4-summary strong{display:block;font-size:.82rem;font-weight:700;margin-bottom:3px}
  </style>
</head>
<body>
<div class="toast" id="toast"></div>
<aside class="sidebar">
  <div class="sb-brand"><div class="sb-brand-row"><div class="sb-icon">LB</div><div><div class="sb-name">UniKLAB RCMP</div><div class="sb-sub">Admin Panel</div></div></div></div>
  <div class="sb-section">Overview</div>
  <button class="sb-link active" onclick="showView('dashboard',this)"><span class="sb-link-icon">📊</span>Dashboard</button>
  <button class="sb-link" onclick="showView('calendar',this)"><span class="sb-link-icon">📅</span>Calendar View</button>
  <div class="sb-section">Bookings</div>
  <button class="sb-link" onclick="showView('all',this)"><span class="sb-link-icon">📋</span>All Bookings</button>
  <button class="sb-link" onclick="showView('research',this)"><span class="sb-link-icon">🧪</span>Research Labs <span class="sb-badge" id="sb-research-count">0</span></button>
  <button class="sb-link" onclick="showView('csl',this)"><span class="sb-link-icon">🏥</span>CSL Labs <span class="sb-badge" id="sb-csl-count">0</span></button>
  <button class="sb-link" onclick="showView('pharma',this)"><span class="sb-link-icon">⚗️</span>Pharma Labs <span class="sb-badge" id="sb-pharma-count">0</span></button>
  <div class="sb-section">Management</div>
  <button class="sb-link" id="sb-schedule-link" onclick="showView('schedule',this)"><span class="sb-link-icon">🗓️</span>Schedule &amp; Block</button>
  <button class="sb-link" onclick="showView('labs',this)"><span class="sb-link-icon">🏷️</span>Manage Labs</button>
  <button class="sb-link" onclick="showView('staff',this)"><span class="sb-link-icon">👥</span>Manage Staff</button>
  <button class="sb-link" onclick="showView('report',this)"><span class="sb-link-icon">📄</span>System Report</button>
  <div class="sb-bottom"><a href="login.php" class="sb-logout">🔓 Sign Out</a></div>
</aside>

<div class="main">
  <div class="topbar">
    <div class="topbar-title" id="topbarTitle">Dashboard</div>
    <div class="topbar-right">
      <div class="admin-chip">👤 Dr. Sarah Admin</div>
      <a href="../index.php" style="font-size:.78rem;color:var(--text-light);">← Public Site</a>
    </div>
  </div>
  <div class="content">

    <!-- DASHBOARD -->
    <div id="view-dashboard" class="view active">
      <div class="stats-row">
        <div class="stat-card"><div class="stat-icon">📋</div><div class="stat-card-label">Total Bookings</div><div class="stat-card-num" id="stat-all">0</div><div class="stat-card-sub">All categories</div></div>
        <div class="stat-card stat-card--success"><div class="stat-icon">✅</div><div class="stat-card-label">Approved</div><div class="stat-card-num" id="stat-approved">0</div><div class="stat-card-sub">Confirmed sessions</div></div>
        <div class="stat-card"><div class="stat-icon">🧪</div><div class="stat-card-label">Research Labs</div><div class="stat-card-num" id="stat-research">0</div><div class="stat-card-sub">AZ + Avicenna</div></div>
        <div class="stat-card stat-card--blue"><div class="stat-icon">🏥</div><div class="stat-card-label">CSL Labs</div><div class="stat-card-num" id="stat-csl">0</div><div class="stat-card-sub">CSL1 &amp; CSL2</div></div>
        <div class="stat-card stat-card--violet"><div class="stat-icon">⚗️</div><div class="stat-card-label">Pharma Labs</div><div class="stat-card-num" id="stat-pharma">0</div><div class="stat-card-sub">CL · MDLP · PL1 · PL2</div></div>
        <div class="stat-card stat-card--block"><div class="stat-icon">🚫</div><div class="stat-card-label">Blocked / Classes</div><div class="stat-card-num" id="stat-blocks">0</div><div class="stat-card-sub">Scheduled blocks</div></div>
      </div>
      <div class="dash-overview-grid">
        <div><div class="cal-card" id="dashCal"><div class="cal-header"><button class="cal-nav" id="dc-prev">&#8249;</button><span class="cal-month-label" id="dc-label"></span><button class="cal-nav" id="dc-next">&#8250;</button></div><div class="cal-weekdays"><div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div></div><div class="cal-grid" id="dc-grid"></div><div class="cal-legend"><span class="cal-leg-item"><span class="cal-dot cal-dot--teal"></span>Research</span><span class="cal-leg-item"><span class="cal-dot cal-dot--blue"></span>CSL</span><span class="cal-leg-item"><span class="cal-dot cal-dot--violet"></span>Pharma</span><span class="cal-leg-item"><span class="cal-dot cal-dot--block"></span>Blocked</span></div></div></div>
        <div><div class="detail-drawer detail-drawer--dashboard" id="dc-detail"><div class="detail-drawer-header"><span id="dc-detail-date">—</span><button class="drawer-close" onclick="closeDcDetail()">✕</button></div><div id="dc-detail-body"></div></div></div>
      </div>
      <div class="section-header" style="margin-top:0;"><div class="section-title">Upcoming Blocks &amp; Classes</div><button class="btn-secondary" onclick="showView('schedule',document.getElementById('sb-schedule-link'))">Manage →</button></div>
      <div class="tbl-wrap"><div id="upcoming-blocks-list"></div></div>
    </div>

    <!-- CALENDAR VIEW -->
    <div id="view-calendar" class="view">
      <div class="cal-card" style="max-width:900px;"><div class="cal-header"><button class="cal-nav" id="fc-prev">&#8249;</button><span class="cal-month-label" id="fc-label"></span><button class="cal-nav" id="fc-next">&#8250;</button><div style="margin-left:12px;display:flex;align-items:center;gap:8px;"><label style="font-size:.82rem;color:var(--text-light);">Show:</label><select id="fc-filter" class="tbl-filter"><option value="all">All</option><option value="research">Research</option><option value="csl">CSL</option><option value="pharma">Pharma</option></select></div></div><div class="cal-weekdays"><div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div></div><div class="cal-grid" id="fc-grid"></div><div class="cal-legend" style="padding:10px 16px;"><span class="cal-leg-item"><span class="cal-dot cal-dot--teal"></span>Research Labs</span><span class="cal-leg-item"><span class="cal-dot cal-dot--blue"></span>CSL Labs</span><span class="cal-leg-item"><span class="cal-dot cal-dot--violet"></span>Pharma Labs</span><span class="cal-leg-item"><span class="cal-dot cal-dot--block"></span>Blocked</span></div></div>
      <div class="detail-drawer" id="fc-detail" style="max-width:900px;margin-top:14px;"><div class="detail-drawer-header"><span id="fc-detail-date">—</span><button class="drawer-close" onclick="closeFcDetail()">✕</button></div><div id="fc-detail-body"></div></div>
    </div>

    <!-- ALL BOOKINGS -->
    <div id="view-all" class="view">
      <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab(this,'atab-all')">All <span class="tab-count" id="tc-all">0</span></button>
        <button class="tab-btn" onclick="switchTab(this,'atab-research')">🧪 Research <span class="tab-count" id="tc-research">0</span></button>
        <button class="tab-btn" onclick="switchTab(this,'atab-csl')">🏥 CSL <span class="tab-count" id="tc-csl">0</span></button>
        <button class="tab-btn" onclick="switchTab(this,'atab-pharma')">⚗️ Pharma <span class="tab-count" id="tc-pharma">0</span></button>
        <button class="tab-btn" onclick="switchTab(this,'atab-pending')">⏳ Pending <span class="tab-count tab-count--warn" id="tc-pending">0</span></button>
      </div>
      <div id="atab-all" class="tab-panel active"><div class="tbl-wrap"><div class="tbl-toolbar"><input class="tbl-search" placeholder="Search name, ID, ref…" oninput="filterTable(this,'tbl-a-all')"/><select class="tbl-filter" onchange="filterStatus(this,'tbl-a-all')"><option value="">All Statuses</option><option>approved</option><option>pending</option><option>rejected</option></select></div><div style="overflow-x:auto;"><table id="tbl-a-all"><thead><tr><th>Ref</th><th>Applicant</th><th>Type</th><th>Date</th><th>Time</th><th>Rooms</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>
      <div id="atab-research" class="tab-panel"><div class="tbl-wrap"><div class="tbl-toolbar"><input class="tbl-search" placeholder="Search…" oninput="filterTable(this,'tbl-a-research')"/></div><div style="overflow-x:auto;"><table id="tbl-a-research"><thead><tr><th>Ref</th><th>Applicant</th><th>Type</th><th>Date</th><th>Time</th><th>Rooms</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>
      <div id="atab-csl" class="tab-panel"><div class="tbl-wrap"><div class="tbl-toolbar"><input class="tbl-search" placeholder="Search…" oninput="filterTable(this,'tbl-a-csl')"/></div><div style="overflow-x:auto;"><table id="tbl-a-csl"><thead><tr><th>Ref</th><th>Applicant</th><th>Type</th><th>Date</th><th>Time</th><th>Rooms</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>
      <div id="atab-pharma" class="tab-panel"><div class="tbl-wrap"><div class="tbl-toolbar"><input class="tbl-search" placeholder="Search…" oninput="filterTable(this,'tbl-a-pharma')"/></div><div style="overflow-x:auto;"><table id="tbl-a-pharma"><thead><tr><th>Ref</th><th>Applicant</th><th>Type</th><th>Date</th><th>Time</th><th>Rooms</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>
      <div id="atab-pending" class="tab-panel"><div class="tbl-wrap"><div class="tbl-toolbar"><input class="tbl-search" placeholder="Search…" oninput="filterTable(this,'tbl-a-pending')"/></div><div style="overflow-x:auto;"><table id="tbl-a-pending"><thead><tr><th>Ref</th><th>Applicant</th><th>Type</th><th>Date</th><th>Time</th><th>Rooms</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>
    </div>

    <!-- RESEARCH / CSL / PHARMA PENDING -->
    <div id="view-research" class="view"><div class="pending-section-note">⏳ Pending only — <strong style="cursor:pointer;text-decoration:underline;" onclick="showView('all',null)">All Bookings</strong> for full list.</div><div class="tbl-wrap"><div class="tbl-toolbar"><span class="tbl-toolbar-title">🧪 Research Labs — Pending</span><input class="tbl-search" placeholder="Search…" oninput="filterTable(this,'tbl-research')"/></div><div style="overflow-x:auto;"><table id="tbl-research"><thead><tr><th>Ref</th><th>Applicant</th><th>Date</th><th>Time</th><th>Rooms</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>
    <div id="view-csl" class="view"><div class="pending-section-note">⏳ Pending only — <strong style="cursor:pointer;text-decoration:underline;" onclick="showView('all',null)">All Bookings</strong> for full list.</div><div class="tbl-wrap"><div class="tbl-toolbar"><span class="tbl-toolbar-title">🏥 CSL Labs — Pending</span><input class="tbl-search" placeholder="Search…" oninput="filterTable(this,'tbl-csl')"/></div><div style="overflow-x:auto;"><table id="tbl-csl"><thead><tr><th>Ref</th><th>Applicant</th><th>Date</th><th>Time</th><th>Rooms</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>
    <div id="view-pharma" class="view"><div class="pending-section-note">⏳ Pending only — <strong style="cursor:pointer;text-decoration:underline;" onclick="showView('all',null)">All Bookings</strong> for full list.</div><div class="tbl-wrap"><div class="tbl-toolbar"><span class="tbl-toolbar-title">⚗️ Pharma Labs — Pending</span><input class="tbl-search" placeholder="Search…" oninput="filterTable(this,'tbl-pharma')"/></div><div style="overflow-x:auto;"><table id="tbl-pharma"><thead><tr><th>Ref</th><th>Applicant</th><th>Date</th><th>Time</th><th>Rooms</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>

    <!-- ======================================================
         SCHEDULE & BLOCK — NEW CALENDAR-FIRST FLOW
         ====================================================== -->
    <div id="view-schedule" class="view">
      <div class="section-header" style="margin-bottom:18px;">
        <div>
          <div class="section-title">Schedule &amp; Block Labs</div>
          <div style="font-size:.78rem;color:var(--text-light);margin-top:3px;">Click a date → choose lab &amp; rooms → pick a time slot → set block details.</div>
        </div>
      </div>

      <div class="sched-layout">
        <!-- ── LEFT: calendar ── -->
        <div class="sched-cal-col">
          <div class="cal-card">
            <div class="cal-header">
              <button class="cal-nav" id="sc-prev">&#8249;</button>
              <span class="cal-month-label" id="sc-label"></span>
              <button class="cal-nav" id="sc-next">&#8250;</button>
            </div>
            <div class="cal-weekdays"><div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div></div>
            <div class="cal-grid" id="sc-grid"></div>
            <div class="cal-legend">
              <span class="cal-leg-item"><span class="cal-dot cal-dot--teal"></span>Research</span>
              <span class="cal-leg-item"><span class="cal-dot cal-dot--blue"></span>CSL</span>
              <span class="cal-leg-item"><span class="cal-dot cal-dot--violet"></span>Pharma</span>
              <span class="cal-leg-item"><span class="cal-dot cal-dot--block"></span>Blocked</span>
            </div>
          </div>
          <!-- Existing blocks for selected date -->
          <div class="sched-existing-panel" id="sc-existing" style="display:none;">
            <div class="sched-existing-hdr" id="sc-existing-hdr">Blocks on this date</div>
            <div id="sc-existing-list"></div>
          </div>
        </div>

        <!-- ── RIGHT: step wizard ── -->
        <div class="sched-steps-panel">
          <!-- Step indicator bar -->
          <div class="sched-step-bar">
            <div class="s-step active" id="ss-1"><div class="s-step-num">1</div><div class="s-step-lbl">Date</div></div>
            <div class="s-line" id="sl-1"></div>
            <div class="s-step" id="ss-2"><div class="s-step-num">2</div><div class="s-step-lbl">Lab &amp; Rooms</div></div>
            <div class="s-line" id="sl-2"></div>
            <div class="s-step" id="ss-3"><div class="s-step-num">3</div><div class="s-step-lbl">Time Slot</div></div>
            <div class="s-line" id="sl-3"></div>
            <div class="s-step" id="ss-4"><div class="s-step-num">4</div><div class="s-step-lbl">Block Details</div></div>
          </div>

          <!-- Step 1: prompt to pick date -->
          <div class="s-panel s-panel-empty" id="sp-1">
            <div class="s-empty-icon">📅</div>
            <div class="s-empty-title">Select a Date</div>
            <div class="s-empty-sub">Click any date on the calendar to begin scheduling a block or class session.</div>
          </div>

          <!-- Step 2: lab category + rooms -->
          <div class="s-panel" id="sp-2" style="display:none;">
            <div class="s-panel-head">
              <div><div class="s-panel-title">Choose Lab Category</div><div class="s-panel-sub">Then select the specific rooms to block.</div></div>
              <div class="s-date-chip" id="sp2-date-chip">—</div>
            </div>
            <div class="sched-lab-grid">
              <button class="sched-lab-card" data-type="research" onclick="scSelectLab('research')">
                <div class="sched-lab-card-icon">🧪</div>
                <div class="sched-lab-card-name">Research Labs</div>
                <div class="sched-lab-card-sub">AZ &amp; Avicenna</div>
              </button>
              <button class="sched-lab-card" data-type="csl" onclick="scSelectLab('csl')">
                <div class="sched-lab-card-icon">🏥</div>
                <div class="sched-lab-card-name">CSL Labs</div>
                <div class="sched-lab-card-sub">CSL1 &amp; CSL2</div>
              </button>
              <button class="sched-lab-card" data-type="pharma" onclick="scSelectLab('pharma')">
                <div class="sched-lab-card-icon">⚗️</div>
                <div class="sched-lab-card-name">Pharma Labs</div>
                <div class="sched-lab-card-sub">CL · MDLP · PL1 · PL2</div>
              </button>
            </div>
            <div class="sched-rooms-section" id="sc-rooms-section" style="display:none;">
              <div class="sched-rooms-label">Select Room(s) *</div>
              <div class="sched-rooms-grid" id="sc-rooms-grid"></div>
              <div style="margin-top:14px;display:flex;justify-content:flex-end;">
                <button class="btn-primary s-next-btn" id="sc-step2-next" disabled onclick="scGoStep3()" style="opacity:.5;">Next: Pick Time →</button>
              </div>
            </div>
          </div>

          <!-- Step 3: time grid -->
          <div class="s-panel" id="sp-3" style="display:none;">
            <div class="s-panel-head">
              <div><div class="s-panel-title">Pick a Time Slot</div><div class="s-panel-sub">Choose duration, then click an available circle to select your start time.</div></div>
              <div style="display:flex;align-items:center;gap:8px;">
                <div class="s-date-chip" id="sp3-date-chip">—</div>
                <button class="btn-secondary" style="font-size:.72rem;padding:4px 9px;" onclick="scGoStep(2)">← Back</button>
              </div>
            </div>
            <!-- Duration pills -->
            <div class="sched-dur-bar">
              <span class="sched-dur-lbl">Duration:</span>
              <div id="sc-dur-pills" style="display:flex;gap:6px;flex-wrap:wrap;"></div>
            </div>
            <!-- Legend -->
            <div class="tg-legend">
              <div class="tg-legend-item"><div class="tg-dot tg-dot--avail"></div>Available</div>
              <div class="tg-legend-item"><div class="tg-dot tg-dot--selected"></div>Selected start</div>
              <div class="tg-legend-item"><div class="tg-dot tg-dot--range"></div>Duration range</div>
              <div class="tg-legend-item"><div class="tg-dot tg-dot--booked"></div>Booked</div>
              <div class="tg-legend-item"><div class="tg-dot tg-dot--blocked"></div>Blocked</div>
            </div>
            <!-- Time grid -->
            <div class="tg-wrap"><div class="tg-inner" id="sc-timegrid"></div></div>
            <!-- Selected time bar -->
            <div class="tg-selection-bar" id="sc-sel-bar" style="display:none;">
              <span id="sc-sel-text">—</span>
              <button class="btn-primary" style="font-size:.75rem;padding:5px 13px;" onclick="scGoStep4()">Next: Details →</button>
            </div>
          </div>

          <!-- Step 4: block details -->
          <div class="s-panel" id="sp-4" style="display:none;">
            <div class="s-panel-head">
              <div><div class="s-panel-title">Block Details</div><div class="s-panel-sub">Add a title and configure the block type.</div></div>
              <button class="btn-secondary" style="font-size:.72rem;padding:4px 9px;" onclick="scGoStep(3)">← Back</button>
            </div>
            <div class="step4-summary" id="sp4-summary"><strong>Summary</strong>—</div>
            <div class="block-form-grid">
              <div class="form-group-block">
                <label class="form-label-block">Block Type *</label>
                <select class="form-ctrl" id="sc-blk-cat">
                  <option value="class">📚 Class / Teaching</option>
                  <option value="practical">🔬 Practical Session</option>
                  <option value="maintenance">🔧 Maintenance</option>
                  <option value="reserved">🔒 Reserved / Private</option>
                  <option value="exam">📝 Exam / OSCE</option>
                  <option value="event">🎓 Event</option>
                </select>
              </div>
              <div class="form-group-block">
                <label class="form-label-block">Recurring</label>
                <select class="form-ctrl" id="sc-blk-recur">
                  <option value="none">One-time only</option>
                  <option value="weekly">Every week (same day)</option>
                  <option value="biweekly">Every 2 weeks</option>
                </select>
              </div>
              <div class="form-group-block full">
                <label class="form-label-block">Title / Event Name *</label>
                <input type="text" class="form-ctrl" id="sc-blk-title" placeholder="e.g. Year 3 CSL Suturing Class"/>
              </div>
              <div class="form-group-block full">
                <label class="form-label-block">Instructor / Person In Charge</label>
                <input type="text" class="form-ctrl" id="sc-blk-pic" placeholder="Name or department"/>
              </div>
              <div class="form-group-block full">
                <label class="form-label-block">Notes / Remarks</label>
                <textarea class="form-ctrl form-ctrl-lg" id="sc-blk-notes" placeholder="Setup requirements, equipment, class codes…"></textarea>
              </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px;">
              <button class="btn-secondary" onclick="scGoStep(3)">← Back</button>
              <button class="btn-primary" onclick="scSaveBlock()" style="background:var(--block);">🚫 Save Block</button>
            </div>
          </div>
        </div><!-- /sched-steps-panel -->
      </div><!-- /sched-layout -->

      <!-- All blocks list -->
      <div style="margin-top:28px;">
        <div class="section-header" style="margin-bottom:12px;"><div class="section-title" style="font-size:.92rem;">All Scheduled Blocks</div></div>
        <div class="tab-bar">
          <button class="tab-btn active" onclick="switchTab(this,'stab-all')">All <span class="tab-count" id="btc-all">0</span></button>
          <button class="tab-btn" onclick="switchTab(this,'stab-research')">🧪 Research <span class="tab-count" id="btc-research">0</span></button>
          <button class="tab-btn" onclick="switchTab(this,'stab-csl')">🏥 CSL <span class="tab-count" id="btc-csl">0</span></button>
          <button class="tab-btn" onclick="switchTab(this,'stab-pharma')">⚗️ Pharma <span class="tab-count" id="btc-pharma">0</span></button>
        </div>
        <div id="stab-all" class="tab-panel active"><div class="tbl-wrap"><div id="block-list-all"></div></div></div>
        <div id="stab-research" class="tab-panel"><div class="tbl-wrap"><div id="block-list-research"></div></div></div>
        <div id="stab-csl" class="tab-panel"><div class="tbl-wrap"><div id="block-list-csl"></div></div></div>
        <div id="stab-pharma" class="tab-panel"><div class="tbl-wrap"><div id="block-list-pharma"></div></div></div>
      </div>
    </div><!-- /view-schedule -->

    <!-- SYSTEM REPORT -->
    <div id="view-report" class="view">
      <section class="report-wrap"><div class="report-header"><div><div class="report-title">System Report — UniKLAB RCMP</div><div class="report-meta">Prepared for HOD / Management</div></div><div class="report-meta" id="reportDate">—</div></div><div class="report-grid"><div class="report-card"><h4>Summary Statistics</h4><div class="report-metrics"><div class="report-metric"><span>Total Bookings</span><strong id="reportStatBookings">0</strong></div><div class="report-metric"><span>Approved</span><strong id="reportStatApproved">0</strong></div><div class="report-metric"><span>Pending</span><strong id="reportStatPending">0</strong></div><div class="report-metric"><span>Approval Rate</span><strong id="reportStatApprovalRate">0%</strong></div><div class="report-metric"><span>Active Blocks</span><strong id="reportStatBlocks">0</strong></div><div class="report-metric"><span>Next 14 Days</span><strong id="reportStatUpcoming">0</strong></div></div></div><div class="report-card"><h4>Recent Activities</h4><div class="report-list" id="reportActivities"></div></div><div class="report-card"><h4>System Status</h4><div class="report-status" id="reportStatus"></div></div></div></section>
    </div>

    <!-- MANAGE LABS -->
    <div id="view-labs" class="view">
      <div class="section-header" style="margin-bottom:18px;"><div><div class="section-title">Manage Labs</div><div style="font-size:.78rem;color:var(--text-light);margin-top:3px;">Lab inventory and availability.</div></div><button class="btn-primary" onclick="openLabModal()">＋ Add Lab</button></div>
      <div class="tbl-wrap"><div class="tbl-toolbar"><span class="tbl-toolbar-title">Lab Directory</span><input class="tbl-search" placeholder="Search…" oninput="filterLabTable(this,'labs-table')"/></div><div style="overflow-x:auto;"><table id="labs-table"><thead><tr><th>Lab Name</th><th>Type</th><th>Code</th><th>Location</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div>
    </div>

    <!-- MANAGE STAFF -->
    <div id="view-staff" class="view">
      <div class="section-header" style="margin-bottom:18px;"><div><div class="section-title">Manage Staff</div><div style="font-size:.78rem;color:var(--text-light);margin-top:3px;">Staff accounts for lab access.</div></div><button class="btn-primary" onclick="openStaffModal()">＋ Add Staff</button></div>
      <div class="tbl-wrap"><div class="tbl-toolbar"><span class="tbl-toolbar-title">Staff Directory</span><input class="tbl-search" placeholder="Search…" oninput="filterStaffTable(this,'staff-table')"/></div><div style="overflow-x:auto;"><table id="staff-table"><thead><tr><th>Staff ID</th><th>Full Name</th><th>Role</th><th>Email</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<!-- Booking Detail Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-header"><h3 id="modalTitle">Booking Details</h3><button class="modal-close" onclick="closeModal()">✕</button></div>
    <div class="modal-body" id="modalBody"></div>
    <div class="modal-footer" id="modalFooter"></div>
  </div>
</div>

<!-- Lab Modal -->
<div class="modal-overlay" id="labModalOverlay" onclick="closeLabModal(event)">
  <div class="modal" onclick="event.stopPropagation()" style="max-width:620px;">
    <div class="modal-header"><h3 id="labModalTitle">Add Lab</h3><button class="modal-close" onclick="closeLabModal()">✕</button></div>
    <div class="modal-body"><div class="block-form-grid"><div class="form-group-block"><label class="form-label-block">Lab Name *</label><input type="text" class="form-ctrl" id="lab-name"/></div><div class="form-group-block"><label class="form-label-block">Type *</label><select class="form-ctrl" id="lab-type"><option value="">—</option><option value="research">Research</option><option value="csl">CSL</option><option value="pharma">Pharma</option></select></div><div class="form-group-block"><label class="form-label-block">Code *</label><input type="text" class="form-ctrl" id="lab-code"/></div><div class="form-group-block"><label class="form-label-block">Location *</label><input type="text" class="form-ctrl" id="lab-location"/></div><div class="form-group-block"><label class="form-label-block">Capacity *</label><input type="number" class="form-ctrl" id="lab-capacity" min="1"/></div><div class="form-group-block"><label class="form-label-block">Status *</label><select class="form-ctrl" id="lab-status"><option value="active">Active</option><option value="maintenance">Maintenance</option><option value="inactive">Inactive</option></select></div><div class="form-group-block full"><label class="form-label-block">Notes</label><textarea class="form-ctrl form-ctrl-lg" id="lab-notes"></textarea></div></div></div>
    <div class="modal-footer"><button class="btn-secondary" onclick="closeLabModal()">Cancel</button><button class="btn-primary" id="saveLabBtn" onclick="saveLab()">💾 Save Lab</button></div>
  </div>
</div>

<!-- Staff Modal -->
<div class="modal-overlay" id="staffModalOverlay" onclick="closeStaffModal(event)">
  <div class="modal" onclick="event.stopPropagation()" style="max-width:560px;">
    <div class="modal-header"><h3 id="staffModalTitle">Add Staff</h3><button class="modal-close" onclick="closeStaffModal()">✕</button></div>
    <div class="modal-body"><div class="block-form-grid"><div class="form-group-block"><label class="form-label-block">Staff ID *</label><input type="text" class="form-ctrl" id="staff-id"/></div><div class="form-group-block"><label class="form-label-block">Role *</label><select class="form-ctrl" id="staff-role"><option value="">—</option><option value="research">Research Lab</option><option value="csl">CSL Lab</option><option value="pharma">Pharma Lab</option></select></div><div class="form-group-block"><label class="form-label-block">Full Name *</label><input type="text" class="form-ctrl" id="staff-name"/></div><div class="form-group-block"><label class="form-label-block">Email *</label><input type="email" class="form-ctrl" id="staff-email"/></div><div class="form-group-block full"><label class="form-label-block">Password *</label><input type="password" class="form-ctrl" id="staff-password"/></div></div></div>
    <div class="modal-footer"><button class="btn-secondary" onclick="closeStaffModal()">Cancel</button><button class="btn-primary" id="saveStaffBtn" onclick="saveStaff()">💾 Save Staff</button></div>
  </div>
</div>
<script>
// ================================================================
// CONSTANTS & DATA
// ================================================================
const CURRENT_ADMIN='Dr. Sarah Admin';
const MONTHS=['January','February','March','April','May','June','July','August','September','October','November','December'];
const CSL_ROOMS=['CSL1 – Physiko Room','CSL1 – Mock Ward','CSL1 – Simulation Room','CSL2 – Room 1','CSL2 – Room 2','CSL2 – Room 3','CSL2 – Room 4','CSL2 – Room 5','CSL2 – Room 6','CSL2 – Room 7','CSL2 – Room 8','CSL2 – Room 9','CSL2 – Room 10','CSL2 – Room 11','CSL2 – Room 12','CSL2 – Discussion Room'];
const RESEARCH_ROOMS=['AZ – Plant Extraction Room (A2052)','AZ – Molecular Room (A2051)','AZ – Media Preparation Room (A2055)','AZ – Assay Room (A2054)','AZ – Microbiology Room (A2012-A2013)','AZ – Cell Culture Room 1','AZ – Cell Culture Room 2','AZ – Cell Culture Room 3','AZ – Instrumentation Room','AV – MDL 3 (2A-31)','AV – Lab Level 2'];
const PHARMA_ROOMS=['Chemistry Lab (CL)','Multidisciplinary Pharma Lab (MDLP)','Pharmaceutical Lab 1 (PL1)','Pharmaceutical Lab 2 (PL2)'];
const ROOMS_BY_TYPE={research:RESEARCH_ROOMS,csl:CSL_ROOMS,pharma:PHARMA_ROOMS};
const BLOCK_ICONS={class:'📚',practical:'🔬',maintenance:'🔧',reserved:'🔒',exam:'📝',event:'🎓'};
const BLOCK_LABELS={class:'Class',practical:'Practical',maintenance:'Maintenance',reserved:'Reserved',exam:'Exam/OSCE',event:'Event'};
const TYPE_LABELS={research:'Research',csl:'CSL',pharma:'Pharma'};
const DURATIONS=[{m:60,l:'1 hr'},{m:90,l:'1.5 hrs'},{m:120,l:'2 hrs'},{m:150,l:'2.5 hrs'},{m:180,l:'3 hrs'},{m:210,l:'3.5 hrs'},{m:240,l:'4 hrs'},{m:300,l:'5 hrs'},{m:360,l:'6 hrs'},{m:480,l:'8 hrs (full day)'}];

function pad2(n){return String(n).padStart(2,'0')}
function dStr(y,m,d){return `${y}-${pad2(m+1)}-${pad2(d)}`}
function toMin(t){if(!t)return 0;const[h,m]=t.split(':').map(Number);return h*60+m}
function fromMin(m){return `${pad2(Math.floor(m/60))}:${pad2(m%60)}`}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}

const now=new Date(),CY=now.getFullYear(),CM=now.getMonth();

let ALL_BOOKINGS=[
  {ref:'LB-2025-001',name:'Ahmad Zulkifli',id:'S12345',email:'ahmad@unikl.edu.my',type:'research',type_label:'Research Labs',color:'teal',date:dStr(CY,CM,5),start:'08:00',end:'12:00',rooms:'AZ – Molecular Room (A2051)',purpose:'Biochemistry experiments for final year project',status:'approved',admin_remark:'Standard booking approved.',audit:[{action:'Created',by:'Ahmad Zulkifli',at:'2025-04-20 09:15',detail:'Booking submitted',type:'created'},{action:'Approved',by:'Dr. Sarah Admin',at:'2025-04-20 11:00',detail:'Standard booking approved.',type:'approved'}]},
  {ref:'LB-2025-002',name:'Nurul Ain binti Hassan',id:'S12346',email:'nurul@unikl.edu.my',type:'csl',type_label:'CSL Labs',color:'blue',date:dStr(CY,CM,5),start:'09:00',end:'11:00',rooms:'CSL2 – Room 3',purpose:'Clinical skills practice – suturing',status:'pending',admin_remark:'',audit:[{action:'Created',by:'Nurul Ain binti Hassan',at:'2025-04-21 08:30',detail:'Booking submitted',type:'created'}]},
  {ref:'LB-2025-003',name:'Raj Kumar s/o Murugan',id:'S12347',email:'raj@unikl.edu.my',type:'pharma',type_label:'Pharma Labs',color:'violet',date:dStr(CY,CM,8),start:'14:00',end:'17:00',rooms:'Pharmaceutical Lab 2 (PL2)',purpose:'Drug formulation practical session',status:'approved',admin_remark:'Lab P2 confirmed available.',audit:[{action:'Created',by:'Raj Kumar',at:'2025-04-22 10:00',detail:'Booking submitted',type:'created'},{action:'Approved',by:'Dr. Sarah Admin',at:'2025-04-22 14:30',detail:'Lab P2 confirmed.',type:'approved'}]},
  {ref:'LB-2025-004',name:'Lim Wei Ling',id:'S12348',email:'lim@unikl.edu.my',type:'csl',type_label:'CSL Labs',color:'blue',date:dStr(CY,CM,10),start:'10:00',end:'13:00',rooms:'CSL2 – Room 3',purpose:'Obstetrics simulation lab session',status:'pending',admin_remark:'',audit:[{action:'Created',by:'Lim Wei Ling',at:'2025-04-23 07:45',detail:'Booking submitted',type:'created'}]},
  {ref:'LB-2025-005',name:'Farah binti Othman',id:'S12349',email:'farah@unikl.edu.my',type:'research',type_label:'Research Labs',color:'teal',date:dStr(CY,CM,12),start:'08:00',end:'10:00',rooms:'AZ – Microbiology Room (A2012-A2013)',purpose:'Microbiology culture experiments',status:'rejected',admin_remark:'Lab under maintenance.',audit:[{action:'Created',by:'Farah Othman',at:'2025-04-23 09:00',detail:'Booking submitted',type:'created'},{action:'Rejected',by:'Dr. Sarah Admin',at:'2025-04-23 16:00',detail:'Lab under maintenance.',type:'rejected'}]},
  {ref:'LB-2025-006',name:'Mohd Haziq bin Ismail',id:'S12350',email:'haziq@unikl.edu.my',type:'pharma',type_label:'Pharma Labs',color:'violet',date:dStr(CY,CM,14),start:'13:00',end:'16:00',rooms:'Chemistry Lab (CL)',purpose:'Tablet compounding lab exercise',status:'pending',admin_remark:'',audit:[{action:'Created',by:'Mohd Haziq',at:'2025-04-24 11:20',detail:'Booking submitted',type:'created'}]},
  {ref:'LB-2025-007',name:'Chong Mei Fang',id:'S12351',email:'chong@unikl.edu.my',type:'csl',type_label:'CSL Labs',color:'blue',date:dStr(CY,CM,15),start:'09:00',end:'11:00',rooms:'CSL1 – Physiko Room',purpose:'Paediatric physical examination training',status:'approved',admin_remark:'Mannequins prepared.',audit:[{action:'Created',by:'Chong Mei Fang',at:'2025-04-24 08:00',detail:'Booking submitted',type:'created'},{action:'Approved',by:'Dr. Sarah Admin',at:'2025-04-24 10:30',detail:'Mannequins prepared.',type:'approved'}]},
  {ref:'LB-2025-008',name:'Siti Rahayu binti Ramli',id:'S12352',email:'siti@unikl.edu.my',type:'research',type_label:'Research Labs',color:'teal',date:dStr(CY,CM,18),start:'14:00',end:'17:00',rooms:'AZ – Assay Room (A2054)',purpose:'Histology slide preparation',status:'approved',admin_remark:'Reagents prepared.',audit:[{action:'Created',by:'Siti Rahayu',at:'2025-04-25 09:10',detail:'Booking submitted',type:'created'},{action:'Approved',by:'Dr. Sarah Admin',at:'2025-04-25 11:00',detail:'Reagents prepared.',type:'approved'}]},
  {ref:'LB-2025-009',name:'Hafiz bin Abdullah',id:'S12353',email:'hafiz@unikl.edu.my',type:'pharma',type_label:'Pharma Labs',color:'violet',date:dStr(CY,CM,20),start:'08:00',end:'12:00',rooms:'Multidisciplinary Pharma Lab (MDLP)',purpose:'Stability testing',status:'pending',admin_remark:'',audit:[{action:'Created',by:'Hafiz Abdullah',at:'2025-04-26 08:00',detail:'Booking submitted',type:'created'}]},
  {ref:'LB-2025-010',name:'Priya a/p Krishnamurthy',id:'S12354',email:'priya@unikl.edu.my',type:'csl',type_label:'CSL Labs',color:'blue',date:dStr(CY,CM,22),start:'10:00',end:'12:00',rooms:'CSL2 – Room 5',purpose:'Cardiac auscultation training',status:'approved',admin_remark:'Simulator ready.',audit:[{action:'Created',by:'Priya Krishnamurthy',at:'2025-04-26 10:00',detail:'Booking submitted',type:'created'},{action:'Approved',by:'Dr. Sarah Admin',at:'2025-04-27 09:00',detail:'Simulator ready.',type:'approved'}]},
];
let BOOKING_MAP={};ALL_BOOKINGS.forEach(b=>BOOKING_MAP[b.ref]=b);

let ALL_BLOCKS=[
  {id:'BLK-001',type:'csl',category:'class',title:'Year 3 – CSL Suturing Class',pic:'Dr. Ahmad Hafizi',date:dStr(CY,CM,5),start:'08:00',end:'09:00',rooms:['CSL2 – Room 11'],recurring:'weekly',notes:'Weekly suturing practical.'},
  {id:'BLK-002',type:'csl',category:'class',title:'Year 2 – Mock Ward Skills',pic:'Dr. Siti Norzahira',date:dStr(CY,CM,5),start:'09:30',end:'12:00',rooms:['CSL1 – Mock Ward'],recurring:'weekly',notes:''},
  {id:'BLK-003',type:'research',category:'maintenance',title:'Biosafety Cabinet Annual Service',pic:'Facilities Dept.',date:dStr(CY,CM,8),start:'08:00',end:'17:00',rooms:['AZ – Microbiology Room (A2012-A2013)'],recurring:'none',notes:'Annual certification by external vendor.'},
  {id:'BLK-004',type:'pharma',category:'class',title:'Year 4 – Pharmaceutical Analysis',pic:'Dr. Nurul Hidayah',date:dStr(CY,CM,10),start:'14:00',end:'17:00',rooms:['Chemistry Lab (CL)','Multidisciplinary Pharma Lab (MDLP)'],recurring:'weekly',notes:''},
  {id:'BLK-005',type:'csl',category:'exam',title:'OSCE Year 4 – Station 3–5',pic:'Dr. Lim Wei Lin',date:dStr(CY,CM,15),start:'08:00',end:'16:00',rooms:['CSL2 – Room 3','CSL2 – Room 4','CSL2 – Room 5'],recurring:'none',notes:'End of block OSCE.'},
];
let blockIdCounter=6;
let LABS=[
  {id:'LAB-001',name:'AZ – Molecular Room (A2051)',type:'research',code:'AZ-2051',location:'Block A · Level 2',capacity:24,status:'active',notes:'PCR workstation.'},
  {id:'LAB-002',name:'CSL2 – Room 3',type:'csl',code:'CSL2-R3',location:'CSL Building · Level 2',capacity:18,status:'active',notes:''},
  {id:'LAB-003',name:'Chemistry Lab (CL)',type:'pharma',code:'PH-CL',location:'Pharma Block · Level 1',capacity:28,status:'maintenance',notes:'Fume hood service.'},
];
let labIdCounter=4,activeLabEditId=null;
let STAFF=[
  {id:'STF-001',staff_id:'S10001',role:'research',name:'Dr. Salmah Karim',email:'salmah.karim@unikl.edu.my',password:'demo123'},
  {id:'STF-002',staff_id:'S10002',role:'csl',name:'Mr. Amirul Hassan',email:'amirul.hassan@unikl.edu.my',password:'demo123'},
  {id:'STF-003',staff_id:'S10003',role:'pharma',name:'Dr. Aisha Rahman',email:'aisha.rahman@unikl.edu.my',password:'demo123'},
];
let staffIdCounter=4,activeStaffEditId=null;

// ================================================================
// SCHEDULE & BLOCK STATE + LOGIC
// ================================================================
let SC={step:1,date:'',labType:'',rooms:[],durMins:60,startTime:'',endTime:''};
let scYear,scMonth;

function scGoStep(n){
  SC.step=n;
  for(let i=1;i<=4;i++){
    const el=document.getElementById('ss-'+i);
    if(!el) continue;
    el.classList.remove('active','done');
    if(i<n) el.classList.add('done');
    else if(i===n) el.classList.add('active');
    const line=document.getElementById('sl-'+i);
    if(line) line.classList.toggle('done',i<n);
  }
  for(let i=1;i<=4;i++){
    const p=document.getElementById('sp-'+i);
    if(p) p.style.display=(i===n)?'flex':'none';
  }
  // step 1 is the empty state
  if(n===1){
    const p=document.getElementById('sp-1');
    if(p){p.style.display='flex';p.classList.add('s-panel-empty');}
  }
}

function scSelectDate(dateStr){
  // remove old highlight
  document.querySelectorAll('#sc-grid .selected-day').forEach(c=>c.classList.remove('selected-day'));
  // highlight new
  document.querySelectorAll('#sc-grid .cal-day[data-date="'+dateStr+'"]').forEach(c=>c.classList.add('selected-day'));
  SC.date=dateStr;SC.labType='';SC.rooms=[];SC.startTime='';SC.endTime='';
  // format chip
  const d=new Date(dateStr+'T00:00:00');
  const fmt=d.toLocaleDateString('en-MY',{weekday:'short',day:'numeric',month:'short',year:'numeric'});
  document.getElementById('sp2-date-chip').textContent=fmt;
  document.getElementById('sp3-date-chip').textContent=fmt;
  // reset lab cards
  document.querySelectorAll('.sched-lab-card').forEach(c=>c.classList.remove('selected'));
  document.getElementById('sc-rooms-section').style.display='none';
  const nb=document.getElementById('sc-step2-next');
  nb.disabled=true;nb.style.opacity='.5';
  // show existing blocks for this date
  scRenderExistingBlocks(dateStr);
  scGoStep(2);
}

function scRenderExistingBlocks(dateStr){
  const blocks=getBlocksByDate()[dateStr]||[];
  const panel=document.getElementById('sc-existing');
  const list=document.getElementById('sc-existing-list');
  const hdr=document.getElementById('sc-existing-hdr');
  if(!blocks.length){panel.style.display='none';return;}
  const d=new Date(dateStr+'T00:00:00');
  const fmt=d.toLocaleDateString('en-MY',{day:'numeric',month:'short'});
  hdr.textContent=blocks.length+' block'+(blocks.length>1?'s':'')+' on '+fmt;
  list.innerHTML=blocks.map(b=>`
    <div class="block-entry" style="padding:8px 12px;">
      <div class="block-entry-icon" style="width:26px;height:26px;font-size:.75rem;">${BLOCK_ICONS[b.category]||'🚫'}</div>
      <div class="block-entry-body">
        <div class="block-entry-title" style="font-size:.77rem;">${esc(b.title)}</div>
        <div class="block-entry-meta">${esc(b.start)}–${esc(b.end)} · ${(b.rooms||[]).join(', ')}</div>
      </div>
      <button class="btn-xs btn-delete" style="font-size:.64rem;" onclick="deleteBlock('${b.id}')">✕</button>
    </div>`).join('');
  panel.style.display='';
}

function scSelectLab(type){
  SC.labType=type;SC.rooms=[];
  document.querySelectorAll('.sched-lab-card').forEach(c=>c.classList.toggle('selected',c.dataset.type===type));
  // render rooms
  const rooms=ROOMS_BY_TYPE[type]||[];
  const grid=document.getElementById('sc-rooms-grid');
  grid.innerHTML=rooms.map(r=>`
    <label class="sched-room-item" id="ritem-${esc(btoa(r).replace(/[^a-zA-Z0-9]/g,''))}">
      <input type="checkbox" value="${esc(r)}" onchange="scToggleRoom(this)"/>
      ${esc(r)}
    </label>`).join('');
  document.getElementById('sc-rooms-section').style.display='';
  const nb=document.getElementById('sc-step2-next');
  nb.disabled=true;nb.style.opacity='.5';
}

function scToggleRoom(cb){
  if(cb.checked){if(!SC.rooms.includes(cb.value))SC.rooms.push(cb.value);}
  else SC.rooms=SC.rooms.filter(r=>r!==cb.value);
  cb.closest('.sched-room-item').classList.toggle('checked',cb.checked);
  const nb=document.getElementById('sc-step2-next');
  nb.disabled=SC.rooms.length===0;
  nb.style.opacity=SC.rooms.length?'1':'.5';
}

function scGoStep3(){
  if(!SC.rooms.length){showToast('Select at least one room.','');return;}
  // build duration pills
  const pills=document.getElementById('sc-dur-pills');
  pills.innerHTML=DURATIONS.map(d=>`<button class="dur-btn${d.m===SC.durMins?' active':''}" onclick="scSetDur(${d.m},this)">${d.l}</button>`).join('');
  scGoStep(3);
  scRenderTimeGrid();
}

function scSetDur(mins,btn){
  SC.durMins=mins;SC.startTime='';SC.endTime='';
  document.querySelectorAll('.dur-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  scRenderTimeGrid();
  document.getElementById('sc-sel-bar').style.display='none';
}

function scRenderTimeGrid(){
  const grid=document.getElementById('sc-timegrid');
  if(!grid)return;
  // time slots 07:00–21:30 in 30-min increments
  const slots=[];
  for(let h=7;h<=21;h++)for(let mi of[0,30])slots.push(h*60+mi);

  // compute occupied ranges for selected rooms on selected date
  const occ=[];
  ALL_BOOKINGS.forEach(bk=>{
    if(bk.date!==SC.date||bk.status==='rejected'||bk.status==='cancelled')return;
    const bkRooms=[bk.rooms].flat();
    if(!SC.rooms.some(r=>bkRooms.includes(r)))return;
    occ.push({s:toMin(bk.start),e:toMin(bk.end),kind:'booked'});
  });
  const byBlock=getBlocksByDate();
  (byBlock[SC.date]||[]).forEach(bl=>{
    if(!SC.rooms.some(r=>(bl.rooms||[]).includes(r)))return;
    occ.push({s:toMin(bl.start),e:toMin(bl.end),kind:'blocked'});
  });

  function slotKind(sm){
    for(const o of occ)if(sm>=o.s&&sm<o.e)return o.kind;
    return 'available';
  }
  function canFit(sm){
    for(let t=sm;t<sm+SC.durMins;t+=30)if(slotKind(t)!=='available')return false;
    return true;
  }
  function inRange(sm){
    if(!SC.startTime)return false;
    const s=toMin(SC.startTime),e=toMin(SC.endTime);
    return sm>=s&&sm<e;
  }

  // Build header
  let html='<div class="tg-header-row"><div class="tg-corner">Room / Lab</div>';
  slots.forEach(sm=>{
    html+=`<div class="tg-time-hdr">${pad2(Math.floor(sm/60))}<br>${pad2(sm%60)}</div>`;
  });
  html+='</div>';

  // Build rows per room
  SC.rooms.forEach(room=>{
    html+=`<div class="tg-row"><div class="tg-room-lbl">${esc(room)}</div>`;
    slots.forEach(sm=>{
      const kind=slotKind(sm);
      const isStart=SC.startTime&&toMin(SC.startTime)===sm;
      const isRange=inRange(sm)&&!isStart;
      const clickable=kind==='available'&&canFit(sm);
      let cls='slot-btn';
      let txt='';
      let title='';
      if(isStart){cls+=' slot-selected';txt='▶';title='Selected start';}
      else if(isRange){cls+=' slot-range';title='In duration range';}
      else if(kind==='blocked'){cls+=' slot-blocked';txt='🚫';title='Blocked';}
      else if(kind==='booked'){cls+=' slot-booked';txt='✕';title='Booked';}
      else if(!clickable){title='Cannot fit duration here';}
      else{title=`Select ${fromMin(sm)}–${fromMin(sm+SC.durMins)}`;}
      const click=clickable?`onclick="scSelectSlot(${sm})"`:kind==='booked'||kind==='blocked'?'':'';
      html+=`<div class="tg-cell"><button class="${cls}" ${click} title="${title}" ${!clickable&&kind==='available'?'style="opacity:.35;cursor:not-allowed"':''}>${txt}</button></div>`;
    });
    html+='</div>';
  });

  grid.innerHTML=html;
}

function scSelectSlot(sm){
  SC.startTime=fromMin(sm);
  SC.endTime=fromMin(sm+SC.durMins);
  scRenderTimeGrid();
  const bar=document.getElementById('sc-sel-bar');
  const txt=document.getElementById('sc-sel-text');
  const durLabel=DURATIONS.find(d=>d.m===SC.durMins)?.l||SC.durMins+'min';
  txt.textContent=`⏰ ${SC.startTime} – ${SC.endTime}  ·  ${durLabel}`;
  bar.style.display='flex';
}

function scGoStep4(){
  if(!SC.startTime){showToast('Please select a time slot.','');return;}
  const d=new Date(SC.date+'T00:00:00');
  const fmt=d.toLocaleDateString('en-MY',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
  const durLabel=DURATIONS.find(d=>d.m===SC.durMins)?.l||SC.durMins+'min';
  document.getElementById('sp4-summary').innerHTML=
    `<strong>📋 Block Summary</strong>`+
    `📅 ${fmt}<br>`+
    `⏰ ${SC.startTime} – ${SC.endTime} (${durLabel})<br>`+
    `🏷️ ${TYPE_LABELS[SC.labType]} · ${SC.rooms.join(', ')}`;
  // reset form fields
  document.getElementById('sc-blk-cat').value='class';
  document.getElementById('sc-blk-recur').value='none';
  document.getElementById('sc-blk-title').value='';
  document.getElementById('sc-blk-pic').value='';
  document.getElementById('sc-blk-notes').value='';
  scGoStep(4);
}

function scSaveBlock(){
  const title=document.getElementById('sc-blk-title').value.trim();
  if(!title){showToast('Please enter a title/event name.','');document.getElementById('sc-blk-title').focus();return;}
  const blk={
    id:`BLK-${String(blockIdCounter++).padStart(3,'0')}`,
    type:SC.labType,
    category:document.getElementById('sc-blk-cat').value,
    title,
    pic:document.getElementById('sc-blk-pic').value.trim(),
    date:SC.date,
    start:SC.startTime,
    end:SC.endTime,
    rooms:SC.rooms.slice(),
    recurring:document.getElementById('sc-blk-recur').value,
    notes:document.getElementById('sc-blk-notes').value.trim()
  };
  ALL_BLOCKS.push(blk);
  renderAllTables();renderBlockLists();renderCals();renderScCal();
  showToast(`Block "${title}" saved successfully.`,'toast-warn');
  // reset flow
  SC={step:1,date:'',labType:'',rooms:[],durMins:60,startTime:'',endTime:''};
  document.querySelectorAll('#sc-grid .selected-day').forEach(c=>c.classList.remove('selected-day'));
  document.getElementById('sc-existing').style.display='none';
  document.getElementById('sc-sel-bar').style.display='none';
  scGoStep(1);
  renderBlockLists();
}

// ================================================================
// SCHEDULE CALENDAR
// ================================================================
function renderScCal(){
  if(!document.getElementById('sc-grid'))return;
  document.getElementById('sc-label').textContent=MONTHS[scMonth]+' '+scYear;
  const grid=document.getElementById('sc-grid');grid.innerHTML='';
  const byDate=getAllBookingsByDate(),byBlock=getBlocksByDate();
  const first=new Date(scYear,scMonth,1).getDay();
  const dInM=new Date(scYear,scMonth+1,0).getDate();
  const dInP=new Date(scYear,scMonth,0).getDate();
  const today=new Date();
  function mkCell(d,cy,cm,other){
    const ds=`${cy}-${pad2(cm+1)}-${pad2(d)}`;
    const isToday=d===today.getDate()&&cm===today.getMonth()&&cy===today.getFullYear();
    const isSel=ds===SC.date;
    const cell=document.createElement('div');
    cell.className='cal-day'+(other?' other-month':'')+(isToday?' today':'')+(isSel?' selected-day':'');
    cell.dataset.date=ds;
    const num=document.createElement('div');num.className='cal-day-num';num.textContent=d;cell.appendChild(num);
    const bks=byDate[ds]||[],blks=byBlock[ds]||[];
    if(bks.length||blks.length){
      const dots=document.createElement('div');dots.className='cal-day-dots';
      bks.slice(0,4).forEach(b=>{const dot=document.createElement('span');dot.className=`cal-day-dot cal-day-dot--${b.color}${b.status==='pending'?' cal-day-dot--pending':''}`;dots.appendChild(dot);});
      blks.slice(0,2).forEach(()=>{const dot=document.createElement('span');dot.className='cal-day-dot cal-day-dot--block';dots.appendChild(dot);});
      cell.appendChild(dots);
    }
    if(!other)cell.addEventListener('click',()=>scSelectDate(ds));
    return cell;
  }
  for(let i=first-1;i>=0;i--)grid.appendChild(mkCell(dInP-i,scMonth===0?scYear-1:scYear,scMonth===0?11:scMonth-1,true));
  for(let d=1;d<=dInM;d++)grid.appendChild(mkCell(d,scYear,scMonth,false));
  const total=first+dInM,rem=total%7===0?0:7-(total%7);
  for(let d=1;d<=rem;d++)grid.appendChild(mkCell(d,scMonth===11?scYear+1:scYear,scMonth===11?0:scMonth+1,true));
}

document.addEventListener('DOMContentLoaded',()=>{
  const n=new Date();scYear=n.getFullYear();scMonth=n.getMonth();
  renderScCal();
});
document.getElementById('sc-prev').addEventListener('click',()=>{scMonth--;if(scMonth<0){scMonth=11;scYear--;}renderScCal();});
document.getElementById('sc-next').addEventListener('click',()=>{scMonth++;if(scMonth>11){scMonth=0;scYear++;}renderScCal();});

// ================================================================
// VIEWS
// ================================================================
function showView(id,btn){
  document.querySelectorAll('.view').forEach(v=>v.classList.remove('active'));
  document.getElementById('view-'+id).classList.add('active');
  document.querySelectorAll('.sb-link').forEach(l=>l.classList.remove('active'));
  if(btn)btn.classList.add('active');
  const titles={dashboard:'Dashboard',calendar:'Calendar View',all:'All Bookings',research:'Research Labs',csl:'CSL Labs',pharma:'Pharma Labs',schedule:'Schedule & Block',report:'System Report',labs:'Manage Labs',staff:'Manage Staff'};
  document.getElementById('topbarTitle').textContent=titles[id]||'';
  if(id==='all')renderAllTabs();
  if(id==='research')renderPendingTable('tbl-research','research');
  if(id==='csl')renderPendingTable('tbl-csl','csl');
  if(id==='pharma')renderPendingTable('tbl-pharma','pharma');
  if(id==='schedule'){renderBlockLists();renderScCal();}
  if(id==='labs')renderLabs();
  if(id==='staff')renderStaffs();
}
function switchTab(btn,tabId){
  btn.closest('.tab-bar').querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  btn.closest('.view').querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.getElementById(tabId)?.classList.add('active');
}

// ================================================================
// TABLE RENDERING
// ================================================================
function typeChipHtml(t){const m={research:'type-chip--research',csl:'type-chip--csl',pharma:'type-chip--pharma'};const l={research:'Research',csl:'CSL',pharma:'Pharma'};return`<span class="type-chip ${m[t]||''}">${l[t]||t}</span>`}
function statusBadgeHtml(s){return`<span class="status-badge status-${s}">${s.charAt(0).toUpperCase()+s.slice(1)}</span>`}
function actionBtns(b){let s=`<button class="btn-xs btn-view" onclick="openModal('${b.ref}')">View</button>`;if(b.status==='pending'){s+=`<button class="btn-xs btn-approve" onclick="quickDecide('${b.ref}','approved')">✓</button><button class="btn-xs btn-reject" onclick="quickDecide('${b.ref}','rejected')">✕</button>`;}return s}

function renderRows(rows,tblId,showType=false){
  const tbody=document.querySelector('#'+tblId+' tbody');if(!tbody)return;
  if(!rows.length){tbody.innerHTML=`<tr><td colspan="${showType?8:7}" style="text-align:center;padding:32px;color:var(--text-light);">No bookings found.</td></tr>`;return;}
  tbody.innerHTML=rows.map(b=>`<tr data-ref="${b.ref}"><td><div class="ref-code">${esc(b.ref)}</div></td><td><strong style="font-size:.82rem;">${esc(b.name)}</strong><br><span style="font-size:.7rem;color:var(--text-light);">${esc(b.id)}</span></td>${showType?`<td>${typeChipHtml(b.type)}</td>`:''}<td style="font-size:.8rem;">${esc(b.date)}</td><td style="font-size:.78rem;color:var(--text-mid);white-space:nowrap;">${esc(b.start)}–${esc(b.end)}</td><td style="max-width:170px;font-size:.76rem;color:var(--text-mid);">${esc(b.rooms)}</td><td>${statusBadgeHtml(b.status)}</td><td><div class="action-btns">${actionBtns(b)}</div></td></tr>`).join('');
}
function renderPendingTable(tblId,type){renderRows(ALL_BOOKINGS.filter(b=>b.type===type&&b.status==='pending'),tblId,false)}
function renderAllTabs(){
  const res=ALL_BOOKINGS.filter(b=>b.type==='research'),csl=ALL_BOOKINGS.filter(b=>b.type==='csl'),pha=ALL_BOOKINGS.filter(b=>b.type==='pharma'),pend=ALL_BOOKINGS.filter(b=>b.status==='pending');
  renderRows(ALL_BOOKINGS,'tbl-a-all',true);renderRows(res,'tbl-a-research',false);renderRows(csl,'tbl-a-csl',false);renderRows(pha,'tbl-a-pharma',false);renderRows(pend,'tbl-a-pending',true);
  document.getElementById('tc-all').textContent=ALL_BOOKINGS.length;document.getElementById('tc-research').textContent=res.length;document.getElementById('tc-csl').textContent=csl.length;document.getElementById('tc-pharma').textContent=pha.length;document.getElementById('tc-pending').textContent=pend.length;
}
function renderAllTables(){
  const res=ALL_BOOKINGS.filter(b=>b.type==='research'),csl=ALL_BOOKINGS.filter(b=>b.type==='csl'),pha=ALL_BOOKINGS.filter(b=>b.type==='pharma'),appr=ALL_BOOKINGS.filter(b=>b.status==='approved');
  document.getElementById('stat-all').textContent=ALL_BOOKINGS.length;document.getElementById('stat-approved').textContent=appr.length;document.getElementById('stat-research').textContent=res.length;document.getElementById('stat-csl').textContent=csl.length;document.getElementById('stat-pharma').textContent=pha.length;document.getElementById('stat-blocks').textContent=ALL_BLOCKS.length;
  document.getElementById('sb-research-count').textContent=ALL_BOOKINGS.filter(b=>b.type==='research'&&b.status==='pending').length;
  document.getElementById('sb-csl-count').textContent=ALL_BOOKINGS.filter(b=>b.type==='csl'&&b.status==='pending').length;
  document.getElementById('sb-pharma-count').textContent=ALL_BOOKINGS.filter(b=>b.type==='pharma'&&b.status==='pending').length;
  renderUpcomingBlocks();renderReport();
}

// ================================================================
// BLOCK LISTS
// ================================================================
function blockEntryHtml(blk){
  const recur=blk.recurring!=='none'?`<span class="recurring-badge">🔄 ${blk.recurring==='weekly'?'Weekly':'Bi-weekly'}</span>`:'';
  return`<div class="block-entry">
    <div class="block-entry-icon">${BLOCK_ICONS[blk.category]||'🚫'}</div>
    <div class="block-entry-body">
      <div class="block-entry-title">${esc(blk.title)}${recur}</div>
      <div class="block-entry-meta"><span class="type-chip type-chip--block">${TYPE_LABELS[blk.type]}</span> ${BLOCK_LABELS[blk.category]||blk.category} · ${esc(blk.date)} · ${esc(blk.start)}–${esc(blk.end)}${blk.pic?' · '+esc(blk.pic):''}</div>
      <div class="block-entry-rooms">${(blk.rooms||[]).join(', ')}</div>
      ${blk.notes?`<div style="font-size:.71rem;color:var(--text-light);margin-top:2px;font-style:italic;">${esc(blk.notes)}</div>`:''}
    </div>
    <div class="block-entry-actions"><button class="btn-xs btn-delete" onclick="deleteBlock('${blk.id}')">✕ Remove</button></div>
  </div>`;
}
function renderBlockList(id,blocks){
  const el=document.getElementById(id);if(!el)return;
  el.innerHTML=blocks.length?blocks.map(b=>blockEntryHtml(b)).join(''):'<div class="empty-state"><strong>No blocks scheduled</strong>All slots open for booking</div>';
}
function renderBlockLists(){
  const res=ALL_BLOCKS.filter(b=>b.type==='research'),csl=ALL_BLOCKS.filter(b=>b.type==='csl'),pha=ALL_BLOCKS.filter(b=>b.type==='pharma');
  renderBlockList('block-list-all',ALL_BLOCKS);renderBlockList('block-list-research',res);renderBlockList('block-list-csl',csl);renderBlockList('block-list-pharma',pha);
  document.getElementById('btc-all').textContent=ALL_BLOCKS.length;document.getElementById('btc-research').textContent=res.length;document.getElementById('btc-csl').textContent=csl.length;document.getElementById('btc-pharma').textContent=pha.length;
}
function renderUpcomingBlocks(){
  const el=document.getElementById('upcoming-blocks-list');if(!el)return;
  const todayStr=dStr(CY,CM,now.getDate());
  const up=ALL_BLOCKS.filter(b=>b.date>=todayStr||b.recurring!=='none').slice(0,5);
  el.innerHTML=up.length?up.map(b=>blockEntryHtml(b)).join(''):'<div class="empty-state">No upcoming blocks</div>';
}
function deleteBlock(id){
  if(!confirm('Remove this block?'))return;
  ALL_BLOCKS=ALL_BLOCKS.filter(b=>b.id!==id);
  renderAllTables();renderBlockLists();renderCals();renderScCal();
  if(SC.date)scRenderExistingBlocks(SC.date);
  showToast('Block removed.','toast-warn');
}
function getRecurDates(base,rec,max=4){
  const dates=[base];if(rec==='none')return dates;
  const dt=new Date(base+'T00:00:00'),step=rec==='weekly'?7:14;
  for(let i=1;i<max;i++){const nx=new Date(dt);nx.setDate(dt.getDate()+step*i);dates.push(`${nx.getFullYear()}-${pad2(nx.getMonth()+1)}-${pad2(nx.getDate())}`);}
  return dates;
}
function getBlocksByDate(){
  const map={};
  ALL_BLOCKS.forEach(b=>getRecurDates(b.date,b.recurring,4).forEach(ds=>{if(!map[ds])map[ds]=[];map[ds].push({...b,occDate:ds});}));
  return map;
}

// ================================================================
// CALENDARS
// ================================================================
let dcYear,dcMonth,fcYear,fcMonth,CAL_FILTER='all';

function getAllBookingsByDate(){
  const map={};
  ALL_BOOKINGS.forEach(b=>{
    if(CAL_FILTER!=='all'&&b.type!==CAL_FILTER)return;
    if(!map[b.date])map[b.date]=[];map[b.date].push(b);
  });
  return map;
}

function initCals(){
  const n=new Date();dcYear=fcYear=n.getFullYear();dcMonth=fcMonth=n.getMonth();
  renderCals();
  const ts=`${n.getFullYear()}-${pad2(n.getMonth()+1)}-${pad2(n.getDate())}`;
  showDcDetail(ts,getAllBookingsByDate()[ts]||[],getBlocksByDate()[ts]||[]);
}
function renderCals(){renderCal('dc-grid','dc-label',dcYear,dcMonth,showDcDetail);renderCal('fc-grid','fc-label',fcYear,fcMonth,showFcDetail);}
function renderCal(gridId,labelId,cy,cm,fn){
  document.getElementById(labelId).textContent=MONTHS[cm]+' '+cy;
  const grid=document.getElementById(gridId);grid.innerHTML='';
  const byDate=getAllBookingsByDate(),byBlock=getBlocksByDate();
  const first=new Date(cy,cm,1).getDay(),dInM=new Date(cy,cm+1,0).getDate(),dInP=new Date(cy,cm,0).getDate();
  const today=new Date();
  function mkC(d,cy2,cm2,other,isTod){
    const ds=`${cy2}-${pad2(cm2+1)}-${pad2(d)}`;
    const cell=document.createElement('div');
    cell.className='cal-day'+(other?' other-month':'')+(isTod?' today':'');
    const num=document.createElement('div');num.className='cal-day-num';num.textContent=d;cell.appendChild(num);
    const bks=byDate[ds]||[],blks=byBlock[ds]||[];
    if(bks.length||blks.length){
      const dots=document.createElement('div');dots.className='cal-day-dots';
      bks.slice(0,4).forEach(b=>{const dot=document.createElement('span');dot.className=`cal-day-dot cal-day-dot--${b.color}${b.status==='pending'?' cal-day-dot--pending':''}`;dots.appendChild(dot);});
      blks.slice(0,2).forEach(()=>{const dot=document.createElement('span');dot.className='cal-day-dot cal-day-dot--block';dots.appendChild(dot);});
      cell.appendChild(dots);
      cell.addEventListener('click',()=>fn(ds,bks,blks));
    }
    return cell;
  }
  for(let i=first-1;i>=0;i--)grid.appendChild(mkC(dInP-i,cm===0?cy-1:cy,cm===0?11:cm-1,true,false));
  for(let d=1;d<=dInM;d++)grid.appendChild(mkC(d,cy,cm,false,d===today.getDate()&&cm===today.getMonth()&&cy===today.getFullYear()));
  const total=first+dInM,rem=total%7===0?0:7-(total%7);
  for(let d=1;d<=rem;d++)grid.appendChild(mkC(d,cm===11?cy+1:cy,cm===11?0:cm+1,true,false));
}
function showCalDetail(ds,bks,blks,panelId,dateId,bodyId){
  const d=new Date(ds+'T00:00:00');
  document.getElementById(dateId).textContent=d.toLocaleDateString('en-MY',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
  let html=`<div style="padding:10px 15px 0;"><button class="btn-secondary" style="font-size:.74rem;padding:5px 10px;" onclick="goSchedBlock('${ds}')">🗓 Block This Date</button></div>`;
  if(blks.length){html+=`<div class="booking-item" style="background:var(--off);"><div class="bi-type bi-type--block">🚫 Blocked (${blks.length})</div></div>`;blks.forEach(b=>{html+=`<div class="booking-item"><div class="bi-type bi-type--block">${BLOCK_LABELS[b.category]||'Block'}</div><div class="bi-name">${esc(b.title)}</div><div class="bi-time">${b.start}–${b.end}</div><div class="bi-rooms">${(b.rooms||[]).join(', ')}</div></div>`;});}
  if(bks.length){html+=`<div class="booking-item" style="background:var(--off);"><div class="bi-type bi-type--${bks[0]?.color||'teal'}">Bookings (${bks.length})</div></div>`;bks.forEach(b=>{html+=`<div class="booking-item"><div class="bi-type bi-type--${b.color}">${b.type_label}</div><div class="bi-name">${esc(b.name)} <span class="status-inline status-${b.status}">${b.status}</span></div><div class="bi-time">${b.start}–${b.end}</div><div class="bi-rooms">${esc(b.rooms)}</div></div>`;});}
  if(!bks.length&&!blks.length)html+='<div style="padding:20px;text-align:center;color:var(--text-light);font-size:.82rem;">No events on this date.</div>';
  document.getElementById(bodyId).innerHTML=html;
  document.getElementById(panelId).classList.add('open');
}
function goSchedBlock(ds){showView('schedule',document.getElementById('sb-schedule-link'));setTimeout(()=>scSelectDate(ds),80);}
function showDcDetail(ds,bks,blks){showCalDetail(ds,bks,blks,'dc-detail','dc-detail-date','dc-detail-body')}
function showFcDetail(ds,bks,blks){showCalDetail(ds,bks,blks,'fc-detail','fc-detail-date','fc-detail-body')}
function closeDcDetail(){document.getElementById('dc-detail').classList.remove('open')}
function closeFcDetail(){document.getElementById('fc-detail').classList.remove('open')}
document.getElementById('dc-prev').addEventListener('click',()=>{dcMonth--;if(dcMonth<0){dcMonth=11;dcYear--;}renderCal('dc-grid','dc-label',dcYear,dcMonth,showDcDetail);closeDcDetail();});
document.getElementById('dc-next').addEventListener('click',()=>{dcMonth++;if(dcMonth>11){dcMonth=0;dcYear++;}renderCal('dc-grid','dc-label',dcYear,dcMonth,showDcDetail);closeDcDetail();});
document.getElementById('fc-prev').addEventListener('click',()=>{fcMonth--;if(fcMonth<0){fcMonth=11;fcYear--;}renderCal('fc-grid','fc-label',fcYear,fcMonth,showFcDetail);closeFcDetail();});
document.getElementById('fc-next').addEventListener('click',()=>{fcMonth++;if(fcMonth>11){fcMonth=0;fcYear++;}renderCal('fc-grid','fc-label',fcYear,fcMonth,showFcDetail);closeFcDetail();});
document.getElementById('fc-filter').addEventListener('change',e=>{CAL_FILTER=e.target.value||'all';renderCal('fc-grid','fc-label',fcYear,fcMonth,showFcDetail);});

// ================================================================
// BOOKING MODAL
// ================================================================
function openModal(ref){
  const data=BOOKING_MAP[ref];if(!data)return;
  document.getElementById('modalTitle').textContent=`${data.ref} — ${data.type_label}`;
  const fields=[['Applicant',esc(data.name)],['Student ID',esc(data.id)],['Email',esc(data.email)],['Date',esc(data.date)],['Time',`${esc(data.start)} – ${esc(data.end)}`],['Rooms/Labs',esc(data.rooms),'full'],['Purpose',esc(data.purpose),'full'],['Status',statusBadgeHtml(data.status),'full']];
  const fHtml=`<div class="modal-grid">${fields.map(([l,v,c])=>`<div class="modal-field${c?' '+c:''}"><span>${l}</span><strong>${v}</strong></div>`).join('')}</div>`;
  let reassign='';
  if(data.type==='csl'){const opts=CSL_ROOMS.map(r=>`<option value="${r}"${r===data.rooms?' selected':''}>${r}</option>`).join('');reassign=`<div class="modal-section"><div class="modal-section-title">Room Management</div><div class="room-reassign-section"><div class="room-reassign-title">🔄 Reassign Room</div><div class="room-current">Currently: <strong>${esc(data.rooms)}</strong></div><div class="room-select-wrap"><select class="room-select" id="roomReassignSelect">${opts}</select><button class="btn-reassign" onclick="reassignRoom('${ref}')">Reassign</button></div><div class="reassign-note">Logged in audit trail.</div></div></div>`;}
  let decision='';
  if(data.status==='pending')decision=`<div class="modal-section"><div class="modal-section-title">Admin Decision</div><div class="remark-wrap"><label class="remark-label">Remark (required)</label><textarea id="adminRemarkInput" class="remark-input" placeholder="Write reason / notes…">${esc(data.admin_remark||'')}</textarea></div></div>`;
  document.getElementById('modalBody').innerHTML=`<div class="modal-section"><div class="modal-section-title">Submission Details</div>${fHtml}</div>${reassign}${decision}<div class="modal-section"><div class="modal-section-title">📋 Audit Trail</div>${buildAuditHtml(data.audit||[])}</div>`;
  document.getElementById('modalFooter').innerHTML=data.status==='pending'?`<button class="btn-xs btn-approve" style="padding:6px 14px;" onclick="handleModalDecision('${ref}','approved')">✓ Approve</button><button class="btn-xs btn-reject" style="padding:6px 14px;" onclick="handleModalDecision('${ref}','rejected')">✕ Reject</button>`:`<button class="btn-xs btn-view" style="padding:6px 14px;" onclick="closeModal()">Close</button>`;
  document.getElementById('modalOverlay').classList.add('open');
}
function buildAuditHtml(audit){
  if(!audit||!audit.length)return'<div class="audit-trail"><div style="font-size:.78rem;color:var(--text-light);font-style:italic;">No records yet.</div></div>';
  return`<div class="audit-trail">${audit.map(a=>`<div class="audit-row"><div class="audit-dot audit-dot--${a.type}"></div><div class="audit-content"><div class="audit-action">${esc(a.action)}</div><div class="audit-meta">By <strong>${esc(a.by)}</strong> · ${esc(a.at)}</div>${a.detail?`<div class="audit-detail">${esc(a.detail)}</div>`:''}</div></div>`).join('')}</div>`;
}
function closeModal(e){if(!e||e.target===document.getElementById('modalOverlay'))document.getElementById('modalOverlay').classList.remove('open')}
function handleModalDecision(ref,status){
  const remark=document.getElementById('adminRemarkInput')?.value?.trim();
  if(!remark){showToast('Please provide a remark first.','');document.getElementById('adminRemarkInput')?.focus();return;}
  applyDecision(ref,status,remark);closeModal();
}
function quickDecide(ref,status){
  const remark=window.prompt(`Remark for ${status}:`,'');
  if(remark===null)return;if(!remark.trim()){alert('Remark required.');return;}
  applyDecision(ref,status,remark.trim());
}
function applyDecision(ref,status,remark){
  const data=BOOKING_MAP[ref];if(!data)return;
  data.status=status;data.admin_remark=remark;
  addAudit(data,status==='approved'?'Approved':'Rejected',CURRENT_ADMIN,remark,status);
  refreshRows(ref);renderAllTables();
  showToast(`Booking ${ref} ${status}.`,status==='approved'?'toast-success':'');
}
function addAudit(data,action,by,detail,type){
  const n=new Date();const at=n.getFullYear()+'-'+pad2(n.getMonth()+1)+'-'+pad2(n.getDate())+' '+pad2(n.getHours())+':'+pad2(n.getMinutes());
  data.audit=data.audit||[];data.audit.push({action,by,at,detail,type});
}
function reassignRoom(ref){
  const data=BOOKING_MAP[ref];if(!data)return;
  const sel=document.getElementById('roomReassignSelect');const nr=sel?.value;
  if(!nr||nr===data.rooms){showToast('Select a different room.','');return;}
  const old=data.rooms;data.rooms=nr;
  addAudit(data,'Room Reassigned',CURRENT_ADMIN,`Changed from "${old}" to "${nr}"`,'reassigned');
  refreshRows(ref);openModal(ref);showToast(`Room reassigned to ${nr}`,'toast-info');
}
function refreshRows(ref){
  const data=BOOKING_MAP[ref];
  document.querySelectorAll(`tr[data-ref="${ref}"]`).forEach(row=>{
    const kids=[...row.children];const sc=kids[kids.length-2];const ac=kids[kids.length-1]?.querySelector('.action-btns');
    if(sc)sc.innerHTML=statusBadgeHtml(data.status);if(ac)ac.innerHTML=actionBtns(data);
  });
}

// ================================================================
// LABS CRUD
// ================================================================
function labStatusBadge(s){return`<span class="status-badge status-${s}">${s.charAt(0).toUpperCase()+s.slice(1)}</span>`}
function renderLabs(){
  const tbody=document.querySelector('#labs-table tbody');if(!tbody)return;
  if(!LABS.length){tbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:28px;color:var(--text-light);">No labs.</td></tr>';return;}
  tbody.innerHTML=LABS.map(l=>`<tr><td><strong>${esc(l.name)}</strong></td><td>${typeChipHtml(l.type)}</td><td style="font-size:.78rem;color:var(--text-mid);">${esc(l.code)}</td><td style="font-size:.78rem;">${esc(l.location)}</td><td>${esc(l.capacity)}</td><td>${labStatusBadge(l.status)}</td><td><div class="action-btns"><button class="btn-xs btn-view" onclick="openLabModal('${l.id}')">Edit</button><button class="btn-xs btn-delete" onclick="deleteLab('${l.id}')">✕</button></div></td></tr>`).join('');
}
function openLabModal(id=null){
  activeLabEditId=id;const isEdit=!!id;
  document.getElementById('labModalTitle').textContent=isEdit?'Edit Lab':'Add Lab';
  document.getElementById('saveLabBtn').textContent=isEdit?'💾 Update':'💾 Save';
  const lab=isEdit?LABS.find(l=>l.id===id):null;
  ['lab-name','lab-type','lab-code','lab-location','lab-capacity','lab-status','lab-notes'].forEach((fid,i)=>{
    const keys=['name','type','code','location','capacity','status','notes'];
    document.getElementById(fid).value=lab?.[keys[i]]||'';
  });
  document.getElementById('labModalOverlay').classList.add('open');
}
function closeLabModal(e){if(!e||e.target===document.getElementById('labModalOverlay'))document.getElementById('labModalOverlay').classList.remove('open');activeLabEditId=null;}
function saveLab(){
  const name=document.getElementById('lab-name').value.trim();const type=document.getElementById('lab-type').value;const code=document.getElementById('lab-code').value.trim();const location=document.getElementById('lab-location').value.trim();const capacity=parseInt(document.getElementById('lab-capacity').value,10);const status=document.getElementById('lab-status').value;const notes=document.getElementById('lab-notes').value.trim();
  if(!name||!type||!code||!location||!capacity){alert('Fill all required fields.');return;}
  if(activeLabEditId){const lab=LABS.find(l=>l.id===activeLabEditId);if(lab)Object.assign(lab,{name,type,code,location,capacity,status,notes});}
  else LABS.push({id:`LAB-${String(labIdCounter++).padStart(3,'0')}`,name,type,code,location,capacity,status,notes});
  closeLabModal();renderLabs();showToast('Lab saved.','toast-info');
}
function deleteLab(id){if(!confirm('Remove this lab?'))return;LABS=LABS.filter(l=>l.id!==id);renderLabs();showToast('Lab removed.','toast-warn');}
function filterLabTable(input,tblId){const q=input.value.toLowerCase();document.getElementById(tblId)?.querySelectorAll('tbody tr').forEach(r=>{r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';});}

// ================================================================
// STAFF CRUD
// ================================================================
function renderStaffs(){
  const tbody=document.querySelector('#staff-table tbody');if(!tbody)return;
  if(!STAFF.length){tbody.innerHTML='<tr><td colspan="5" style="text-align:center;padding:28px;">No staff.</td></tr>';return;}
  tbody.innerHTML=STAFF.map(s=>`<tr><td><strong>${esc(s.staff_id)}</strong></td><td>${esc(s.name)}</td><td>${typeChipHtml(s.role)}</td><td style="font-size:.78rem;color:var(--text-mid);">${esc(s.email)}</td><td><div class="action-btns"><button class="btn-xs btn-view" onclick="openStaffModal('${s.id}')">Edit</button><button class="btn-xs btn-delete" onclick="deleteStaff('${s.id}')">✕</button></div></td></tr>`).join('');
}
function openStaffModal(id=null){
  activeStaffEditId=id;const isEdit=!!id;
  document.getElementById('staffModalTitle').textContent=isEdit?'Edit Staff':'Add Staff';
  document.getElementById('saveStaffBtn').textContent=isEdit?'💾 Update':'💾 Save';
  const s=isEdit?STAFF.find(s=>s.id===id):null;
  document.getElementById('staff-id').value=s?.staff_id||'';document.getElementById('staff-role').value=s?.role||'';document.getElementById('staff-name').value=s?.name||'';document.getElementById('staff-email').value=s?.email||'';document.getElementById('staff-password').value=s?.password||'';
  document.getElementById('staffModalOverlay').classList.add('open');
}
function closeStaffModal(e){if(!e||e.target===document.getElementById('staffModalOverlay'))document.getElementById('staffModalOverlay').classList.remove('open');activeStaffEditId=null;}
function saveStaff(){
  const sid=document.getElementById('staff-id').value.trim();const role=document.getElementById('staff-role').value;const name=document.getElementById('staff-name').value.trim();const email=document.getElementById('staff-email').value.trim();const pw=document.getElementById('staff-password').value.trim();
  if(!sid||!role||!name||!email||!pw){alert('Fill all required fields.');return;}
  if(activeStaffEditId){const s=STAFF.find(s=>s.id===activeStaffEditId);if(s)Object.assign(s,{staff_id:sid,role,name,email,password:pw});}
  else STAFF.push({id:`STF-${String(staffIdCounter++).padStart(3,'0')}`,staff_id:sid,role,name,email,password:pw});
  closeStaffModal();renderStaffs();showToast('Staff saved.','toast-info');
}
function deleteStaff(id){if(!confirm('Remove staff?'))return;STAFF=STAFF.filter(s=>s.id!==id);renderStaffs();showToast('Staff removed.','toast-warn');}
function filterStaffTable(input,tblId){const q=input.value.toLowerCase();document.getElementById(tblId)?.querySelectorAll('tbody tr').forEach(r=>{r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';});}

// ================================================================
// FILTERS
// ================================================================
function filterTable(input,tblId){const q=input.value.toLowerCase();document.getElementById(tblId)?.querySelectorAll('tbody tr').forEach(r=>{r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';});}
function filterStatus(sel,tblId){const v=sel.value.toLowerCase();document.getElementById(tblId)?.querySelectorAll('tbody tr').forEach(r=>{r.style.display=(!v||r.textContent.toLowerCase().includes(v))?'':'none';});}

// ================================================================
// REPORT
// ================================================================
function renderReport(){
  const today=new Date();const ts=dStr(today.getFullYear(),today.getMonth(),today.getDate());
  const p14=new Date(today);p14.setDate(today.getDate()+14);const p14s=dStr(p14.getFullYear(),p14.getMonth(),p14.getDate());
  const total=ALL_BOOKINGS.length,appr=ALL_BOOKINGS.filter(b=>b.status==='approved').length,pend=ALL_BOOKINGS.filter(b=>b.status==='pending').length;
  const rate=total?Math.round(appr/total*100):0;
  const upBk=ALL_BOOKINGS.filter(b=>b.status!=='rejected'&&b.date>=ts&&b.date<=p14s).length;
  const rd=document.getElementById('reportDate');if(rd)rd.textContent=today.toLocaleDateString('en-MY',{day:'numeric',month:'short',year:'numeric'});
  document.getElementById('reportStatBookings').textContent=total;document.getElementById('reportStatApproved').textContent=appr;document.getElementById('reportStatPending').textContent=pend;document.getElementById('reportStatApprovalRate').textContent=rate+'%';document.getElementById('reportStatBlocks').textContent=ALL_BLOCKS.length;document.getElementById('reportStatUpcoming').textContent=upBk;
  const acts=[];
  ALL_BOOKINGS.forEach(b=>{if(b.audit?.length){const last=b.audit[b.audit.length-1];acts.push({time:last.at,title:`${last.action}: ${b.name}`,meta:`${b.type_label} · ${b.date}`});}});
  ALL_BLOCKS.forEach(b=>acts.push({time:`${b.date} ${b.start}`,title:`Block: ${b.title}`,meta:`${TYPE_LABELS[b.type]}`}));
  acts.sort((a,b)=>new Date(b.time.replace(' ','T'))-new Date(a.time.replace(' ','T')));
  const actEl=document.getElementById('reportActivities');
  if(actEl)actEl.innerHTML=acts.slice(0,5).map(a=>`<div class="report-item"><div class="report-item-title">${esc(a.title)}</div><div class="report-item-meta">${esc(a.time)} · ${esc(a.meta)}</div></div>`).join('')||'<div class="report-item"><div class="report-item-title">No recent activity</div></div>';
  const nb=ALL_BOOKINGS.filter(b=>b.status!=='rejected'&&b.date>=ts).sort((a,b)=>a.date.localeCompare(b.date))[0];
  const stEl=document.getElementById('reportStatus');
  if(stEl)stEl.innerHTML=[{l:'Pending approvals',v:`${pend} request${pend===1?'':'s'}`},{l:'Active blocks',v:`${ALL_BLOCKS.length} scheduled`},{l:'Next booking',v:nb?`${nb.date} ${nb.start}`:'None scheduled'}].map(s=>`<div class="report-status-chip"><span>${esc(s.l)}</span><strong>${esc(s.v)}</strong></div>`).join('');
}

// ================================================================
// TOAST
// ================================================================
function showToast(msg,cls=''){
  const t=document.getElementById('toast');t.textContent=msg;t.className='toast '+(cls||'');
  setTimeout(()=>t.classList.add('show'),10);setTimeout(()=>t.classList.remove('show'),3000);
}

// ================================================================
// INIT
// ================================================================
document.addEventListener('DOMContentLoaded',()=>{
  renderAllTables();
  initCals();
  const n=new Date();scYear=n.getFullYear();scMonth=n.getMonth();
  renderScCal();
  scGoStep(1);
});
</script>
</body>
</html>