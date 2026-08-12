<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A single "block this out" request can now cover several rooms, each with its
 * own date and time — so it's saved as one TimeBlock per room (each room keeps
 * its own schedule and can be removed on its own) tied together by group_id,
 * which is the id of the first block created in the request.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            $table->string('group_id', 20)->nullable()->after('id')
                ->comment('Blocks created together in one multi-room request share this');
            $table->index('group_id', 'idx_time_blocks_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            $table->dropIndex('idx_time_blocks_group_id');
            $table->dropColumn('group_id');
        });
    }
};
