<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Until now every active panel account received the new-booking ticket for
     * every booking, but staff each look after one lab area — a CSL person had
     * no use for Research and Pharma tickets. This mirrors the lab_type enum
     * already on labs/bookings/time_blocks onto the staff account itself so a
     * ticket can be routed to the people who actually handle that area.
     *
     * Nullable, and NULL means "receives nothing" rather than "receives
     * everything" — a forgotten assignment should be quiet, not noisy. There
     * is deliberately no backfill: existing lab staff go silent until someone
     * assigns them a lab type in Manage Lab Staff. Admins are unaffected;
     * they oversee every lab and keep receiving all tickets.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('lab_type', ['research', 'csl', 'pharma'])->nullable()->after('role_id');

            $table->index('lab_type', 'idx_users_lab_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_lab_type');
            $table->dropColumn('lab_type');
        });
    }
};
