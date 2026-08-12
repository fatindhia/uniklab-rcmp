<?php
/**
 * Reports / Blocks Data
 * $BLOCKED_SLOTS copied from previous-used/dashboard.php (ALL_BLOCKS).
 * Each block keeps the original rich fields (category, title, pic, rooms,
 * notes) AND flat `lab`/`reason` fields so every page can read it:
 *   `lab`    = the room name(s)
 *   `reason` = the block title
 * Dates are relative to the current month (as in the original).
 */

$BLOCKED_SLOTS = [
  ['id'=>'BLK-001','type'=>'csl','category'=>'class','title'=>'Year 3 – CSL Suturing Class','pic'=>'Dr. Ahmad Hafizi','date'=>date('Y-m-05'),'start'=>'08:00','end'=>'09:00','rooms'=>['CSL2 – Room 11'],'recurring'=>'weekly','notes'=>'Weekly suturing practical.','lab'=>'CSL2 – Room 11','reason'=>'Year 3 – CSL Suturing Class'],
  ['id'=>'BLK-002','type'=>'csl','category'=>'class','title'=>'Year 2 – Mock Ward Skills','pic'=>'Dr. Siti Norzahira','date'=>date('Y-m-05'),'start'=>'09:30','end'=>'12:00','rooms'=>['CSL1 – Mock Ward'],'recurring'=>'weekly','notes'=>'','lab'=>'CSL1 – Mock Ward','reason'=>'Year 2 – Mock Ward Skills'],
  ['id'=>'BLK-003','type'=>'research','category'=>'maintenance','title'=>'Biosafety Cabinet Annual Service','pic'=>'Facilities Dept.','date'=>date('Y-m-08'),'start'=>'08:00','end'=>'17:00','rooms'=>['AZ – Microbiology Room (A2012-A2013)'],'recurring'=>'none','notes'=>'Annual certification by external vendor.','lab'=>'AZ – Microbiology Room (A2012-A2013)','reason'=>'Biosafety Cabinet Annual Service'],
  ['id'=>'BLK-004','type'=>'pharma','category'=>'class','title'=>'Year 4 – Pharmaceutical Analysis','pic'=>'Dr. Nurul Hidayah','date'=>date('Y-m-10'),'start'=>'14:00','end'=>'17:00','rooms'=>['Chemistry Lab (CL)','Multidisciplinary Pharma Lab (MDLP)'],'recurring'=>'weekly','notes'=>'','lab'=>'Chemistry Lab (CL), Multidisciplinary Pharma Lab (MDLP)','reason'=>'Year 4 – Pharmaceutical Analysis'],
  ['id'=>'BLK-005','type'=>'csl','category'=>'exam','title'=>'OSCE Year 4 – Station 3–5','pic'=>'Dr. Lim Wei Lin','date'=>date('Y-m-15'),'start'=>'08:00','end'=>'16:00','rooms'=>['CSL2 – Room 3','CSL2 – Room 4','CSL2 – Room 5'],'recurring'=>'none','notes'=>'End of block OSCE.','lab'=>'CSL2 – Room 3, CSL2 – Room 4, CSL2 – Room 5','reason'=>'OSCE Year 4 – Station 3–5'],
];

// ── Aggregate analytics (system report only; no equivalent in the original) ──
$MONTHLY_STATS = [
  ['month'=>'Jan','bookings'=>18,'approved'=>14,'rejected'=>2,'pending'=>2],
  ['month'=>'Feb','bookings'=>22,'approved'=>18,'rejected'=>2,'pending'=>2],
  ['month'=>'Mar','bookings'=>31,'approved'=>25,'rejected'=>3,'pending'=>3],
  ['month'=>'Apr','bookings'=>28,'approved'=>22,'rejected'=>4,'pending'=>2],
  ['month'=>'May','bookings'=>35,'approved'=>29,'rejected'=>3,'pending'=>3],
  ['month'=>'Jun','bookings'=>10,'approved'=>7, 'rejected'=>1,'pending'=>2],
];

$LAB_USAGE = [
  ['lab'=>'CSL2 – Room 3',                    'type'=>'csl',      'sessions'=>42, 'hours'=>84],
  ['lab'=>'AZ – Molecular Room (A2051)',      'type'=>'research', 'sessions'=>38, 'hours'=>95],
  ['lab'=>'Chemistry Lab (CL)',               'type'=>'pharma',   'sessions'=>30, 'hours'=>60],
  ['lab'=>'CSL1 – Physiko Room',              'type'=>'csl',      'sessions'=>27, 'hours'=>54],
  ['lab'=>'AZ – Assay Room (A2054)',          'type'=>'research', 'sessions'=>22, 'hours'=>66],
];
