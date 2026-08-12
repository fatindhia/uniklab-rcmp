<?php
/**
 * Demo booking data — replace with real DB queries later.
 * Each booking: ref, type (equipment|csl|pharma), name, id, email,
 *   date (Y-m-d), start, end, rooms, purpose, status, submitted, admin_remark
 */

function get_demo_bookings(): array {
    $today = new DateTime();
    $y = $today->format('Y');
    $m = $today->format('m');

    // Helper: date offset from today
    $d = fn(int $offset, string $fmt = 'Y-m-d') =>
        (new DateTime())->modify("{$offset} days")->format($fmt);

    return [
        ['ref'=>'BK-001','type'=>'equipment','type_label'=>'Equipment Labs','color'=>'teal',
         'name'=>'Nur Aisyah bt Kamal',  'id'=>'20231001','email'=>'20231001@student.unikl.edu.my',
         'date'=>$d(1),  'start'=>'09:00','end'=>'11:00',
         'rooms'=>'Molecular Room (A2051)',
         'equipment'=>['Real Time PCR System, CFX96, BioRad','Sonicator, Q500'],
         'purpose'=>'FYP Sample Analysis','status'=>'approved','submitted'=>$d(-2),'admin_remark'=>'Approved. Follow ISO17025 protocols.'],

        ['ref'=>'BK-002','type'=>'csl','type_label'=>'CSL Labs','color'=>'blue',
         'name'=>'Dr. Ahmad Hazwan',      'id'=>'S10045', 'email'=>'ahmad.hazwan@unikl.edu.my',
         'date'=>$d(1),  'start'=>'14:00','end'=>'16:00',
         'rooms'=>'CSL2 — Room 3 (Wound Dressing), Room 11 (Suturing)',
         'equipment'=>[], 'dept_program'=>'Bachelor of Medicine','category'=>'Year 3',
         'session_type'=>'Teaching','discipline'=>'Surgical',
         'purpose'=>'Year 3 Surgical Skills','status'=>'approved','submitted'=>$d(-3),'admin_remark'=>''],

        ['ref'=>'BK-003','type'=>'pharma','type_label'=>'Pharma Labs','color'=>'violet',
         'name'=>'Dr. Siti Mariam',        'id'=>'S10089', 'email'=>'siti.mariam@unikl.edu.my',
         'date'=>$d(2),  'start'=>'18:00','end'=>'21:00',
         'rooms'=>'CL — 2 groups, PL1 — 1 group',
         'equipment'=>['UV-Vis Spectrophotometer','pH Meter'],
         'purpose'=>'Pharmaceutical Chemistry Lab','status'=>'pending','submitted'=>$d(-1),'admin_remark'=>''],

        ['ref'=>'BK-004','type'=>'equipment','type_label'=>'Equipment Labs','color'=>'teal',
         'name'=>'Hafizuddin bin Rosli',   'id'=>'20230892','email'=>'20230892@student.unikl.edu.my',
         'date'=>$d(2),  'start'=>'08:30','end'=>'10:30',
         'rooms'=>'Instrumentation Room — FTIR',
         'equipment'=>['Fourier Transform Infrared Spectrophotometer (FTIR)'],
         'purpose'=>'Material Characterisation','status'=>'approved','submitted'=>$d(-2),'admin_remark'=>''],

        ['ref'=>'BK-005','type'=>'csl','type_label'=>'CSL Labs','color'=>'blue',
         'name'=>'Dr. Lim Wei Cheng',      'id'=>'S10102', 'email'=>'lim.weicheng@unikl.edu.my',
         'date'=>$d(3),  'start'=>'10:00','end'=>'12:00',
         'rooms'=>'CSL2 — Room 1 (Venepuncture / IV Line)',
         'equipment'=>[], 'dept_program'=>'Bachelor of Medicine','category'=>'Year 2',
         'session_type'=>'Practice','discipline'=>'Medical',
         'purpose'=>'IV Cannulation Practice','status'=>'pending','submitted'=>$d(-1),'admin_remark'=>''],

        ['ref'=>'BK-006','type'=>'pharma','type_label'=>'Pharma Labs','color'=>'violet',
         'name'=>'Dr. Rosnah bt Ismail',   'id'=>'S10055', 'email'=>'rosnah.ismail@unikl.edu.my',
         'date'=>$d(3),  'start'=>'19:00','end'=>'22:00',
         'rooms'=>'MDLP — 3 groups, PL2 — 2 groups',
         'equipment'=>['Rotary Evaporator (BUCHI)','Fume Hood','Incubator'],
         'purpose'=>'Drug Formulation Lab','status'=>'approved','submitted'=>$d(-4),'admin_remark'=>''],

        ['ref'=>'BK-007','type'=>'equipment','type_label'=>'Equipment Labs','color'=>'teal',
         'name'=>'Nurul Huda bt Zainudin', 'id'=>'20232201','email'=>'20232201@student.unikl.edu.my',
         'date'=>$d(5),  'start'=>'13:00','end'=>'15:00',
         'rooms'=>'Microbiology Room (A2012-A2013)',
         'equipment'=>['Biosafety Cabinet, Mars 1200','Microscope, BX43F, Olympus'],
         'purpose'=>'Bacterial Culture Study','status'=>'pending','submitted'=>$d(0),'admin_remark'=>''],

        ['ref'=>'BK-008','type'=>'csl','type_label'=>'CSL Labs','color'=>'blue',
         'name'=>'Dr. Faizal bin Mohd',    'id'=>'S10077', 'email'=>'faizal.mohd@unikl.edu.my',
         'date'=>$d(6),  'start'=>'08:00','end'=>'10:00',
         'rooms'=>'CSL1 — Simulation Room',
         'equipment'=>[], 'dept_program'=>'Bachelor of Medicine','category'=>'Year 4',
         'session_type'=>'OSCE / Assessment','discipline'=>'O&G',
         'purpose'=>'OSCE Mock Exam','status'=>'approved','submitted'=>$d(-3),'admin_remark'=>'Approved.'],

        ['ref'=>'BK-009','type'=>'equipment','type_label'=>'Equipment Labs','color'=>'teal',
         'name'=>'Mohamad Amir bin Johari','id'=>'20231588','email'=>'20231588@student.unikl.edu.my',
         'date'=>$d(-1), 'start'=>'09:00','end'=>'12:00',
         'rooms'=>'Assay Room (A2054), Plant Extraction Room (A2052)',
         'equipment'=>['Chemidoc Imaging System','Rotary Evaporator, Heidolph'],
         'purpose'=>'Research Project','status'=>'approved','submitted'=>$d(-5),'admin_remark'=>''],

        ['ref'=>'BK-010','type'=>'pharma','type_label'=>'Pharma Labs','color'=>'violet',
         'name'=>'Dr. Kartini bt Ahmad',   'id'=>'S10031', 'email'=>'kartini.ahmad@unikl.edu.my',
         'date'=>$d(8),  'start'=>'18:30','end'=>'21:30',
         'rooms'=>'CL — 1 group, MDLP — 2 groups',
         'equipment'=>['pH Meter','Water Bath','Fume Hood'],
         'purpose'=>'Cosmetic Formulation Study','status'=>'pending','submitted'=>$d(0),'admin_remark'=>''],

        ['ref'=>'BK-011','type'=>'equipment','type_label'=>'Equipment Labs','color'=>'teal',
         'name'=>'Nur Syafiqah bt Osman',  'id'=>'20230441','email'=>'20230441@student.unikl.edu.my',
         'date'=>$d(-3), 'start'=>'10:00','end'=>'13:00',
         'rooms'=>'MDL 3 (2A-31)',
         'equipment'=>['High Performance Liquid Chromatographer, Waters Alliance'],
         'purpose'=>'HPLC Analysis for Thesis','status'=>'rejected','submitted'=>$d(-6),
         'admin_remark'=>'Equipment under maintenance. Please rebook next week.'],

        ['ref'=>'BK-012','type'=>'csl','type_label'=>'CSL Labs','color'=>'blue',
         'name'=>'Dr. Zulaikha bt Yusof',  'id'=>'S10120', 'email'=>'zulaikha.yusof@unikl.edu.my',
         'date'=>$d(10), 'start'=>'14:00','end'=>'16:00',
         'rooms'=>'CSL2 — Room 9 (Breast Examination), Room 10 (NG Tube)',
         'equipment'=>[], 'dept_program'=>'Bachelor of Medicine','category'=>'Year 3',
         'session_type'=>'Revision','discipline'=>'Medical',
         'purpose'=>'Clinical Examination Skills Revision','status'=>'approved','submitted'=>$d(-1),'admin_remark'=>''],
    ];
}

/**
 * Group bookings by date → returns ['Y-m-d' => [bookings]]
 */
function bookings_by_date(array $bookings): array {
    $out = [];
    foreach ($bookings as $b) {
        $out[$b['date']][] = $b;
    }
    return $out;
}

/**
 * Filter bookings by lab type
 */
function bookings_by_type(array $bookings, string $type): array {
    return array_values(array_filter($bookings, fn($b) => $b['type'] === $type));
}
