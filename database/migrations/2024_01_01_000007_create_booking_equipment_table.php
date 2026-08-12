<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_equipment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('booking_id');
            $table->unsignedSmallInteger('lab_id');
            $table->string('equipment_name', 300);
            $table->boolean('is_alt_lab')->default(false)->comment('Pharma overflow from alt lab');

            $table->index('booking_id', 'idx_booking_equipment_booking_id');
            $table->index('lab_id', 'idx_booking_equipment_lab_id');

            $table->foreign('booking_id', 'fk_booking_equipment_booking')
                ->references('id')->on('bookings')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('lab_id', 'fk_booking_equipment_lab')
                ->references('id')->on('labs')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_equipment');
    }
};
