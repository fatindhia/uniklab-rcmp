<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabSeeder extends Seeder
{
    public function run(): void
    {
        $labs = require __DIR__.'/data/labs.php';

        foreach ($labs as $lab) {
            DB::table('labs')->updateOrInsert(['id' => $lab['id']], $lab);
        }
    }
}
