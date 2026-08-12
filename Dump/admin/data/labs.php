<?php
/**
 * Labs Data
 * Copied from previous-used/dashboard.php (LABS), mapped to the admin schema.
 *   old `notes` → `equipment` (closest field the directory renders)
 *   `in_charge` was not in the original data → '—'
 */

$LABS = [
  ['id'=>'LAB-001','name'=>'AZ – Molecular Room (A2051)','type'=>'research','code'=>'AZ-2051','capacity'=>24,'status'=>'active',      'equipment'=>'PCR workstation.','location'=>'Block A · Level 2','in_charge'=>'—'],
  ['id'=>'LAB-002','name'=>'CSL2 – Room 3','type'=>'csl','code'=>'CSL2-R3','capacity'=>18,'status'=>'active',                        'equipment'=>'','location'=>'CSL Building · Level 2','in_charge'=>'—'],
  ['id'=>'LAB-003','name'=>'Chemistry Lab (CL)','type'=>'pharma','code'=>'PH-CL','capacity'=>28,'status'=>'maintenance',             'equipment'=>'Fume hood service.','location'=>'Pharma Block · Level 1','in_charge'=>'—'],
];

// Count by type/status
function labCount($labs, $type = null, $status = null) {
  return count(array_filter($labs, function($l) use ($type, $status) {
    if ($type   && $l['type']   !== $type)   return false;
    if ($status && $l['status'] !== $status) return false;
    return true;
  }));
}
