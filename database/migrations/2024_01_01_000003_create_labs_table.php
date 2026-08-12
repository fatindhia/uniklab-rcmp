<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name', 150);
            $table->enum('lab_type', ['research', 'csl', 'pharma']);
            $table->enum('lab_block', ['az-research', 'av-research', 'csl', 'pharma']);
            $table->string('room_code', 30)->default('-');
            $table->string('location', 200)->default('');
            $table->unsignedSmallInteger('capacity')->default(0)->comment('0 = not pax-limited');
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->boolean('is_iso_certified')->default(false);
            $table->boolean('is_room_only')->default(false);
            $table->boolean('requires_special_conditions')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('lab_type', 'idx_labs_lab_type');
            $table->index('lab_block', 'idx_labs_lab_block');
            $table->index('status', 'idx_labs_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
