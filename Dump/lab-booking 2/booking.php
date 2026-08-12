<?php
require_once __DIR__ . '/config/constants.php';

// ── Lab data arrays ───────────────────────────────────────────────────────────

$az_rooms = [
  'plant-extraction' => [
    'name'    => 'Plant Extraction Room',
    'code'    => 'A2052',
    'iso'     => false,
    'room_only' => false,
    'special' => false,
    'equip'   => [
      'Explosion Proof Oven, KEDA Machinery (with vacuum pump, Wiggens)',
      'Fume Hood, Chemoresources',
      'Rotary Evaporator, Heidolph',
      'Universal Oven, UF110, Memmert',
    ],
  ],
  'molecular' => [
    'name'    => 'Molecular Room',
    'code'    => 'A2051',
    'iso'     => true,
    'room_only' => false,
    'special' => false,
    'equip'   => [
      'Biophotometer, Denovix',
      'Gel Documentation System, Major Science',
      'Laminar Flow, ESCO',
      'Microcentrifuge, 5424, Eppendorf',
      'Mini Centrifuge, MiniSpin Plus, Eppendorf',
      'Portable Mini qPCR Instrument, MyGo Mini',
      'Real Time PCR System, CFX96, BioRad',
      'Sonicator, Q500, Q-Sonica',
      'Thermalcycler, Gradient, Eppendorf',
      'Thermomixer with Thermoblock, Eppendorf',
    ],
  ],
  'media-prep' => [
    'name'    => 'Media Preparation Room',
    'code'    => 'A2055',
    'iso'     => false,
    'room_only' => false,
    'special' => false,
    'equip'   => [
      'Microwave',
      'Universal Oven, UF110, Memmert',
    ],
  ],
  'assay' => [
    'name'    => 'Assay Room',
    'code'    => 'A2054',
    'iso'     => true,
    'room_only' => false,
    'special' => false,
    'equip'   => [
      'Cellulose Acetate Electrophoresis Sets, Cleaver Scientific',
      'Chemidoc Imaging System, Biorad',
      'Multimode Plate Reader, Vantastar, BMG Labtech',
      'Transfer System, Trans Blot Turbo, Biorad',
      'UV-Vis Spectrophotometer, Biochrom',
    ],
  ],
  'microbiology' => [
    'name'    => 'Microbiology Room',
    'code'    => 'A2012-A2013',
    'iso'     => true,
    'room_only' => false,
    'special' => false,
    'equip'   => [
      'Biosafety Cabinet, Mars 1200, Labogene',
      'Automatic Colony Counter & Inhibition Zone Reader, Scan1200, Interscience',
      'Incubator 1, IN55, Memmert',
      'Incubator 2, IN55, Memmert',
      'Incubator Shaker, Innova 42R, Eppendorf',
      'Incubator Shaker Top Level, Yihder',
      'Incubator Shaker Bottom Level, Yihder',
      'Microscope, BX43F, Olympus (with camera)',
      'Portable Mini Incubator, Benchmark',
      'Waterbath, WNB22, Memmert (with shaking device)',
    ],
  ],
  'cell-1' => [
    'name'    => 'Cell Culture Room 1',
    'code'    => '-',
    'iso'     => false,
    'room_only' => true,
    'special' => false,
    'equip'   => [],
  ],
  'cell-2' => [
    'name'    => 'Cell Culture Room 2',
    'code'    => '-',
    'iso'     => false,
    'room_only' => true,
    'special' => false,
    'equip'   => [],
  ],
  'cell-3' => [
    'name'    => 'Cell Culture Room 3',
    'code'    => '-',
    'iso'     => false,
    'room_only' => true,
    'special' => false,
    'equip'   => [],
  ],
  'instrumentation' => [
    'name'    => 'Instrumentation Room',
    'code'    => '-',
    'iso'     => false,
    'room_only' => false,
    'special' => true,
    'equip'   => [
      'Fourier Transform Infrared Spectrophotometer (FTIR)',
      'Freeze Dryer, Buchi (FD)',
    ],
    'special_notes' => [
      'Fourier Transform Infrared Spectrophotometer (FTIR)' => 'Weekdays only * 08:30-16:30 * Min. 60 min',
      'Freeze Dryer, Buchi (FD)'                           => 'Mon-Thu only * 10:00-16:00 * No Friday / Weekend',
    ],
  ],
];

$av_equip_rooms = [
  'mdl3' => [
    'name'    => 'MDL 3',
    'code'    => '2A-31',
    'iso'     => false,
    'room_only' => false,
    'special' => false,
    'equip'   => [
      'Fluorescence Microscope, BX53FL, Olympus (with digital camera)',
      'High Performance Liquid Chromatographer, Waters Alliance',
      'Microplate Reader, Tecan',
      'Particle Size Analyzer, Anton Paar',
    ],
  ],
  'lab-level2' => [
    'name'    => 'Lab Level 2',
    'code'    => '-',
    'iso'     => false,
    'room_only' => false,
    'special' => true,
    'equip'   => [
      'Freeze Dryer, Labogene (FD)',
      'Tissue Processor',
    ],
    'special_notes' => [
      'Freeze Dryer, Labogene (FD)' => 'Mon-Thu only * 10:00-16:00 * No Friday / Weekend',
      'Tissue Processor'             => 'Mon-Thu only * Full-day booking * 1-day buffer required before next booking',
    ],
  ],
];

$csl1_rooms = [
  'Physiko Room'    => ['skills' => []],
  'Mock Ward'       => ['skills' => []],
  'Simulation Room' => ['skills' => []],
];

$csl2_rooms = [
  'Room 1'          => ['skills' => ['Venepuncture', 'IV Line Setting', 'IM Injection']],
  'Room 2'          => ['skills' => ['Arterial Blood Sampling']],
  'Room 3'          => ['skills' => ['Wound Dressing']],
  'Room 4'          => ['skills' => ['Chest Drainage']],
  'Room 5'          => ['skills' => ['Multipurpose Room']],
  'Room 6'          => ['skills' => ['Multipurpose Room']],
  'Room 7'          => ['skills' => ['Multipurpose Room']],
  'Room 8'          => ['skills' => ['Multipurpose Room']],
  'Room 9'          => ['skills' => ['Breast Examination']],
  'Room 10'         => ['skills' => ['NG Tube Insertion', 'PR Examination']],
  'Room 11'         => ['skills' => ['Suturing']],
  'Room 12'         => ['skills' => ['Catheterization']],
  'Discussion Room' => ['skills' => []],
];

$csl_roles = ['Staff', 'Lecturer', 'Clinical Instructor', 'Year 1', 'Year 2', 'Year 3', 'Year 4'];

$csl_session_types = [
  'Teaching session',
  'Practice session',
  'Revision',
  'OCSE / Assessment',
  'Simulation / Emergency drill',
];

$csl_disciplines = [
  'Medical', 'Surgical', 'O&G', 'Primary Care', 'Anesthesiology',
  'Orthopedic', 'Paediatric', 'Ophthalmology', 'ICE Module',
  'BCC', 'ILa', 'IPE', 'Nursing', 'Midwifery', 'Physiotherapy',
];

$pharma_labs = [
  'CL'   => [
    'name'     => 'Chemistry Lab (CL)',
    'capacity' => 20,
    'equip'    => [
      'UV-Vis Spectrophotometer', 'Rotary Evaporator (IKA)', 'Rotary Evaporator (BUCHI)',
      'Melting Point (BUCHI)', 'Melting Point (Stuart)', 'pH Meter', 'Ultrasonicator',
      'Centrifuge', 'Micro Centrifuge', 'Oven', 'Fume Hood', 'Water Bath',
    ],
  ],
  'MDLP' => [
    'name'     => 'Multidisciplinary Pharmaceutical Lab (MDLP)',
    'capacity' => 40,
    'equip'    => [
      'UV-Vis Spectrophotometer', 'Rotary Evaporator (BUCHI)', 'pH Meter', 'Ultrasonicator',
      'Calorimeter', 'Incubator', 'Fume Hood', 'Laminar Flow', 'Water Bath',
    ],
  ],
  'PL1'  => [
    'name'     => 'Pharmaceutical Lab 1 (PL1)',
    'capacity' => 40,
    'equip'    => [
      'UV-Vis Spectrophotometer', 'Rotary Evaporator (IKA)', 'pH Meter', 'Ultrasonicator',
      'Micro Centrifuge', 'Oven', 'Fume Hood', 'Water Bath', 'Sonicator Qsonica',
      'Sieve Shaker', 'Digital Overheat Stirrer', 'Dissolution Apparatus',
      'Disintegration Apparatus', 'Franz Cell',
    ],
  ],
  'PL2'  => [
    'name'     => 'Pharmaceutical Lab 2 (PL2)',
    'capacity' => 40,
    'equip'    => [
      'UV-Vis Spectrophotometer', 'Rotary Evaporator (IKA)', 'pH Meter', 'Ultrasonicator',
      'Biosafety Cabinet', 'Oven', 'Fume Hood', 'Water Bath', 'Digital Overheat Stirrer',
      'Rheometer', 'Sonicator Qsonica', 'Dissolution Apparatus', 'Disintegration Apparatus',
    ],
  ],
];

// Time slots
$all_times = [];
for ($h = 7; $h <= 21; $h++) {
  foreach (['00', '30'] as $m) {
    if ($h === 21 && $m === '30') break;
    $all_times[] = sprintf('%02d:%s', $h, $m);
  }
}
$pharma_weekday_times = array_values(array_filter($all_times, fn($t) => $t >= '17:00'));
$research_times       = array_values(array_filter($all_times, fn($t) => $t >= '08:00' && $t <= '17:00'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Book a Lab &mdash; <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css" />
  <style>
    :root { --teal: #50a7b2; --teal-dark: #2a7782; --teal-light: #e3f1f3; }
    .booking-wrap { max-width: 940px; margin: 0 auto; padding: 36px 24px 80px; }

    /* Steps */
    .steps-bar { display:flex; align-items:flex-start; gap:0; margin-bottom:36px; position:relative; }
    .steps-bar::before { content:''; position:absolute; top:18px; left:32px; right:32px; height:2px; background:var(--border); z-index:0; }
    .step-item { flex:1; display:flex; flex-direction:column; align-items:center; gap:8px; position:relative; z-index:1; }
    .step-num { width:36px; height:36px; border-radius:50%; background:var(--border); color:var(--text-light); font-weight:700; font-size:.9rem; display:flex; align-items:center; justify-content:center; border:2px solid var(--border); transition:all .25s; }
    .step-item.active .step-num { background:var(--navy); color:var(--white); border-color:var(--navy); box-shadow:0 0 0 4px rgba(32,39,52,.12); }
    .step-item.done   .step-num { background:var(--teal); color:var(--white); border-color:var(--teal); }
    .step-label { font-size:.72rem; font-weight:600; color:var(--text-light); text-align:center; line-height:1.3; }
    .step-item.active .step-label { color:var(--navy); }
    .step-item.done   .step-label { color:var(--teal-dark); }

    /* Lab type cards */
    .type-cards { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:32px; }
    .type-card { border:2px solid var(--border); border-radius:var(--radius-lg); padding:24px 16px 20px; text-align:center; cursor:pointer; transition:all .2s; background:var(--white); user-select:none; }
    .type-card:hover { border-color:var(--teal); background:var(--teal-light); transform:translateY(-2px); box-shadow:var(--shadow); }
    .type-card.selected { border-color:var(--navy); background:var(--navy-light); box-shadow:var(--shadow); }
    .type-card-icon { font-size:2.2rem; margin-bottom:12px; line-height:1; }
    .type-card-name { font-family:var(--font-serif); font-size:1rem; font-weight:700; color:var(--navy); margin-bottom:6px; }
    .type-card-desc { font-size:.78rem; color:var(--text-light); line-height:1.5; }
    .type-card.selected .type-card-desc { color:var(--text-mid); }

    /* Section heading */
    .section-heading { font-family:var(--font-serif); font-size:1.05rem; font-weight:700; color:var(--navy); margin:28px 0 14px; display:flex; align-items:center; gap:10px; }
    .section-heading::after { content:''; flex:1; height:1px; background:var(--border); }

    /* Room accordion */
    .room-accordion { display:flex; flex-direction:column; gap:10px; }
    .room-card { border:1.5px solid var(--border); border-radius:var(--radius); overflow:hidden; transition:border-color .2s; }
    .room-card.has-selection { border-color:var(--teal); }
    .room-toggle { width:100%; display:flex; align-items:center; gap:12px; padding:14px 16px; background:var(--white); border:none; cursor:pointer; text-align:left; font-family:var(--font-sans); }
    .room-toggle:hover { background:var(--off-white); }
    .room-check { width:20px; height:20px; border:2px solid var(--border); border-radius:4px; flex-shrink:0; transition:all .15s; position:relative; }
    .room-card.has-selection .room-check { background:var(--teal); border-color:var(--teal); }
    .room-card.has-selection .room-check::after { content:''; position:absolute; width:5px; height:9px; border:2px solid white; border-top:none; border-left:none; transform:rotate(45deg) translate(1px,-1px); left:5px; top:2px; }
    .room-name-wrap { flex:1; }
    .room-name { font-size:.92rem; font-weight:600; color:var(--navy); }
    .room-code { font-size:.74rem; color:var(--text-light); margin-top:1px; }
    .room-badges { display:flex; align-items:center; gap:6px; margin-left:auto; flex-wrap:wrap; }
    .badge { font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:999px; letter-spacing:.04em; text-transform:uppercase; }
    .badge-iso     { background:#e3f0fb; color:#1a5fa8; border:1px solid #b3d4f5; }
    .badge-special { background:var(--warning-bg); color:var(--warning); border:1px solid #f0d080; }
    .badge-room    { background:var(--off-white); color:var(--text-light); border:1px solid var(--border); }
    .room-chevron { font-size:.75rem; color:var(--text-light); transition:transform .2s; flex-shrink:0; }
    .room-card.open .room-chevron { transform:rotate(180deg); }
    .room-equip { display:none; padding:0 16px 14px; background:var(--off-white); border-top:1px solid var(--border); }
    .room-card.open .room-equip { display:block; }
    .room-only-note { font-size:.82rem; color:var(--text-mid); padding:12px 0 4px; font-style:italic; }
    .equip-list { display:flex; flex-direction:column; gap:4px; padding-top:10px; }
    .equip-item { display:flex; align-items:flex-start; gap:10px; cursor:pointer; padding:8px 10px; border-radius:var(--radius-sm); transition:background .15s; }
    .equip-item:hover { background:#e7ecec; }
    .equip-item input[type=checkbox] { margin-top:2px; flex-shrink:0; accent-color:var(--teal); width:16px; height:16px; }
    .equip-label { font-size:.85rem; color:var(--text); line-height:1.4; }
    .equip-note  { font-size:.74rem; color:var(--warning); font-weight:600; margin-top:2px; }
    .room-only-check-wrap { display:flex; align-items:center; gap:10px; padding:12px 0 4px; cursor:pointer; }
    .room-only-check-wrap input[type=checkbox] { accent-color:var(--teal); width:17px; height:17px; }

    /* CSL rooms */
    .csl-group-label { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--text-light); margin:18px 0 10px; }
    .csl-room-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:8px; margin-bottom:12px; }
    .csl-room-btn { border:1.5px solid var(--border); border-radius:var(--radius); padding:10px 12px; text-align:left; cursor:pointer; background:var(--white); font-family:var(--font-sans); font-size:.85rem; color:var(--text); transition:all .15s; }
    .csl-room-btn:hover { border-color:var(--teal); background:var(--teal-light); }
    .csl-room-btn.selected { border-color:var(--navy); background:var(--navy-light); font-weight:600; }
    .csl-room-skills { font-size:.7rem; color:var(--text-light); margin-top:3px; }

    /* Pharma cards */
    .pharma-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-bottom:4px; }
    .pharma-card { border:2px solid var(--border); border-radius:var(--radius); padding:16px; cursor:pointer; background:var(--white); transition:all .2s; }
    .pharma-card:hover { border-color:var(--teal); }
    .pharma-card.selected { border-color:var(--navy); background:var(--navy-light); }
    .pharma-card-code { font-family:var(--font-serif); font-size:1.4rem; font-weight:700; color:var(--navy); }
    .pharma-card-name { font-size:.78rem; color:var(--text-mid); margin-top:4px; }
    .pharma-card-cap  { font-size:.72rem; color:var(--text-light); margin-top:4px; }

    /* Forms */
    .form-group { margin-bottom:20px; }
    .form-label { display:block; font-size:.8rem; font-weight:600; color:var(--text); margin-bottom:6px; letter-spacing:.02em; }
    .form-label .req { color:var(--danger); margin-left:2px; }
    .form-control { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:var(--radius-sm); font-family:var(--font-sans); font-size:.9rem; color:var(--text); background:var(--white); transition:border .15s, box-shadow .15s; outline:none; }
    .form-control:focus { border-color:var(--teal); box-shadow:0 0 0 3px rgba(80,167,178,.15); }
    select.form-control { cursor:pointer; }
    textarea.form-control { resize:vertical; min-height:90px; }
    .form-hint { font-size:.75rem; color:var(--text-light); margin-top:4px; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .date-time-row { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }

    /* Alerts */
    .alert { display:flex; gap:12px; padding:12px 16px; border-radius:var(--radius); font-size:.84rem; margin-bottom:18px; border:1px solid; line-height:1.6; }
    .alert-info    { background:#e8f4ff; color:#1a5fa8; border-color:#b3d4f5; }
    .alert-warning { background:var(--warning-bg); color:var(--warning); border-color:#f0d080; }

    /* Step panels */
    .step-panel { display:none; }
    .step-panel.active { display:block; }
    .panel-title { font-family:var(--font-serif); font-size:1.3rem; color:var(--navy); font-weight:700; margin-bottom:6px; }
    .panel-sub   { color:var(--text-mid); font-size:.88rem; margin-bottom:24px; }

    /* Student roster */
    .student-row { display:grid; grid-template-columns:1fr 1fr auto; gap:10px; align-items:center; margin-bottom:8px; }
    .student-row.with-year { grid-template-columns:1fr 1fr 80px auto; }
    .student-remove { background:none; border:1px solid var(--border); border-radius:var(--radius-sm); padding:7px 11px; cursor:pointer; color:var(--danger); font-size:.8rem; transition:all .15s; }
    .student-remove:hover { background:var(--danger-bg); border-color:var(--danger); }

    /* Review */
    .review-block { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); padding:18px 20px; margin-bottom:14px; }
    .review-block-title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-light); margin-bottom:12px; }
    .review-row { display:flex; gap:8px; margin-bottom:8px; font-size:.88rem; flex-wrap:wrap; }
    .review-key { color:var(--text-light); min-width:160px; flex-shrink:0; }
    .review-val { color:var(--text); font-weight:500; }

    /* Nav */
    .wizard-nav { display:flex; align-items:center; justify-content:space-between; margin-top:32px; padding-top:20px; border-top:1px solid var(--border); gap:12px; }
    .wizard-nav .btn { min-width:130px; }

    /* Modal */
    .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px; }
    .modal-overlay.hidden { display:none; }
    .modal-box { background:var(--white); border-radius:var(--radius-lg); max-width:560px; width:100%; box-shadow:var(--shadow-lg); overflow:hidden; }
    .modal-header { padding:20px 24px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
    .modal-header h3 { font-family:var(--font-serif); font-size:1.1rem; color:var(--navy); }
    .modal-close { background:none; border:none; font-size:1.4rem; cursor:pointer; color:var(--text-light); line-height:1; }
    .modal-body { padding:20px 24px; max-height:50vh; overflow-y:auto; font-size:.88rem; color:var(--text-mid); line-height:1.7; }
    .modal-body ul { padding-left:20px; margin:8px 0; }
    .modal-body li { margin-bottom:6px; }
    .modal-footer { padding:16px 24px; border-top:1px solid var(--border); display:flex; gap:10px; justify-content:flex-end; }

    /* Misc */
    .spinner { display:inline-block; width:14px; height:14px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; margin-right:6px; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .divider { border:none; border-top:1px solid var(--border); margin:24px 0; }

    @media(max-width:700px){
      .type-cards { grid-template-columns:1fr; }
      .pharma-grid { grid-template-columns:1fr 1fr; }
      .steps-bar::before { display:none; }
      .date-time-row { grid-template-columns:1fr; }
      .form-grid { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="breadcrumb">
      <a href="<?= BASE_URL ?>/index.php">Home</a>
      <span class="breadcrumb-sep">/</span>
      <span>Book a Lab</span>
    </div>
    <h1>Book a Lab</h1>
    <p>Submit a booking request for Research, CSL, or Pharmaceutical labs. All requests are subject to admin approval.</p>
  </div>
</div>

<div class="booking-wrap">

  <!-- Steps bar -->
  <div class="steps-bar">
    <div class="step-item active" id="stepInd1"><div class="step-num">1</div><div class="step-label">Lab &amp;<br>Equipment</div></div>
    <div class="step-item" id="stepInd2"><div class="step-num">2</div><div class="step-label">Date &amp;<br>Time</div></div>
    <div class="step-item" id="stepInd3"><div class="step-num">3</div><div class="step-label">Your<br>Details</div></div>
    <div class="step-item" id="stepInd4"><div class="step-num">4</div><div class="step-label">Review &amp;<br>Submit</div></div>
  </div>

  <form id="bookForm" method="POST" action="<?= BASE_URL ?>/booking_handler.php" novalidate>

    <!-- ═══ STEP 1 ═══ -->
    <div class="step-panel active" id="step1">
      <div class="panel-title">Select Lab Type &amp; Equipment</div>
      <div class="panel-sub">Choose the type of lab you need, then select your rooms and equipment.</div>

      <div class="type-cards">
        <div class="type-card" id="typeResearch" onclick="selectLabType('research')">
          <div class="type-card-icon">🔬</div>
          <div class="type-card-name">Research Labs</div>
          <div class="type-card-desc">Al Zahrawi &amp; Avicenna research facilities with advanced equipment.</div>
        </div>
        <div class="type-card" id="typeCsl" onclick="selectLabType('csl')">
          <div class="type-card-icon">🏥</div>
          <div class="type-card-name">CSL Labs</div>
          <div class="type-card-desc">Clinical Simulation Labs — skills training rooms and simulation facilities.</div>
        </div>
        <div class="type-card" id="typePharma" onclick="selectLabType('pharma')">
          <div class="type-card-icon">⚗️</div>
          <div class="type-card-name">Pharma Labs</div>
          <div class="type-card-desc">Pharmaceutical labs for evening &amp; weekend practical sessions.</div>
        </div>
      </div>
      <input type="hidden" name="lab_type" id="labTypeInput" />

      <!-- Research section -->
      <div id="researchSection" style="display:none;">
        <div class="alert alert-info">
          <span>ℹ️</span>
          <div>Working hours: <strong>Weekdays 08:00–17:00</strong>. Minimum booking: 60 min. Cell Culture Rooms are room-only. Instrumentation Room has per-equipment booking restrictions.</div>
        </div>
        <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
          <button type="button" class="btn btn-primary" id="btnAZ" onclick="switchResBlock('az')">Al Zahrawi Block</button>
          <button type="button" class="btn btn-ghost"   id="btnAV" onclick="switchResBlock('av')">Avicenna Research</button>
        </div>
        <input type="hidden" name="lab_block" id="labBlockInput" value="az-research" />

        <div id="azRooms">
          <div class="section-heading">Al Zahrawi Research Rooms</div>
          <div class="room-accordion">
<?php foreach ($az_rooms as $key => $room): ?>
            <div class="room-card" id="azRoom_<?= $key ?>">
              <button type="button" class="room-toggle" onclick="toggleRoom('azRoom_<?= $key ?>')">
                <div class="room-check"></div>
                <div class="room-name-wrap">
                  <div class="room-name"><?= htmlspecialchars($room['name']) ?></div>
                  <?php if ($room['code'] !== '-'): ?><div class="room-code"><?= htmlspecialchars($room['code']) ?></div><?php endif; ?>
                </div>
                <div class="room-badges">
                  <?php if ($room['iso']): ?><span class="badge badge-iso">ISO 17025</span><?php endif; ?>
                  <?php if ($room['special']): ?><span class="badge badge-special">Special Conditions</span><?php endif; ?>
                  <?php if ($room['room_only']): ?><span class="badge badge-room">Room Only</span><?php endif; ?>
                </div>
                <span class="room-chevron">▼</span>
              </button>
              <div class="room-equip">
                <?php if ($room['room_only']): ?>
                <label class="room-only-check-wrap">
                  <input type="checkbox" name="az_room[]" value="<?= $key ?>" onchange="updateRoomSel('azRoom_<?= $key ?>', this.checked)" />
                  <span>Book this room (no equipment selection)</span>
                </label>
                <div class="room-only-note">Cell Culture Rooms are booked as a whole room.</div>
                <?php elseif (!empty($room['equip'])): ?>
                <div class="equip-list">
                  <?php foreach ($room['equip'] as $eq): ?>
                  <label class="equip-item">
                    <input type="checkbox" name="az_equip[<?= $key ?>][]" value="<?= htmlspecialchars($eq) ?>" onchange="updateRoomEquip('azRoom_<?= $key ?>')" />
                    <div>
                      <div class="equip-label"><?= htmlspecialchars($eq) ?></div>
                      <?php if ($room['special'] && isset($room['special_notes'][$eq])): ?>
                      <div class="equip-note">⚠️ <?= htmlspecialchars($room['special_notes'][$eq]) ?></div>
                      <?php endif; ?>
                    </div>
                  </label>
                  <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="room-only-note">No equipment list for this room.</div>
                <?php endif; ?>
              </div>
            </div>
<?php endforeach; ?>
          </div>
        </div>

        <div id="avRooms" style="display:none;">
          <div class="section-heading">Avicenna Research Rooms</div>
          <div class="room-accordion">
<?php foreach ($av_equip_rooms as $key => $room): ?>
            <div class="room-card" id="avRoom_<?= $key ?>">
              <button type="button" class="room-toggle" onclick="toggleRoom('avRoom_<?= $key ?>')">
                <div class="room-check"></div>
                <div class="room-name-wrap">
                  <div class="room-name"><?= htmlspecialchars($room['name']) ?></div>
                  <?php if ($room['code'] !== '-'): ?><div class="room-code"><?= htmlspecialchars($room['code']) ?></div><?php endif; ?>
                </div>
                <div class="room-badges">
                  <?php if ($room['special']): ?><span class="badge badge-special">Special Conditions</span><?php endif; ?>
                </div>
                <span class="room-chevron">▼</span>
              </button>
              <div class="room-equip">
                <?php if (!empty($room['equip'])): ?>
                <div class="equip-list">
                  <?php foreach ($room['equip'] as $eq): ?>
                  <label class="equip-item">
                    <input type="checkbox" name="av_equip[<?= $key ?>][]" value="<?= htmlspecialchars($eq) ?>" onchange="updateRoomEquip('avRoom_<?= $key ?>')" />
                    <div>
                      <div class="equip-label"><?= htmlspecialchars($eq) ?></div>
                      <?php if ($room['special'] && isset($room['special_notes'][$eq])): ?>
                      <div class="equip-note">⚠️ <?= htmlspecialchars($room['special_notes'][$eq]) ?></div>
                      <?php endif; ?>
                    </div>
                  </label>
                  <?php endforeach; ?>
                </div>
                <?php endif; ?>
              </div>
            </div>
<?php endforeach; ?>
          </div>
        </div>

        <div style="margin-top:24px;">
          <div class="form-group">
            <label class="form-label">Purpose of Use / Research Title <span class="req">*</span></label>
            <textarea name="purpose_of_use" id="purposeOfUse" class="form-control" rows="3" placeholder="Briefly describe your research purpose or project title..."></textarea>
          </div>
          <div class="form-group" style="max-width:200px;">
            <label class="form-label">Number of Researchers (pax)</label>
            <input type="number" name="research_pax" id="researchPax" class="form-control" min="1" max="30" value="1" />
          </div>
          <div id="specialConditionsNotice" style="display:none;" class="alert alert-warning">
            <span>⚠️</span>
            <div>
              You have selected equipment with <strong>special booking conditions</strong>. Ensure your date/time complies with the restrictions shown above.
              <label style="display:flex;align-items:center;gap:8px;margin-top:10px;cursor:pointer;">
                <input type="checkbox" name="has_special_conditions" id="hasSpecialConditions" value="1" />
                <span>I acknowledge the special booking conditions</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- CSL section -->
      <div id="cslSection" style="display:none;">
        <div class="alert alert-info">
          <span>ℹ️</span>
          <div>CSL bookings must be made at least <strong>1 working day in advance</strong>. A 30-minute buffer is applied between sessions.</div>
        </div>
        <div class="csl-group-label">CSL 1</div>
        <div class="csl-room-grid">
<?php foreach ($csl1_rooms as $name => $info): ?>
          <button type="button" class="csl-room-btn" data-room="<?= htmlspecialchars($name) ?>" onclick="toggleCslRoom(this)">
            <div><?= htmlspecialchars($name) ?></div>
          </button>
<?php endforeach; ?>
        </div>
        <div class="csl-group-label">CSL 2</div>
        <div class="csl-room-grid">
<?php foreach ($csl2_rooms as $name => $info): ?>
          <button type="button" class="csl-room-btn" data-room="<?= htmlspecialchars($name) ?>" onclick="toggleCslRoom(this)">
            <div><?= htmlspecialchars($name) ?></div>
            <?php if (!empty($info['skills'])): ?><div class="csl-room-skills"><?= htmlspecialchars(implode(', ', $info['skills'])) ?></div><?php endif; ?>
          </button>
<?php endforeach; ?>
        </div>
        <div id="cslRoomsHidden"></div>

        <hr class="divider">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Session Type <span class="req">*</span></label>
            <select name="csl_session_type" id="cslSessionType" class="form-control">
              <option value="">— Select —</option>
<?php foreach ($csl_session_types as $t): ?>
              <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Discipline <span class="req">*</span></label>
            <select name="csl_discipline" id="cslDiscipline" class="form-control">
              <option value="">— Select —</option>
<?php foreach ($csl_disciplines as $d): ?>
              <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
<?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group" style="max-width:220px;">
          <label class="form-label">Number of Students</label>
          <input type="number" name="csl_num_students" id="cslNumStudents" class="form-control" min="0" value="0" />
        </div>
      </div>

      <!-- Pharma section -->
      <div id="pharmaSection" style="display:none;">
        <div class="alert alert-warning">
          <span>⚠️</span>
          <div>Pharma labs are available <strong>weekday evenings (17:00–21:00)</strong> and <strong>weekends (08:00–21:00)</strong> only.</div>
        </div>
        <div class="section-heading">Select Primary Lab</div>
        <div class="pharma-grid">
<?php foreach ($pharma_labs as $code => $lab): ?>
          <div class="pharma-card" id="pharmaCard_<?= $code ?>" onclick="selectPharmaLab('<?= $code ?>')">
            <div class="pharma-card-code"><?= htmlspecialchars($code) ?></div>
            <div class="pharma-card-name"><?= htmlspecialchars($lab['name']) ?></div>
            <div class="pharma-card-cap">Capacity: <?= $lab['capacity'] ?> pax</div>
          </div>
<?php endforeach; ?>
        </div>
        <input type="hidden" name="pharma_primary_lab" id="pharmaPrimaryLab" />

        <div id="pharmaEquipSection" style="display:none;margin-top:20px;">
          <div class="section-heading">Equipment in Selected Lab</div>
          <div id="pharmaEquipList" class="equip-list"></div>
        </div>

        <hr class="divider">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Group Number <span class="req">*</span></label>
            <input type="text" name="pharma_group_number" id="pharmaGroupNumber" class="form-control" placeholder="e.g. Group A3" />
          </div>
          <div class="form-group">
            <label class="form-label">Number of Students</label>
            <input type="number" name="pharma_num_students" id="pharmaNS" class="form-control" min="0" value="0" />
          </div>
        </div>
        <div class="alert alert-info">
          <span>📋</span>
          <div>
            You must agree to the Pharma Lab Terms &amp; Conditions before submitting.
            <button type="button" class="btn btn-sm btn-ghost" style="margin-left:8px;" onclick="showPharmaTC()">View T&amp;C</button>
          </div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:.88rem;margin-top:6px;cursor:pointer;">
          <input type="checkbox" name="pharma_tc_accepted" id="pharmaTcAccepted" value="1" />
          <span>I have read and agree to the Pharma Lab Terms &amp; Conditions</span>
        </label>
      </div>

      <!-- General purpose -->
      <div id="generalPurposeWrap" style="display:none;margin-top:24px;">
        <div class="form-group">
          <label class="form-label">General Purpose / Booking Reason <span class="req">*</span></label>
          <textarea name="purpose" id="generalPurpose" class="form-control" rows="3" placeholder="Briefly describe the purpose of this booking..."></textarea>
        </div>
      </div>

      <div class="wizard-nav">
        <div></div>
        <button type="button" class="btn btn-primary" onclick="goStep(2)">Next: Date &amp; Time →</button>
      </div>
    </div>

    <!-- ═══ STEP 2 ═══ -->
    <div class="step-panel" id="step2">
      <div class="panel-title">Select Date &amp; Time</div>
      <div class="panel-sub">Choose your booking date and time slot.</div>

      <div class="date-time-row">
        <div class="form-group">
          <label class="form-label">From Date <span class="req">*</span></label>
          <input type="date" name="booking_date_from" id="dateFrom" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" />
        </div>
        <div class="form-group">
          <label class="form-label">To Date <span class="req">*</span></label>
          <input type="date" name="booking_date_to" id="dateTo" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" />
          <div class="form-hint">Same as From Date for single-day bookings.</div>
        </div>
        <div></div>
      </div>

      <div class="date-time-row">
        <div class="form-group">
          <label class="form-label">Start Time <span class="req">*</span></label>
          <select name="start_time" id="startTime" class="form-control">
            <option value="">— Select —</option>
<?php foreach ($all_times as $t): ?>
            <option value="<?= $t ?>"><?= $t ?></option>
<?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">End Time <span class="req">*</span></label>
          <select name="end_time" id="endTime" class="form-control">
            <option value="">— Select —</option>
<?php foreach ($all_times as $t): ?>
            <option value="<?= $t ?>"><?= $t ?></option>
<?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Duration</label>
          <div class="form-control" id="durationDisplay" style="background:var(--off-white);color:var(--text-mid);">—</div>
        </div>
      </div>
      <div id="timeRestrictionNote" style="display:none;"></div>

      <div class="wizard-nav">
        <button type="button" class="btn btn-ghost" onclick="goStep(1)">← Back</button>
        <button type="button" class="btn btn-primary" onclick="goStep(3)">Next: Your Details →</button>
      </div>
    </div>

    <!-- ═══ STEP 3 ═══ -->
    <div class="step-panel" id="step3">
      <div class="panel-title">Your Details</div>
      <div class="panel-sub">Fill in your personal information. This will be recorded with the booking.</div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Full Name <span class="req">*</span></label>
          <input type="text" name="applicant_name" id="applicantName" class="form-control" placeholder="Full name as per ID" />
        </div>
        <div class="form-group">
          <label class="form-label">Staff / Student ID <span class="req">*</span></label>
          <input type="text" name="applicant_id" id="applicantId" class="form-control" placeholder="e.g. 620123 or 20231234" />
        </div>
        <div class="form-group">
          <label class="form-label">Email Address <span class="req">*</span></label>
          <input type="email" name="applicant_email" id="applicantEmail" class="form-control" placeholder="your@unikl.edu.my" />
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="tel" name="applicant_phone" id="applicantPhone" class="form-control" placeholder="+60 12-345 6789" />
        </div>
        <div class="form-group">
          <label class="form-label">Department / Faculty <span class="req">*</span></label>
          <input type="text" name="applicant_department" id="applicantDept" class="form-control" placeholder="e.g. Faculty of Pharmacy" />
        </div>
        <div class="form-group">
          <label class="form-label">Role <span class="req">*</span></label>
          <select name="applicant_role" id="applicantRole" class="form-control">
            <option value="">— Select Role —</option>
<?php foreach ($csl_roles as $r): ?>
            <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
<?php endforeach; ?>
            <option value="Postgraduate">Postgraduate</option>
            <option value="Undergraduate">Undergraduate</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Additional Remarks</label>
        <textarea name="applicant_remark" id="applicantRemark" class="form-control" rows="3" placeholder="Any special requirements or notes for the admin..."></textarea>
      </div>

      <div id="cslStudentSection" style="display:none;">
        <div class="section-heading">Student List</div>
        <div id="cslStudentRows"></div>
        <button type="button" class="btn btn-ghost btn-sm" style="margin-top:4px;" onclick="addStudentRow('csl')">+ Add Student</button>
      </div>
      <div id="pharmaStudentSection" style="display:none;">
        <div class="section-heading">Student / Group List</div>
        <div id="pharmaStudentRows"></div>
        <button type="button" class="btn btn-ghost btn-sm" style="margin-top:4px;" onclick="addStudentRow('pharma')">+ Add Student</button>
      </div>

      <div class="wizard-nav">
        <button type="button" class="btn btn-ghost" onclick="goStep(2)">← Back</button>
        <button type="button" class="btn btn-primary" onclick="goStep(4)">Next: Review →</button>
      </div>
    </div>

    <!-- ═══ STEP 4 ═══ -->
    <div class="step-panel" id="step4">
      <div class="panel-title">Review &amp; Submit</div>
      <div class="panel-sub">Review your booking details below before submitting.</div>
      <div id="reviewContent"></div>
      <div class="alert alert-warning" style="margin-top:16px;">
        <span>⚠️</span>
        <div>Submitting this form does <strong>not</strong> guarantee a booking. All requests are subject to admin approval. You will be notified by email once reviewed.</div>
      </div>
      <div class="wizard-nav">
        <button type="button" class="btn btn-ghost" onclick="goStep(3)">← Back</button>
        <button type="submit" class="btn btn-primary" id="submitBtn" style="background:var(--teal);">Submit Booking Request</button>
      </div>
    </div>

  </form>
</div>

<!-- Pharma T&C Modal -->
<div class="modal-overlay hidden" id="pharmaTcModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3>Pharmaceutical Lab — Terms &amp; Conditions</h3>
      <button type="button" class="modal-close" onclick="hidePharmaTC()">×</button>
    </div>
    <div class="modal-body">
      <p>By booking the Pharmaceutical Lab, you agree to the following:</p>
      <ul>
        <li>Bookings are available on <strong>weekdays 17:00–21:00</strong> and <strong>weekends 08:00–21:00</strong>.</li>
        <li>The lab must be left clean and in the same condition as found. Any damage must be reported immediately.</li>
        <li>All chemicals must be stored appropriately. Hazardous waste must be disposed of per RCMP guidelines.</li>
        <li>Students must be supervised at all times. The applicant is responsible for all students during the session.</li>
        <li>Unauthorised use of equipment outside the booked time slot is strictly prohibited.</li>
        <li>Bookings must be cancelled at least <strong>24 hours</strong> in advance if not proceeding.</li>
        <li>Repeated no-shows may result in suspension of booking privileges.</li>
        <li>RCMP reserves the right to cancel any booking due to maintenance or unforeseen circumstances.</li>
      </ul>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" onclick="hidePharmaTC()">Close</button>
      <button type="button" class="btn btn-primary" onclick="acceptPharmaTC()">I Agree</button>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// ════ State ════════════════════════════════════════════════════════
let curStep       = 1;
let labType       = '';
let resBlock      = 'az';
let pharmaLab     = '';
const cslRooms    = new Set();

const pharmaEquipData = <?= json_encode(
  array_values(array_map(fn($code, $lab) => [
    'code'     => $code,
    'name'     => $lab['name'],
    'capacity' => $lab['capacity'],
    'equip'    => $lab['equip'],
  ], array_keys($pharma_labs), $pharma_labs)),
  JSON_UNESCAPED_UNICODE
) ?>;

const allTimes      = <?= json_encode($all_times) ?>;
const resTimes      = <?= json_encode($research_times) ?>;
const pharmaTimes   = <?= json_encode($pharma_weekday_times) ?>;

const specialEquip  = <?= json_encode(array_values(array_merge(
  ...array_map(fn($r) => $r['special'] ? array_keys($r['special_notes'] ?? []) : [], array_values($az_rooms)),
  ...array_map(fn($r) => $r['special'] ? array_keys($r['special_notes'] ?? []) : [], array_values($av_equip_rooms))
))) ?>;

// ════ Step nav ═════════════════════════════════════════════════════
function goStep(n) {
  if (n > curStep && !validateStep(curStep)) return;
  document.getElementById('step' + curStep).classList.remove('active');
  document.getElementById('step' + n).classList.add('active');
  for (let i = 1; i <= 4; i++) {
    const el = document.getElementById('stepInd' + i);
    el.classList.remove('active','done');
    if (i < n)  el.classList.add('done');
    if (i === n) el.classList.add('active');
  }
  curStep = n;
  if (n === 4) buildReview();
  window.scrollTo({top:0,behavior:'smooth'});
}

// ════ Validation ═══════════════════════════════════════════════════
function validateStep(s) {
  if (s === 1) {
    if (!labType) { alert('Please select a lab type.'); return false; }
    if (labType === 'research') {
      if (!getResSelections().length) { alert('Please select at least one room or piece of equipment.'); return false; }
      if (!v('purposeOfUse')) { alert('Please enter the purpose of use / research title.'); return false; }
      if (hasSpecial() && !document.getElementById('hasSpecialConditions').checked) {
        alert('Please acknowledge the special booking conditions.'); return false;
      }
    }
    if (labType === 'csl') {
      if (!cslRooms.size) { alert('Please select at least one CSL room.'); return false; }
      if (!v('cslSessionType')) { alert('Please select a session type.'); return false; }
      if (!v('cslDiscipline'))  { alert('Please select a discipline.');  return false; }
    }
    if (labType === 'pharma') {
      if (!pharmaLab) { alert('Please select a primary Pharma lab.'); return false; }
      if (!v('pharmaGroupNumber')) { alert('Please enter a group number.'); return false; }
      if (!document.getElementById('pharmaTcAccepted').checked) { alert('Please accept the Pharma Lab Terms & Conditions.'); return false; }
    }
    if (!v('generalPurpose')) { alert('Please enter the general purpose / booking reason.'); return false; }
    return true;
  }
  if (s === 2) {
    const from = v('dateFrom'), to = v('dateTo'), st = v('startTime'), et = v('endTime');
    if (!from) { alert('Please select a From Date.'); return false; }
    if (!to)   { alert('Please select a To Date.');   return false; }
    if (to < from) { alert('To Date cannot be before From Date.'); return false; }
    if (!st) { alert('Please select a Start Time.'); return false; }
    if (!et) { alert('Please select an End Time.');  return false; }
    if (et <= st) { alert('End Time must be after Start Time.'); return false; }
    if (tdiff(st,et) < 60) { alert('Minimum booking duration is 60 minutes.'); return false; }
    return true;
  }
  if (s === 3) {
    if (!v('applicantName'))  { alert('Please enter your full name.');        return false; }
    if (!v('applicantId'))    { alert('Please enter your Staff/Student ID.'); return false; }
    if (!v('applicantEmail')) { alert('Please enter your email address.');    return false; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v('applicantEmail'))) { alert('Please enter a valid email address.'); return false; }
    if (!v('applicantDept'))  { alert('Please enter your department.');       return false; }
    if (!v('applicantRole'))  { alert('Please select your role.');            return false; }
    return true;
  }
  return true;
}
function v(id) { return document.getElementById(id).value.trim(); }
function tdiff(a,b) { const [ah,am]=a.split(':').map(Number),[bh,bm]=b.split(':').map(Number); return (bh*60+bm)-(ah*60+am); }

// ════ Lab type ═════════════════════════════════════════════════════
function selectLabType(t) {
  labType = t;
  document.getElementById('labTypeInput').value = t;
  ['Research','Csl','Pharma'].forEach(x => {
    document.getElementById('type'+x).classList.remove('selected');
    document.getElementById(x.toLowerCase()+'Section').style.display='none';
  });
  if (t === 'research') {
    document.getElementById('typeResearch').classList.add('selected');
    document.getElementById('researchSection').style.display='block';
    document.getElementById('labBlockInput').value='az-research';
  } else if (t === 'csl') {
    document.getElementById('typeCsl').classList.add('selected');
    document.getElementById('cslSection').style.display='block';
    document.getElementById('labBlockInput').value='csl';
  } else if (t === 'pharma') {
    document.getElementById('typePharma').classList.add('selected');
    document.getElementById('pharmaSection').style.display='block';
    document.getElementById('labBlockInput').value='pharma';
  }
  document.getElementById('generalPurposeWrap').style.display='block';
  refreshTimeDDs();
}

// ════ Research block ═══════════════════════════════════════════════
function switchResBlock(b) {
  resBlock = b;
  document.getElementById('azRooms').style.display = b==='az'?'block':'none';
  document.getElementById('avRooms').style.display = b==='av'?'block':'none';
  document.getElementById('labBlockInput').value   = b==='az'?'az-research':'av-research';
  document.getElementById('btnAZ').className = b==='az'?'btn btn-primary':'btn btn-ghost';
  document.getElementById('btnAV').className = b==='av'?'btn btn-primary':'btn btn-ghost';
}

// ════ Room accordion ═══════════════════════════════════════════════
function toggleRoom(id) { document.getElementById(id).classList.toggle('open'); }

function updateRoomEquip(cardId) {
  const checked = document.getElementById(cardId).querySelectorAll('input[type=checkbox]:checked').length;
  document.getElementById(cardId).classList.toggle('has-selection', checked > 0);
  updateSpecialNotice();
}
function updateRoomSel(cardId, on) { document.getElementById(cardId).classList.toggle('has-selection', on); }

function getResSelections() {
  const p = resBlock==='az'?'az':'av';
  return [...document.querySelectorAll('#'+p+'Rooms input[type=checkbox]:checked')].map(c=>c.value);
}
function hasSpecial() {
  return [...document.querySelectorAll('#researchSection input[type=checkbox]:checked')]
    .some(c => specialEquip.includes(c.value));
}
function updateSpecialNotice() {
  const n = document.getElementById('specialConditionsNotice');
  if (n) n.style.display = hasSpecial()?'block':'none';
}

// ════ CSL ══════════════════════════════════════════════════════════
function toggleCslRoom(btn) {
  const r = btn.dataset.room;
  cslRooms.has(r) ? (cslRooms.delete(r), btn.classList.remove('selected'))
                  : (cslRooms.add(r),    btn.classList.add('selected'));
  const wrap = document.getElementById('cslRoomsHidden');
  wrap.innerHTML = '';
  cslRooms.forEach(room => {
    const i = document.createElement('input');
    i.type='hidden'; i.name='csl_rooms[]'; i.value=room;
    wrap.appendChild(i);
  });
}

// ════ Pharma ═══════════════════════════════════════════════════════
function selectPharmaLab(code) {
  pharmaLab = code;
  document.getElementById('pharmaPrimaryLab').value = code;
  document.querySelectorAll('.pharma-card').forEach(c=>c.classList.remove('selected'));
  document.getElementById('pharmaCard_'+code).classList.add('selected');
  const lab = pharmaEquipData.find(l=>l.code===code);
  if (!lab) return;
  document.getElementById('pharmaEquipSection').style.display='block';
  document.getElementById('pharmaEquipList').innerHTML = lab.equip.map(e=>
    `<label class="equip-item"><input type="checkbox" name="pharma_equip[]" value="${escH(e)}" /><div class="equip-label">${escH(e)}</div></label>`
  ).join('');
}

// ════ Time dropdowns ═══════════════════════════════════════════════
function refreshTimeDDs() {
  const st=document.getElementById('startTime'), et=document.getElementById('endTime');
  const note=document.getElementById('timeRestrictionNote');
  let times=allTimes, msg='';
  if (labType==='research') {
    times=resTimes;
    msg='<div class="alert alert-info"><span>ℹ️</span><div>Research lab hours: <strong>Weekdays 08:00–17:00</strong> only.</div></div>';
  } else if (labType==='pharma') {
    times=pharmaTimes;
    msg='<div class="alert alert-warning"><span>⚠️</span><div>Pharma weekday slots: <strong>17:00–21:00</strong>. For weekend sessions (08:00–21:00) note in remarks.</div></div>';
  }
  const blank='<option value="">— Select —</option>';
  const opts=times.map(t=>`<option value="${t}">${t}</option>`).join('');
  st.innerHTML=blank+opts; et.innerHTML=blank+opts;
  note.innerHTML=msg; note.style.display=msg?'block':'none';
}
document.getElementById('startTime').addEventListener('change', calcDuration);
document.getElementById('endTime').addEventListener('change', calcDuration);
function calcDuration() {
  const st=v('startTime'), et=v('endTime'), d=document.getElementById('durationDisplay');
  if (!st||!et||et<=st){d.textContent='—';return;}
  const m=tdiff(st,et); if(m<=0){d.textContent='—';return;}
  d.textContent=(Math.floor(m/60)?Math.floor(m/60)+'h ':'')+(m%60?m%60+'min':'');
}
document.getElementById('dateFrom').addEventListener('change',function(){
  const to=document.getElementById('dateTo');
  to.min=this.value; if(to.value&&to.value<this.value) to.value=this.value;
});

// ════ Student roster ═══════════════════════════════════════════════
let cslCount=0, pharmaCount=0;
function addStudentRow(type) {
  if (type==='csl') {
    const n=++cslCount, row=document.createElement('div');
    row.className='student-row with-year'; row.id='cslStu'+n;
    row.innerHTML=`<input type="text" name="csl_student_name[]" class="form-control" placeholder="Full Name" />`+
      `<input type="text" name="csl_student_id[]" class="form-control" placeholder="Student ID" />`+
      `<select name="csl_student_year[]" class="form-control"><option value="">Yr</option><option>1</option><option>2</option><option>3</option><option>4</option></select>`+
      `<button type="button" class="student-remove" onclick="this.closest('.student-row').remove()">✕</button>`;
    document.getElementById('cslStudentRows').appendChild(row);
  } else {
    const n=++pharmaCount, row=document.createElement('div');
    row.className='student-row'; row.id='pharmaStu'+n;
    row.innerHTML=`<input type="text" name="pharma_student_name[]" class="form-control" placeholder="Full Name" />`+
      `<input type="text" name="pharma_student_id[]" class="form-control" placeholder="Student ID" />`+
      `<button type="button" class="student-remove" onclick="this.closest('.student-row').remove()">✕</button>`;
    document.getElementById('pharmaStudentRows').appendChild(row);
  }
}

// ════ Review ═══════════════════════════════════════════════════════
function buildReview() {
  document.getElementById('cslStudentSection').style.display   = labType==='csl'   ?'block':'none';
  document.getElementById('pharmaStudentSection').style.display = labType==='pharma'?'block':'none';
  const typeLabel={research:'Research Labs',csl:'CSL Labs',pharma:'Pharma Labs'}[labType]||labType;
  let labRows='';
  if (labType==='research') {
    const blk=document.getElementById('labBlockInput').value;
    labRows  = rv('Lab Block', blk==='az-research'?'Al Zahrawi':'Avicenna');
    labRows += rv('Selected Equipment / Rooms', getResSelections().join(', ')||'—');
    labRows += rv('Purpose of Use', v('purposeOfUse'));
    labRows += rv('Researchers (pax)', document.getElementById('researchPax').value);
  } else if (labType==='csl') {
    labRows  = rv('CSL Rooms', [...cslRooms].join(', ')||'—');
    labRows += rv('Session Type', v('cslSessionType'));
    labRows += rv('Discipline', v('cslDiscipline'));
    labRows += rv('No. of Students', document.getElementById('cslNumStudents').value);
  } else if (labType==='pharma') {
    const lab=pharmaEquipData.find(l=>l.code===pharmaLab);
    labRows  = rv('Primary Lab', lab?lab.name:pharmaLab);
    labRows += rv('Group Number', v('pharmaGroupNumber'));
    labRows += rv('No. of Students', document.getElementById('pharmaNS').value);
  }
  document.getElementById('reviewContent').innerHTML=`
    <div class="review-block"><div class="review-block-title">Lab &amp; Booking</div>${rv('Lab Type',typeLabel)}${labRows}${rv('General Purpose',v('generalPurpose'))}</div>
    <div class="review-block"><div class="review-block-title">Date &amp; Time</div>${rv('From Date',v('dateFrom'))}${rv('To Date',v('dateTo'))}${rv('Start Time',v('startTime'))}${rv('End Time',v('endTime'))}</div>
    <div class="review-block"><div class="review-block-title">Applicant Details</div>${rv('Full Name',v('applicantName'))}${rv('ID',v('applicantId'))}${rv('Email',v('applicantEmail'))}${rv('Phone',v('applicantPhone')||'—')}${rv('Department',v('applicantDept'))}${rv('Role',v('applicantRole'))}</div>`;
}
function rv(k,val) { return `<div class="review-row"><span class="review-key">${escH(k)}</span><span class="review-val">${escH(val)}</span></div>`; }

// ════ Pharma TC ════════════════════════════════════════════════════
function showPharmaTC()  { document.getElementById('pharmaTcModal').classList.remove('hidden'); }
function hidePharmaTC()  { document.getElementById('pharmaTcModal').classList.add('hidden'); }
function acceptPharmaTC(){ document.getElementById('pharmaTcAccepted').checked=true; hidePharmaTC(); }
document.getElementById('pharmaTcModal').addEventListener('click',function(e){if(e.target===this)hidePharmaTC();});

// ════ Submit spinner ═══════════════════════════════════════════════
document.getElementById('bookForm').addEventListener('submit',function(){
  const b=document.getElementById('submitBtn');
  b.disabled=true; b.innerHTML='<span class="spinner"></span>Submitting…';
});

// ════ Utility ══════════════════════════════════════════════════════
function escH(s){const d=document.createElement('div');d.textContent=String(s||'');return d.innerHTML;}
</script>

</body>
</html>
