<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who changed what in the admin panel's configuration areas — Manage Labs
     * and Manage Staff. Booking decisions already have their own trail in
     * booking_audit_logs; this covers the settings-shaped changes that had no
     * record at all, and is surfaced to Super Admins as the Activity Log.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('area', 20);              // labs | staff
            $table->string('action', 20);            // created | updated | deleted
            $table->string('performed_by', 20)->nullable();
            $table->string('subject_id', 50)->nullable();
            $table->string('subject_label', 200);    // lab or person name at the time
            $table->json('changes')->nullable();     // field => [from, to] on updates
            $table->timestamp('created_at')->useCurrent();

            $table->index(['area', 'created_at']);
            $table->index('created_at');

            $table->foreign('performed_by')
                ->references('staff_id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
