<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flags a decision (approve / reject / cancel) that only landed after the
     * booking date had already passed while the request sat pending — the
     * audit trail renders those in red as a "Late response".
     */
    public function up(): void
    {
        Schema::table('booking_audit_logs', function (Blueprint $table) {
            $table->boolean('is_late')->default(false)->after('detail');
        });

        // Backfill: the same rule applied to rows already on record — a
        // decision logged on a later calendar day than the booking's last day.
        DB::table('booking_audit_logs')
            ->join('bookings', 'bookings.id', '=', 'booking_audit_logs.booking_id')
            ->whereIn('booking_audit_logs.action', ['approved', 'rejected', 'cancelled'])
            ->whereRaw('DATE(booking_audit_logs.created_at) > COALESCE(bookings.booking_date_to, bookings.booking_date_from)')
            ->update(['booking_audit_logs.is_late' => true]);
    }

    public function down(): void
    {
        Schema::table('booking_audit_logs', function (Blueprint $table) {
            $table->dropColumn('is_late');
        });
    }
};
