-- ============================================================
-- UniKLAB RCMP Laboratory Booking System — Seed Data
-- Run AFTER schema.sql
-- Default admin username: 620798
-- Default admin password: Admin@1234  (bcrypt, change before go-live)
-- ============================================================

USE lab_booking;

-- ── Roles ─────────────────────────────────────────────────────────────────────
INSERT INTO roles (name, label) VALUES
    ('lab_staff', 'Lab Staff'),
    ('admin',     'Admin');

-- ── Admin user ────────────────────────────────────────────────────────────────
-- Default username: 620798
-- Default password: Admin@1234
-- To regenerate: php -r "echo password_hash('YourNewPassword', PASSWORD_BCRYPT);"
INSERT INTO users (staff_id, role_id, full_name, department, email, phone_number, password_hash) VALUES
    ('620798', 2, 'System Administrator', 'RCMP Laboratory Management',
     'fatindhiya07@gmail.com', '',
     '$2y$10$7pIkk.uYXgsRj./GkAk6TuB4dsEQ4AVQEyQu.Od3IL25XRDOleXoy');

-- ============================================================
-- LABS
-- ============================================================

-- ── Al Zahrawi Research Labs (az-research) ───────────────────────────────────
INSERT INTO labs (name, lab_type, lab_block, room_code, location, capacity,
                  is_iso_certified, is_room_only, requires_special_conditions, notes)
VALUES
    ('Plant Extraction Room',  'research', 'az-research', 'A2052',       'Al Zahrawi Block A, Level 2', 0, 0, 0, 0, NULL),
    ('Molecular Room',         'research', 'az-research', 'A2051',       'Al Zahrawi Block A, Level 2', 0, 1, 0, 0, 'ISO 17025 certified'),
    ('Media Preparation Room', 'research', 'az-research', 'A2055',       'Al Zahrawi Block A, Level 2', 0, 0, 0, 0, NULL),
    ('Assay Room',             'research', 'az-research', 'A2054',       'Al Zahrawi Block A, Level 2', 0, 1, 0, 0, 'ISO certified'),
    ('Microbiology Room',      'research', 'az-research', 'A2012-A2013', 'Al Zahrawi Block A, Level 2', 0, 1, 0, 0, 'ISO certified'),
    ('Cell Culture Room 1',    'research', 'az-research', '-',           'Al Zahrawi Block A',          0, 0, 1, 0, 'Room booking only — no equipment selection'),
    ('Cell Culture Room 2',    'research', 'az-research', '-',           'Al Zahrawi Block A',          0, 0, 1, 0, 'Room booking only — no equipment selection'),
    ('Cell Culture Room 3',    'research', 'az-research', '-',           'Al Zahrawi Block A',          0, 0, 1, 0, 'Room booking only — no equipment selection'),
    ('Instrumentation Room',   'research', 'az-research', '-',           'Al Zahrawi Block A',          0, 0, 0, 1, 'Equipment subject to special booking conditions');

-- ── Avicenna Research Labs (av-research) ─────────────────────────────────────
INSERT INTO labs (name, lab_type, lab_block, room_code, location, capacity,
                  is_iso_certified, is_room_only, requires_special_conditions, notes)
VALUES
    ('MDL 3',      'research', 'av-research', '2A-31', 'Avicenna, Level 2A', 0, 0, 0, 0, NULL),
    ('Lab Level 2','research', 'av-research', '-',     'Avicenna, Level 2',  0, 0, 0, 1, 'Equipment subject to special booking conditions');

-- ── CSL 1 (csl) ──────────────────────────────────────────────────────────────
INSERT INTO labs (name, lab_type, lab_block, room_code, location, capacity,
                  is_iso_certified, is_room_only, requires_special_conditions, notes)
VALUES
    ('Physiko Room',    'csl', 'csl', '-', 'Avicenna, CSL 1', 0, 0, 0, 0, NULL),
    ('Mock Ward',       'csl', 'csl', '-', 'Avicenna, CSL 1', 0, 0, 0, 0, NULL),
    ('Simulation Room', 'csl', 'csl', '-', 'Avicenna, CSL 1', 0, 0, 0, 0, NULL);

-- ── CSL 2 (csl) ──────────────────────────────────────────────────────────────
INSERT INTO labs (name, lab_type, lab_block, room_code, location, capacity,
                  is_iso_certified, is_room_only, requires_special_conditions, notes)
VALUES
    ('Room 1',          'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Venepuncture, IV Line Setting, IM Injection'),
    ('Room 2',          'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Arterial Blood Sampling'),
    ('Room 3',          'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Wound Dressing'),
    ('Room 4',          'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Chest Drainage'),
    ('Room 5',          'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Multipurpose Room'),
    ('Room 6',          'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Multipurpose Room'),
    ('Room 7',          'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Multipurpose Room'),
    ('Room 8',          'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Multipurpose Room'),
    ('Room 9',          'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Breast Examination'),
    ('Room 10',         'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'NG Tube Insertion, PR Examination'),
    ('Room 11',         'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Suturing'),
    ('Room 12',         'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, 'Catheterization'),
    ('Discussion Room', 'csl', 'csl', '-', 'Avicenna, CSL 2', 0, 0, 0, 0, NULL);

-- ── Pharma Labs ───────────────────────────────────────────────────────────────
INSERT INTO labs (name, lab_type, lab_block, room_code, location, capacity,
                  is_iso_certified, is_room_only, requires_special_conditions, notes)
VALUES
    ('Chemistry Lab (CL)',                          'pharma', 'pharma', 'PH-CL',   'Avicenna, Level 1', 20, 0, 0, 0, NULL),
    ('Multidisciplinary Pharmaceutical Lab (MDLP)', 'pharma', 'pharma', 'PH-MDLP', 'Avicenna, Level 1', 40, 0, 0, 0, NULL),
    ('Pharmaceutical Lab 1 (PL1)',                  'pharma', 'pharma', 'PH-PL1',  'Avicenna, Level 1', 40, 0, 0, 0, NULL),
    ('Pharmaceutical Lab 2 (PL2)',                  'pharma', 'pharma', 'PH-PL2',  'Avicenna, Level 1', 40, 0, 0, 0, NULL);

-- ============================================================
-- EQUIPMENT
-- All items verbatim from booking.php PHP arrays
-- ============================================================

-- ── Plant Extraction Room ─────────────────────────────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'Explosion Proof Oven, KEDA Machinery (with vacuum pump, Wiggens)' name, 1 ord UNION ALL
    SELECT 'Fume Hood, Chemoresources',  2 UNION ALL
    SELECT 'Rotary Evaporator, Heidolph', 3 UNION ALL
    SELECT 'Universal Oven, UF110, Memmert', 4
) e WHERE labs.name = 'Plant Extraction Room';

-- ── Molecular Room ────────────────────────────────────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'Biophotometer, Denovix' name, 1 ord UNION ALL
    SELECT 'Gel Documentation System, Major Science', 2 UNION ALL
    SELECT 'Laminar Flow, ESCO', 3 UNION ALL
    SELECT 'Microcentrifuge, 5424, Eppendorf', 4 UNION ALL
    SELECT 'Mini Centrifuge, MiniSpin Plus, Eppendorf', 5 UNION ALL
    SELECT 'Portable Mini qPCR Instrument, MyGo Mini', 6 UNION ALL
    SELECT 'Real Time PCR System, CFX96, BioRad', 7 UNION ALL
    SELECT 'Sonicator, Q500, Q-Sonica', 8 UNION ALL
    SELECT 'Thermalcycler, Gradient, Eppendorf', 9 UNION ALL
    SELECT 'Thermomixer with Thermoblock, Eppendorf', 10
) e WHERE labs.name = 'Molecular Room';

-- ── Media Preparation Room ────────────────────────────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'Microwave' name, 1 ord UNION ALL
    SELECT 'Universal Oven, UF110, Memmert', 2
) e WHERE labs.name = 'Media Preparation Room';

-- ── Assay Room ────────────────────────────────────────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'Cellulose Acetate Electrophoresis Sets, Cleaver Scientific' name, 1 ord UNION ALL
    SELECT 'Chemidoc Imaging System, Biorad', 2 UNION ALL
    SELECT 'Multimode Plate Reader, Vantastar, BMG Labtech', 3 UNION ALL
    SELECT 'Transfer System, Trans Blot Turbo, Biorad', 4 UNION ALL
    SELECT 'UV-Vis Spectrophotometer, Biochrom', 5
) e WHERE labs.name = 'Assay Room';

-- ── Microbiology Room ─────────────────────────────────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'Biosafety Cabinet, Mars 1200, Labogene' name, 1 ord UNION ALL
    SELECT 'Automatic Colony Counter & Inhibition Zone Reader, Scan1200, Interscience', 2 UNION ALL
    SELECT 'Incubator 1, IN55, Memmert', 3 UNION ALL
    SELECT 'Incubator 2, IN55, Memmert', 4 UNION ALL
    SELECT 'Incubator Shaker, Innova 42R, Eppendorf', 5 UNION ALL
    SELECT 'Incubator Shaker Top Level, Yihder', 6 UNION ALL
    SELECT 'Incubator Shaker Bottom Level, Yihder', 7 UNION ALL
    SELECT 'Microscope, BX43F, Olympus (with camera)', 8 UNION ALL
    SELECT 'Portable Mini Incubator, Benchmark', 9 UNION ALL
    SELECT 'Waterbath, WNB22, Memmert (with shaking device)', 10
) e WHERE labs.name = 'Microbiology Room';

-- ── Instrumentation Room (special conditions per equipment) ───────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, special_conditions_note, sort_order)
SELECT id, e.name, e.note, e.ord FROM labs, (
    SELECT 'Fourier Transform Infrared Spectrophotometer (FTIR)'  name,
           'Weekdays only * 08:30-16:30 * Min. 60 min'           note, 1 ord UNION ALL
    SELECT 'Freeze Dryer, Buchi (FD)',
           'Mon-Thu only * 10:00-16:00 * No Friday / Weekend', 2
) e WHERE labs.name = 'Instrumentation Room';

-- ── MDL 3 ─────────────────────────────────────────────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'Fluorescence Microscope, BX53FL, Olympus (with digital camera)' name, 1 ord UNION ALL
    SELECT 'High Performance Liquid Chromatographer, Waters Alliance', 2 UNION ALL
    SELECT 'Microplate Reader, Tecan', 3 UNION ALL
    SELECT 'Particle Size Analyzer, Anton Paar', 4
) e WHERE labs.name = 'MDL 3';

-- ── Lab Level 2 (special conditions per equipment) ────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, special_conditions_note, sort_order)
SELECT id, e.name, e.note, e.ord FROM labs, (
    SELECT 'Freeze Dryer, Labogene (FD)'                                      name,
           'Mon-Thu only * 10:00-16:00 * No Friday / Weekend'                 note, 1 ord UNION ALL
    SELECT 'Tissue Processor',
           'Mon-Thu only * Full-day booking * 1-day buffer required before next booking', 2
) e WHERE labs.name = 'Lab Level 2';

-- ── Chemistry Lab (CL) ────────────────────────────────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'UV-Vis Spectrophotometer' name, 1 ord UNION ALL
    SELECT 'Rotary Evaporator (IKA)',  2 UNION ALL
    SELECT 'Rotary Evaporator (BUCHI)', 3 UNION ALL
    SELECT 'Melting Point (BUCHI)', 4 UNION ALL
    SELECT 'Melting Point (Stuart)', 5 UNION ALL
    SELECT 'pH Meter', 6 UNION ALL
    SELECT 'Ultrasonicator', 7 UNION ALL
    SELECT 'Centrifuge', 8 UNION ALL
    SELECT 'Micro Centrifuge', 9 UNION ALL
    SELECT 'Oven', 10 UNION ALL
    SELECT 'Fume Hood', 11 UNION ALL
    SELECT 'Water Bath', 12
) e WHERE labs.name = 'Chemistry Lab (CL)';

-- ── Multidisciplinary Pharmaceutical Lab (MDLP) ───────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'UV-Vis Spectrophotometer' name, 1 ord UNION ALL
    SELECT 'Rotary Evaporator (BUCHI)', 2 UNION ALL
    SELECT 'pH Meter', 3 UNION ALL
    SELECT 'Ultrasonicator', 4 UNION ALL
    SELECT 'Calorimeter', 5 UNION ALL
    SELECT 'Incubator', 6 UNION ALL
    SELECT 'Fume Hood', 7 UNION ALL
    SELECT 'Laminar Flow', 8 UNION ALL
    SELECT 'Water Bath', 9
) e WHERE labs.name = 'Multidisciplinary Pharmaceutical Lab (MDLP)';

-- ── Pharmaceutical Lab 1 (PL1) ────────────────────────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'UV-Vis Spectrophotometer' name, 1 ord UNION ALL
    SELECT 'Rotary Evaporator (IKA)', 2 UNION ALL
    SELECT 'pH Meter', 3 UNION ALL
    SELECT 'Ultrasonicator', 4 UNION ALL
    SELECT 'Micro Centrifuge', 5 UNION ALL
    SELECT 'Oven', 6 UNION ALL
    SELECT 'Fume Hood', 7 UNION ALL
    SELECT 'Water Bath', 8 UNION ALL
    SELECT 'Sonicator Qsonica', 9 UNION ALL
    SELECT 'Sieve Shaker', 10 UNION ALL
    SELECT 'Digital Overheat Stirrer', 11 UNION ALL
    SELECT 'Dissolution Apparatus', 12 UNION ALL
    SELECT 'Disintegration Apparatus', 13 UNION ALL
    SELECT 'Franz Cell', 14
) e WHERE labs.name = 'Pharmaceutical Lab 1 (PL1)';

-- ── Pharmaceutical Lab 2 (PL2) ────────────────────────────────────────────────
INSERT INTO lab_equipment (lab_id, equipment_name, sort_order)
SELECT id, e.name, e.ord FROM labs, (
    SELECT 'UV-Vis Spectrophotometer' name, 1 ord UNION ALL
    SELECT 'Rotary Evaporator (IKA)', 2 UNION ALL
    SELECT 'pH Meter', 3 UNION ALL
    SELECT 'Ultrasonicator', 4 UNION ALL
    SELECT 'Biosafety Cabinet', 5 UNION ALL
    SELECT 'Oven', 6 UNION ALL
    SELECT 'Fume Hood', 7 UNION ALL
    SELECT 'Water Bath', 8 UNION ALL
    SELECT 'Digital Overheat Stirrer', 9 UNION ALL
    SELECT 'Rheometer', 10 UNION ALL
    SELECT 'Sonicator Qsonica', 11 UNION ALL
    SELECT 'Dissolution Apparatus', 12 UNION ALL
    SELECT 'Disintegration Apparatus', 13
) e WHERE labs.name = 'Pharmaceutical Lab 2 (PL2)';
