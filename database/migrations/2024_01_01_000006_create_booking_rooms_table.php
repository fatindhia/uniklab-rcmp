<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('booking_id');
            $table->unsignedSmallInteger('lab_id');
            $table->boolean('is_primary')->default(true)->comment('Pharma: 0 = alt-lab equipment source');

            $table->unique(['booking_id', 'lab_id'], 'uq_booking_rooms');
            $table->index('lab_id', 'idx_booking_rooms_lab_id');

            $table->foreign('booking_id', 'fk_booking_rooms_booking')
                ->references('id')->on('bookings')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('lab_id', 'fk_booking_rooms_lab')
                ->references('id')->on('labs')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_rooms');
    }
};
