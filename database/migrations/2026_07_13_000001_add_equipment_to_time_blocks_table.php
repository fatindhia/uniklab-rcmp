<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            $table->json('equipment')->nullable()->after('rooms')
                ->comment('Array of "Room::Equipment" name strings reserved by this block');
        });
    }

    public function down(): void
    {
        Schema::table('time_blocks', function (Blueprint $table) {
            $table->dropColumn('equipment');
        });
    }
};
