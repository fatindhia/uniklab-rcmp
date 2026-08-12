<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_equipment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('lab_id');
            $table->string('equipment_name', 300);
            $table->string('special_conditions_note', 300)->default('');
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->index('lab_id', 'idx_lab_equipment_lab_id');

            $table->foreign('lab_id', 'fk_lab_equipment_lab')
                ->references('id')->on('labs')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_equipment');
    }
};
