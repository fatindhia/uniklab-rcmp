<?php

// Which CSL rooms each discipline may be booked in. The keys double as the
// discipline dropdown's options (and its display order); the values are lab
// names exactly as seeded in the labs table.
$cslDisciplineRooms = [
    'Medical' => ['Physiko Room', 'Mock Ward', 'Room 6', 'Room 7'],
    'Surgical' => ['Room 1', 'Room 2', 'Room 3', 'Room 4', 'Room 9', 'Room 10', 'Room 11', 'Room 12'],
    'O&G' => ['Mock Ward', 'Room 6', 'Room 7'],
    'Primary Care' => ['Mock Ward', 'Room 7', 'Discussion Room'],
    'Anesthesiology' => ['Mock Ward', 'Room 7'],
    'Orthopedic' => ['Mock Ward', 'Room 5', 'Room 6', 'Room 7'],
    'Paediatric' => ['Discussion Room', 'Room 7'],
    'Ophthalmology' => ['Room 6', 'Room 8'],
    'ICE Module' => ['Room 5', 'Room 6', 'Room 7', 'Discussion Room'],
    'BCC Surgery' => ['Room 1', 'Room 3', 'Room 9', 'Room 10'],
    'BCC Medicine' => ['Room 6', 'Room 7', 'Room 8'],
    'ILA' => ['Room 7', 'Room 8'],
    'IPE' => ['Simulation Room', 'Mock Ward'],
    'Nursing' => ['Mock Ward'],
    'Midwifery' => ['Room 7', 'Simulation Room', 'Mock Ward'],
    'Physiotherapy' => ['Mock Ward'],
];

return [
    'work_start' => '08:00',
    'work_end' => '17:00',
    'min_booking_minutes' => 60,

    // Applicant email must end in one of these two domains. Student emails are
    // checked first since the student domain is a subdomain of the staff one.
    'staff_email_domain' => 'unikl.edu.my',
    'student_email_domain' => 's.unikl.edu.my',

    'staff_roles' => ['Staff', 'Lecturer', 'Clinical Instructor'],
    'student_roles' => ['Year 1', 'Year 2', 'Year 3', 'Year 4', 'Postgraduate'],

    // Pharma bookings are restricted to these applicant roles (staff domain only).
    'pharma_allowed_roles' => ['Staff', 'Lecturer', 'Clinical Instructor'],

    // Equipment (research) lab bookings are restricted to these applicant roles
    // (spans both the student and staff domains, unlike pharma_allowed_roles).
    'research_allowed_roles' => ['Postgraduate', 'Lecturer'],

    // Max number of additional pax (beyond the applicant) that can be listed
    // for equipment/CSL "more than you" bookings.
    'pax_max' => 30,

    // Research & Development labs: 08:00-17:00, any day of the week. Weekends
    // are open per room, not globally — a room with labs.weekends_allowed = 0
    // stays weekday-only (see Lab::$fillable). A handful of individual
    // equipment items (see lab_equipment.special_conditions_note) still carry
    // their own Mon-Thu-only / narrower-window restrictions.
    'research' => [
        'weekday_start' => '08:00',
        'weekday_end' => '17:00',
        'pax_min' => 1,
        'pax_max' => 30,
    ],

    // Pharma labs: weekday evenings after regular classes, or daytime on weekends.
    'pharma' => [
        'weekday_start' => '17:00',
        'weekday_end' => '21:00',
        'weekend_start' => '08:00',
        'weekend_end' => '17:00',
    ],

    // CSL labs: general 08:00-17:00 window, must be booked in advance with a buffer
    // between sessions in the same room.
    'csl' => [
        'day_start' => '08:00',
        'day_end' => '17:00',
        'weekdays_only' => true,
        'advance_working_days' => 1,
        'buffer_minutes' => 30,
    ],

    'csl_session_types' => [
        'Teaching session', 'Practice session', 'Revision', 'OSCE / Assessment', 'Simulation / Emergency drill',
    ],

    'csl_disciplines' => array_keys($cslDisciplineRooms),

    'csl_discipline_rooms' => $cslDisciplineRooms,

    // Package-based disciplines: the whole set of rooms listed above is booked
    // together, so the applicant never picks rooms individually — selecting the
    // discipline reserves every one of its rooms. Every other discipline lets
    // the applicant pick any subset of its rooms.
    'csl_package_disciplines' => ['Surgical', 'BCC Surgery', 'BCC Medicine', 'IPE'],
];
