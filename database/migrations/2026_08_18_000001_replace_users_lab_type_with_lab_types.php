<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users.lab_type assumed each lab staff account looks after exactly one
     * area, which turned out not to hold — some staff cover two (Aminah
     * handles both Pharma and CSL), and a single enum forced whoever set the
     * account up to pick one and leave the other area's tickets unrouted.
     *
     * The column becomes a JSON array of lab types, mirroring how
     * time_blocks.rooms already stores a list. Existing assignments carry over
     * as a one-element array, and NULL keeps its old meaning: "receives
     * nothing", so a forgotten assignment stays quiet rather than noisy.
     *
     * The idx_users_lab_type index goes with the column — MySQL can't index a
     * JSON column directly, and the recipient lookup runs over a staff table
     * of a few dozen rows, so the scan is not worth a generated column.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('lab_types')->nullable()->after('role_id')
                ->comment('Array of lab types this account receives booking tickets for');
        });

        DB::table('users')
            ->whereNotNull('lab_type')
            ->update(['lab_types' => DB::raw('JSON_ARRAY(lab_type)')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_lab_type');
            $table->dropColumn('lab_type');
        });
    }

    /**
     * Rolling back narrows anyone with several areas down to their first one —
     * the old column simply has nowhere to put the rest.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('lab_type', ['research', 'csl', 'pharma'])->nullable()->after('role_id');
        });

        DB::table('users')
            ->whereNotNull('lab_types')
            ->update(['lab_type' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(lab_types, '$[0]'))")]);

        Schema::table('users', function (Blueprint $table) {
            $table->index('lab_type', 'idx_users_lab_type');
            $table->dropColumn('lab_types');
        });
    }
};
