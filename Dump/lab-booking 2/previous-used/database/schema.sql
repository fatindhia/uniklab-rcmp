-- ============================================================
-- UniKLAB RCMP Laboratory Booking System — Database Schema
-- Run against: lab_booking (utf8mb4_unicode_ci)
-- ============================================================

CREATE DATABASE IF NOT EXISTS lab_booking
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE lab_booking;

-- ── 1. roles ─────────────────────────────────────────────────────────────────
CREATE TABLE roles (
    id    TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name  VARCHAR(50)      NOT NULL,
    label VARCHAR(100)     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. users ─────────────────────────────────────────────────────────────────
CREATE TABLE users (
    staff_id      CHAR(6)          NOT NULL COMMENT '6-digit Staff ID used as login username',
    role_id       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    full_name     VARCHAR(150)     NOT NULL,
    department    VARCHAR(150)     NOT NULL DEFAULT '',
    email         VARCHAR(150)     NOT NULL,
    phone_number  VARCHAR(30)      NOT NULL DEFAULT '',
    password_hash VARCHAR(255)     NOT NULL,
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (staff_id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role_id (role_id),
    KEY idx_users_is_active (is_active),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. labs ──────────────────────────────────────────────────────────────────
CREATE TABLE labs (
    id                          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name                        VARCHAR(150)      NOT NULL,
    lab_type                    ENUM('research','csl','pharma') NOT NULL,
    lab_block                   ENUM('az-research','av-research','csl','pharma') NOT NULL,
    room_code                   VARCHAR(30)       NOT NULL DEFAULT '-',
    location                    VARCHAR(200)      NOT NULL DEFAULT '',
    capacity                    SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = not pax-limited',
    status                      ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active',
    is_iso_certified            TINYINT(1)        NOT NULL DEFAULT 0,
    is_room_only                TINYINT(1)        NOT NULL DEFAULT 0,
    requires_special_conditions TINYINT(1)        NOT NULL DEFAULT 0,
    notes                       TEXT,
    created_at                  TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_labs_lab_type  (lab_type),
    KEY idx_labs_lab_block (lab_block),
    KEY idx_labs_status    (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. lab_equipment ─────────────────────────────────────────────────────────
CREATE TABLE lab_equipment (
    id                      INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    lab_id                  SMALLINT UNSIGNED NOT NULL,
    equipment_name          VARCHAR(300)      NOT NULL,
    special_conditions_note VARCHAR(300)      NOT NULL DEFAULT '',
    sort_order              TINYINT UNSIGNED  NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_lab_equipment_lab_id (lab_id),
    CONSTRAINT fk_lab_equipment_lab FOREIGN KEY (lab_id) REFERENCES labs (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. bookings ──────────────────────────────────────────────────────────────
CREATE TABLE bookings (
    id                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ref                    VARCHAR(20)  NOT NULL,
    user_staff_id          CHAR(6)      NULL COMMENT 'NULL for unauthenticated submissions',

    -- Applicant snapshot (captured at submit time, not normalised to users)
    applicant_name         VARCHAR(150) NOT NULL,
    applicant_id           VARCHAR(30)  NOT NULL COMMENT 'Staff ID or Student ID',
    applicant_email        VARCHAR(150) NOT NULL,
    applicant_phone        VARCHAR(30)  NOT NULL DEFAULT '',
    applicant_department   VARCHAR(150) NOT NULL DEFAULT '',
    applicant_role         VARCHAR(50)  NOT NULL COMMENT 'Staff / Lecturer / Clinical Instructor / Year 1-4 etc.',

    -- Lab & time
    lab_type               ENUM('research','csl','pharma') NOT NULL,
    lab_block              ENUM('az-research','av-research','csl','pharma') NOT NULL,
    booking_date_from      DATE         NOT NULL,
    booking_date_to        DATE         NOT NULL COMMENT 'Same as date_from for single-day bookings',
    start_time             TIME         NOT NULL,
    end_time               TIME         NOT NULL,

    -- Research-specific
    research_pax           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    purpose_of_use         TEXT,
    has_special_conditions TINYINT(1)   NOT NULL DEFAULT 0,

    -- CSL-specific
    csl_session_type       VARCHAR(60)  NOT NULL DEFAULT '',
    csl_discipline         VARCHAR(60)  NOT NULL DEFAULT '',
    csl_num_students       SMALLINT UNSIGNED NOT NULL DEFAULT 0,

    -- Pharma-specific
    pharma_primary_lab     ENUM('CL','MDLP','PL1','PL2') NULL,
    pharma_group_number    VARCHAR(30)  NOT NULL DEFAULT '',
    pharma_num_students    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    pharma_tc_accepted     TINYINT(1)   NOT NULL DEFAULT 0,

    -- General
    purpose                TEXT         NOT NULL,
    applicant_remark       TEXT,

    -- Admin
    status                 ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
    admin_remark           TEXT,
    processed_by           CHAR(6)      NULL,
    processed_at           TIMESTAMP    NULL,
    submitted_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_bookings_ref (ref),
    KEY idx_bookings_status          (status),
    KEY idx_bookings_lab_type        (lab_type),
    KEY idx_bookings_lab_block       (lab_block),
    KEY idx_bookings_date_from       (booking_date_from),
    KEY idx_bookings_date_range      (booking_date_from, booking_date_to),
    KEY idx_bookings_applicant_email (applicant_email),
    KEY idx_bookings_applicant_id    (applicant_id),
    KEY idx_bookings_user_staff_id   (user_staff_id),
    KEY idx_bookings_processed_by    (processed_by),
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_staff_id) REFERENCES users (staff_id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_bookings_processed_by FOREIGN KEY (processed_by) REFERENCES users (staff_id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. booking_rooms ─────────────────────────────────────────────────────────
-- One booking can span multiple rooms (CSL multi-room, research multi-equipment-room)
CREATE TABLE booking_rooms (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    booking_id INT UNSIGNED      NOT NULL,
    lab_id     SMALLINT UNSIGNED NOT NULL,
    is_primary TINYINT(1)        NOT NULL DEFAULT 1 COMMENT 'Pharma: 0 = alt-lab equipment source',
    PRIMARY KEY (id),
    UNIQUE KEY uq_booking_rooms (booking_id, lab_id),
    KEY idx_booking_rooms_lab_id (lab_id),
    CONSTRAINT fk_booking_rooms_booking FOREIGN KEY (booking_id) REFERENCES bookings (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_booking_rooms_lab FOREIGN KEY (lab_id) REFERENCES labs (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 7. booking_equipment ─────────────────────────────────────────────────────
CREATE TABLE booking_equipment (
    id             INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    booking_id     INT UNSIGNED      NOT NULL,
    lab_id         SMALLINT UNSIGNED NOT NULL,
    equipment_name VARCHAR(300)      NOT NULL,
    is_alt_lab     TINYINT(1)        NOT NULL DEFAULT 0 COMMENT 'Pharma overflow from alt lab',
    PRIMARY KEY (id),
    KEY idx_booking_equipment_booking_id (booking_id),
    KEY idx_booking_equipment_lab_id     (lab_id),
    CONSTRAINT fk_booking_equipment_booking FOREIGN KEY (booking_id) REFERENCES bookings (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_booking_equipment_lab FOREIGN KEY (lab_id) REFERENCES labs (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 8. booking_students ──────────────────────────────────────────────────────
CREATE TABLE booking_students (
    id           INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    booking_id   INT UNSIGNED      NOT NULL,
    student_name VARCHAR(150)      NOT NULL,
    student_id   VARCHAR(30)       NOT NULL,
    student_year TINYINT UNSIGNED  NULL COMMENT 'CSL only: year group 1-4; NULL for pharma/research',
    sort_order   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_booking_students_booking_id (booking_id),
    KEY idx_booking_students_student_id (student_id),
    CONSTRAINT fk_booking_students_booking FOREIGN KEY (booking_id) REFERENCES bookings (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 9. time_blocks ───────────────────────────────────────────────────────────
-- Admin-managed blocked time slots (schedule-block feature)
CREATE TABLE time_blocks (
    id          VARCHAR(20)  NOT NULL,
    lab_type    ENUM('research','csl','pharma') NOT NULL,
    category    ENUM('class','practical','maintenance','reserved','exam','event') NOT NULL,
    title       VARCHAR(200) NOT NULL,
    pic         VARCHAR(150) NOT NULL DEFAULT '' COMMENT 'Person In Charge',
    block_date  DATE         NOT NULL,
    start_time  TIME         NOT NULL,
    end_time    TIME         NOT NULL,
    rooms       JSON         NOT NULL COMMENT 'Array of room name strings',
    recurring   ENUM('none','weekly','biweekly') NOT NULL DEFAULT 'none',
    notes       TEXT,
    created_by  CHAR(6)      NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_time_blocks_lab_type   (lab_type),
    KEY idx_time_blocks_block_date (block_date),
    KEY idx_time_blocks_created_by (created_by),
    CONSTRAINT fk_time_blocks_created_by FOREIGN KEY (created_by) REFERENCES users (staff_id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
