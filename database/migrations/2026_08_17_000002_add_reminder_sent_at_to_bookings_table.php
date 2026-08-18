<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marks when the "still pending 24 hours before the slot" reminder went out
     * to the lab staff, so the hourly bookings:remind-pending run can tell an
     * unreminded booking from one it already chased. Without it, every run
     * inside the 24-hour window would re-send the same reminder.
     *
     * NULL means "not reminded yet", which is the right default for the
     * existing rows: any of them still pending inside the window gets one
     * reminder on the first run, and nothing after that.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_at');
        });
    }
};
