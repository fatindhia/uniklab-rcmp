<?php
/**
 * Lab staff demo data for the admin UI.
 */

$LAB_STAFF = [
  ['staff_id' => '620798', 'name' => 'System Administrator', 'email' => 'fatindhiya07@gmail.com', 'role' => 'admin'],
  ['staff_id' => '100001', 'name' => 'Dr. Sarah Admin', 'email' => 'sarah.admin@unikl.edu.my', 'role' => 'lab_staff'],
  ['staff_id' => '100002', 'name' => 'Muhammad Izzat', 'email' => 'izzat.lab@unikl.edu.my', 'role' => 'lab_staff'],
  ['staff_id' => '100003', 'name' => 'Nur Aisyah Rahman', 'email' => 'aisyah.lab@unikl.edu.my', 'role' => 'lab_staff'],
];

function labStaffCount($staff, $role = null) {
  return count(array_filter($staff, function($s) use ($role) {
    if ($role && $s['role'] !== $role) return false;
    return true;
  }));
}

function labStaffRoleLabel($role) {
  return $role === 'admin' ? 'Admin' : 'Lab Staff';
}
