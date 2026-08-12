<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Research & Development labs now open on weekends, except for a handful of
 * rooms that stay weekday-only — so "no weekend bookings" moves from a global
 * config rule onto the room itself (labs.weekends_allowed), where the admin
 * can toggle it per room.
 *
 * Also adds the Thermal Analysis Room (Avicenna Level 3) with its equipment,
 * and the flow cytometer that joined the Instrumentation Room. Both are
 * mirrored in database/seeders/data/{labs,lab_equipment}.php for fresh installs;
 * the inserts here are what brings an already-seeded database up to date.
 */
return new class extends Migration
{
    /** Rooms that stay weekday-only. */
    private const WEEKDAY_ONLY_LAB_IDS = [
        9,  // Instrumentation Room (Al-Zahrawi)
        10, // MDL 3 (2A-31)
        11, // Lab Level 2 (Avicenna)
        32, // Thermal Analysis Room (Avicenna Level 3)
    ];

    private const THERMAL_ANALYSIS_LAB_ID = 32;

    private const NEW_EQUIPMENT = [
        // Instrumentation Room
        ['id' => 119, 'lab_id' => 9, 'equipment_name' => 'Flow Cytometry System (ACEA NOVOCYTE ADVANTEON B5R3), 2 lasers (Blue, Red)', 'special_conditions_note' => '', 'sort_order' => 3],
        // Thermal Analysis Room
        ['id' => 120, 'lab_id' => 32, 'equipment_name' => 'Atomic Absorption Spectrometer (AA7800F/AAC)', 'special_conditions_note' => '', 'sort_order' => 1],
        ['id' => 121, 'lab_id' => 32, 'equipment_name' => 'Differential Scanning Calorimetry (DSC 300 CALARIS CLASSIC)', 'special_conditions_note' => '', 'sort_order' => 2],
        ['id' => 122, 'lab_id' => 32, 'equipment_name' => 'Gas Chromatography Mass Spectrometry (GCMS-QP2020NX with AOC-20i Plus +20sU)', 'special_conditions_note' => '', 'sort_order' => 3],
        ['id' => 123, 'lab_id' => 32, 'equipment_name' => 'Microwave Digestion System (Multiwave GO PLUS)', 'special_conditions_note' => '', 'sort_order' => 4],
        ['id' => 124, 'lab_id' => 32, 'equipment_name' => 'Fume Hood', 'special_conditions_note' => '', 'sort_order' => 5],
    ];

    public function up(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->boolean('weekends_allowed')->default(true)->after('requires_special_conditions');
        });

        DB::table('labs')->updateOrInsert(['id' => self::THERMAL_ANALYSIS_LAB_ID], [
            'name' => 'Thermal Analysis Room',
            'lab_type' => 'research',
            'location' => 'Avicenna, Level 3',
            'capacity' => 0,
            'status' => 'active',
            'is_room_only' => 0,
            'requires_special_conditions' => 0,
            'notes' => null,
            'created_at' => now(),
        ]);

        DB::table('labs')->whereIn('id', self::WEEKDAY_ONLY_LAB_IDS)->update(['weekends_allowed' => false]);

        foreach (self::NEW_EQUIPMENT as $equipment) {
            DB::table('lab_equipment')->updateOrInsert(['id' => $equipment['id']], $equipment);
        }
    }

    public function down(): void
    {
        DB::table('lab_equipment')->whereIn('id', array_column(self::NEW_EQUIPMENT, 'id'))->delete();
        DB::table('labs')->where('id', self::THERMAL_ANALYSIS_LAB_ID)->delete();

        Schema::table('labs', function (Blueprint $table) {
            $table->dropColumn('weekends_allowed');
        });
    }
};
