<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_blocks', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->enum('lab_type', ['research', 'csl', 'pharma']);
            $table->enum('category', ['class', 'practical', 'maintenance', 'reserved', 'exam', 'event']);
            $table->string('title', 200);
            $table->string('pic', 150)->default('')->comment('Person In Charge');
            $table->date('block_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('rooms')->comment('Array of room name strings');
            $table->enum('recurring', ['none', 'weekly', 'biweekly'])->default('none');
            $table->text('notes')->nullable();
            $table->string('created_by', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('lab_type', 'idx_time_blocks_lab_type');
            $table->index('block_date', 'idx_time_blocks_block_date');
            $table->index('created_by', 'idx_time_blocks_created_by');

            $table->foreign('created_by', 'fk_time_blocks_created_by')
                ->references('staff_id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_blocks');
    }
};
