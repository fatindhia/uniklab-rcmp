<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distinguishes the two meanings a multi-day booking can have. Until now
     * booking_date_from/to + start_time/end_time only ever meant "this same
     * clock window, repeated on every day in the range" — right for a CSL class
     * held 09:00-11:00 on Monday and Tuesday, wrong for a tissue processor that
     * has to run non-stop from Wednesday noon to Thursday noon. The second kind
     * was silently stored as the first, so a 25-hour run reserved one hour.
     *
     * With this flag set, the same four columns mean a single continuous span:
     * booking_date_from + start_time through booking_date_to + end_time.
     *
     * FALSE is the right default for every existing row — they were all entered
     * (and validated) under the repeated-window reading.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_continuous')->default(false)->after('end_time')
                ->comment('Research only: one continuous run from date_from+start_time to date_to+end_time');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('is_continuous');
        });
    }
};
