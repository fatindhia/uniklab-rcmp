<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_students', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('booking_id');
            $table->string('student_name', 150);
            $table->string('student_id', 30);
            $table->unsignedTinyInteger('student_year')->nullable()->comment('CSL only: year group 1-4; NULL for pharma/research');
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->index('booking_id', 'idx_booking_students_booking_id');
            $table->index('student_id', 'idx_booking_students_student_id');

            $table->foreign('booking_id', 'fk_booking_students_booking')
                ->references('id')->on('bookings')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_students');
    }
};
