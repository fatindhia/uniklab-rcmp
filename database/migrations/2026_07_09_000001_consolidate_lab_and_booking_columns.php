<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * lab_block/room_code/is_iso_certified on labs, and lab_block/purpose_of_use/
     * pharma_group_number on bookings, are either redundant with another column
     * (location already identifies the building; purpose already holds the
     * value purpose_of_use only ever duplicated) or dead (is_iso_certified and
     * pharma_group_number are never set through any form). Before dropping
     * room_code, fold its value into the lab name so it isn't lost, and
     * normalise az-research location text so a "building" can be reliably
     * derived from location's leading segment once lab_block is gone.
     */
    public function up(): void
    {
        DB::table('labs')
            ->where('location', 'like', 'Al Zahrawi Block A%')
            ->update([
                'location' => DB::raw("REPLACE(location, 'Al Zahrawi Block A', 'Al-Zahrawi, Block A')"),
            ]);

        DB::table('labs')
            ->where('room_code', '!=', '-')
            ->where('lab_type', '!=', 'pharma')
            ->orderBy('id')
            ->get(['id', 'name', 'room_code'])
            ->each(function ($lab) {
                DB::table('labs')
                    ->where('id', $lab->id)
                    ->update(['name' => "{$lab->name} ({$lab->room_code})"]);
            });

        Schema::table('labs', function (Blueprint $table) {
            $table->dropIndex('idx_labs_lab_block');
            $table->dropColumn(['lab_block', 'room_code', 'is_iso_certified']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_lab_block');
            $table->dropColumn(['lab_block', 'purpose_of_use', 'pharma_group_number']);
        });
    }

    /**
     * Best-effort only: re-adds the columns so the app still boots, but the
     * original room_code/lab_block/pre-merge name text cannot be restored.
     */
    public function down(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->enum('lab_block', ['az-research', 'av-research', 'csl', 'pharma'])->nullable()->after('lab_type');
            $table->string('room_code', 30)->default('-')->after('lab_block');
            $table->boolean('is_iso_certified')->default(false)->after('capacity');
            $table->index('lab_block', 'idx_labs_lab_block');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('lab_block', ['az-research', 'av-research', 'csl', 'pharma'])->nullable()->after('lab_type');
            $table->text('purpose_of_use')->nullable()->after('research_pax');
            $table->string('pharma_group_number', 30)->default('')->after('pharma_primary_lab');
            $table->index('lab_block', 'idx_bookings_lab_block');
        });
    }
};
