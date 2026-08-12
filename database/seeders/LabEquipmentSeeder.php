<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $equipment = require __DIR__.'/data/lab_equipment.php';

        foreach ($equipment as $row) {
            DB::table('lab_equipment')->updateOrInsert(['id' => $row['id']], $row);
        }
    }
}
