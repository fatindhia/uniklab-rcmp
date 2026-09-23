<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * New staff are created under a placeholder staff ID that their first
     * Microsoft sign-in replaces. Every other foreign key on users.staff_id
     * already cascades updates; this one didn't, so the swap would fail for
     * anyone who had logged activity in the meantime.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->foreign('performed_by')
                ->references('staff_id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->foreign('performed_by')
                ->references('staff_id')->on('users')
                ->nullOnDelete();
        });
    }
};
