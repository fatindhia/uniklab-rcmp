<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_audit_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('booking_id');
            $table->string('action', 30);
            $table->string('performed_by', 20)->nullable();
            $table->text('detail')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('booking_id');

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('performed_by')->references('staff_id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_audit_logs');
    }
};
