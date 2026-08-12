<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Purely additive indexes for query patterns that were doing a full scan +
 * filesort under load:
 *  - bookings(status, lab_type): the grouped stats queries (Dashboard,
 *    Admin\BookingController::stats, ReportController) and every per-type
 *    "pending" filter now hit this composite instead of the single-column
 *    status/lab_type indexes separately.
 *  - bookings(status, submitted_at): dashboard's "pending, oldest first"
 *    queue query orders by submitted_at while filtering on status.
 *  - booking_audit_logs(created_at) / time_blocks(created_at): both are
 *    read with `latest('created_at')` (Report's recent activity feed,
 *    History's full timeline) with no index to satisfy the sort.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['status', 'lab_type'], 'idx_bookings_status_lab_type');
            $table->index(['status', 'submitted_at'], 'idx_bookings_status_submitted_at');
        });

        Schema::table('booking_audit_logs', function (Blueprint $table) {
            $table->index('created_at', 'idx_booking_audit_logs_created_at');
        });

        Schema::table('time_blocks', function (Blueprint $table) {
            $table->index('created_at', 'idx_time_blocks_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_status_lab_type');
            $table->dropIndex('idx_bookings_status_submitted_at');
        });

        Schema::table('booking_audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_booking_audit_logs_created_at');
        });

        Schema::table('time_blocks', function (Blueprint $table) {
            $table->dropIndex('idx_time_blocks_created_at');
        });
    }
};
