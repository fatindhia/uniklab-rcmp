<?php
/**
 * Bookings Data
 * Copied from previous-used/dashboard.php (ALL_BOOKINGS), mapped to the
 * admin schema. Dates are relative to the current month (as in the original),
 * so they always land on the live calendar.
 *
 *   old `type`  → `lab_type`
 *   old `rooms` → `lab`
 */

$BOOKINGS = [
  // ── Research Labs ──
  ['ref'=>'LB-2025-001','name'=>'Ahmad Zulkifli','id'=>'S12345','email'=>'ahmad@unikl.edu.my','lab_type'=>'research','type_label'=>'Research Labs','lab'=>'AZ – Molecular Room (A2051)','date'=>date('Y-m-05'),'start'=>'08:00','end'=>'12:00','purpose'=>'Biochemistry experiments for final year project','equipment'=>'','status'=>'approved','admin_remark'=>'Standard booking approved.','audit'=>[['action'=>'Created','by'=>'Ahmad Zulkifli','at'=>'2025-04-20 09:15','detail'=>'Booking submitted','type'=>'created'],['action'=>'Approved','by'=>'Dr. Sarah Admin','at'=>'2025-04-20 11:00','detail'=>'Standard booking approved.','type'=>'approved']]],
  ['ref'=>'LB-2025-005','name'=>'Farah binti Othman','id'=>'S12349','email'=>'farah@unikl.edu.my','lab_type'=>'research','type_label'=>'Research Labs','lab'=>'AZ – Microbiology Room (A2012-A2013)','date'=>date('Y-m-12'),'start'=>'08:00','end'=>'10:00','purpose'=>'Microbiology culture experiments','equipment'=>'','status'=>'rejected','admin_remark'=>'Lab under maintenance.','audit'=>[['action'=>'Created','by'=>'Farah Othman','at'=>'2025-04-23 09:00','detail'=>'Booking submitted','type'=>'created'],['action'=>'Rejected','by'=>'Dr. Sarah Admin','at'=>'2025-04-23 16:00','detail'=>'Lab under maintenance.','type'=>'rejected']]],
  ['ref'=>'LB-2025-008','name'=>'Siti Rahayu binti Ramli','id'=>'S12352','email'=>'siti@unikl.edu.my','lab_type'=>'research','type_label'=>'Research Labs','lab'=>'AZ – Assay Room (A2054)','date'=>date('Y-m-18'),'start'=>'14:00','end'=>'17:00','purpose'=>'Histology slide preparation','equipment'=>'','status'=>'approved','admin_remark'=>'Reagents prepared.','audit'=>[['action'=>'Created','by'=>'Siti Rahayu','at'=>'2025-04-25 09:10','detail'=>'Booking submitted','type'=>'created'],['action'=>'Approved','by'=>'Dr. Sarah Admin','at'=>'2025-04-25 11:00','detail'=>'Reagents prepared.','type'=>'approved']]],

  // ── CSL Labs ──
  ['ref'=>'LB-2025-002','name'=>'Nurul Ain binti Hassan','id'=>'S12346','email'=>'nurul@unikl.edu.my','lab_type'=>'csl','type_label'=>'CSL Labs','lab'=>'CSL2 – Room 3','date'=>date('Y-m-05'),'start'=>'09:00','end'=>'11:00','purpose'=>'Clinical skills practice – suturing','equipment'=>'','status'=>'pending','admin_remark'=>'','audit'=>[['action'=>'Created','by'=>'Nurul Ain binti Hassan','at'=>'2025-04-21 08:30','detail'=>'Booking submitted','type'=>'created']]],
  ['ref'=>'LB-2025-004','name'=>'Lim Wei Ling','id'=>'S12348','email'=>'lim@unikl.edu.my','lab_type'=>'csl','type_label'=>'CSL Labs','lab'=>'CSL2 – Room 3','date'=>date('Y-m-10'),'start'=>'10:00','end'=>'13:00','purpose'=>'Obstetrics simulation lab session','equipment'=>'','status'=>'pending','admin_remark'=>'','audit'=>[['action'=>'Created','by'=>'Lim Wei Ling','at'=>'2025-04-23 07:45','detail'=>'Booking submitted','type'=>'created']]],
  ['ref'=>'LB-2025-007','name'=>'Chong Mei Fang','id'=>'S12351','email'=>'chong@unikl.edu.my','lab_type'=>'csl','type_label'=>'CSL Labs','lab'=>'CSL1 – Physiko Room','date'=>date('Y-m-15'),'start'=>'09:00','end'=>'11:00','purpose'=>'Paediatric physical examination training','equipment'=>'','status'=>'approved','admin_remark'=>'Mannequins prepared.','audit'=>[['action'=>'Created','by'=>'Chong Mei Fang','at'=>'2025-04-24 08:00','detail'=>'Booking submitted','type'=>'created'],['action'=>'Approved','by'=>'Dr. Sarah Admin','at'=>'2025-04-24 10:30','detail'=>'Mannequins prepared.','type'=>'approved']]],
  ['ref'=>'LB-2025-010','name'=>'Priya a/p Krishnamurthy','id'=>'S12354','email'=>'priya@unikl.edu.my','lab_type'=>'csl','type_label'=>'CSL Labs','lab'=>'CSL2 – Room 5','date'=>date('Y-m-22'),'start'=>'10:00','end'=>'12:00','purpose'=>'Cardiac auscultation training','equipment'=>'','status'=>'approved','admin_remark'=>'Simulator ready.','audit'=>[['action'=>'Created','by'=>'Priya Krishnamurthy','at'=>'2025-04-26 10:00','detail'=>'Booking submitted','type'=>'created'],['action'=>'Approved','by'=>'Dr. Sarah Admin','at'=>'2025-04-27 09:00','detail'=>'Simulator ready.','type'=>'approved']]],

  // ── Pharma Labs ──
  ['ref'=>'LB-2025-003','name'=>'Raj Kumar s/o Murugan','id'=>'S12347','email'=>'raj@unikl.edu.my','lab_type'=>'pharma','type_label'=>'Pharma Labs','lab'=>'Pharmaceutical Lab 2 (PL2)','date'=>date('Y-m-08'),'start'=>'14:00','end'=>'17:00','purpose'=>'Drug formulation practical session','equipment'=>'','status'=>'approved','admin_remark'=>'Lab P2 confirmed available.','audit'=>[['action'=>'Created','by'=>'Raj Kumar','at'=>'2025-04-22 10:00','detail'=>'Booking submitted','type'=>'created'],['action'=>'Approved','by'=>'Dr. Sarah Admin','at'=>'2025-04-22 14:30','detail'=>'Lab P2 confirmed.','type'=>'approved']]],
  ['ref'=>'LB-2025-006','name'=>'Mohd Haziq bin Ismail','id'=>'S12350','email'=>'haziq@unikl.edu.my','lab_type'=>'pharma','type_label'=>'Pharma Labs','lab'=>'Chemistry Lab (CL)','date'=>date('Y-m-14'),'start'=>'13:00','end'=>'16:00','purpose'=>'Tablet compounding lab exercise','equipment'=>'','status'=>'pending','admin_remark'=>'','audit'=>[['action'=>'Created','by'=>'Mohd Haziq','at'=>'2025-04-24 11:20','detail'=>'Booking submitted','type'=>'created']]],
  ['ref'=>'LB-2025-009','name'=>'Hafiz bin Abdullah','id'=>'S12353','email'=>'hafiz@unikl.edu.my','lab_type'=>'pharma','type_label'=>'Pharma Labs','lab'=>'Multidisciplinary Pharma Lab (MDLP)','date'=>date('Y-m-20'),'start'=>'08:00','end'=>'12:00','purpose'=>'Stability testing','equipment'=>'','status'=>'pending','admin_remark'=>'','audit'=>[['action'=>'Created','by'=>'Hafiz Abdullah','at'=>'2025-04-26 08:00','detail'=>'Booking submitted','type'=>'created']]],
];

// Build booking map by ref
$BOOKING_MAP = [];
foreach ($BOOKINGS as $b) {
  $BOOKING_MAP[$b['ref']] = $b;
}

// Stats helpers
function bookingCount($bookings, $status = null, $type = null) {
  return count(array_filter($bookings, function($b) use ($status, $type) {
    if ($status && $b['status'] !== $status) return false;
    if ($type   && $b['lab_type'] !== $type) return false;
    return true;
  }));
}
