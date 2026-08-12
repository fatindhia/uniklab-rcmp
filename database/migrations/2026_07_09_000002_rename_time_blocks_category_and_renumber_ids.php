<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `category` never held anything but the block's purpose (class/practical/
     * maintenance/reserved/exam/event) — renamed for clarity, same values.
     * The id was already a string PK, just a needlessly long random suffix
     * (TB-XXXXXXXX); renumbered sequentially (TB-01, TB-02, ...) since nothing
     * else in the schema has a foreign key to time_blocks.id.
     */
    public function up(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            $table->renameColumn('category', 'purpose');
        });

        DB::table('time_blocks')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id')
            ->each(function ($oldId, $index) {
                DB::table('time_blocks')
                    ->where('id', $oldId)
                    ->update(['id' => 'TB-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)]);
            });
    }

    public function down(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            $table->renameColumn('purpose', 'category');
        });

        // Renumbered ids are not reversed — nothing references time_blocks.id
        // from elsewhere, so this is a cosmetic, non-breaking one-way change.
    }
};
