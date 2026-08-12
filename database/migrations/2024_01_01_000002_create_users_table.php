<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('staff_id', 20)->primary();
            $table->unsignedTinyInteger('role_id')->default(1);
            $table->string('full_name', 150);
            $table->string('department', 150)->default('');
            $table->string('email', 150);
            $table->string('phone_number', 30)->default('');
            $table->string('password_hash', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('email', 'uq_users_email');
            $table->index('role_id', 'idx_users_role_id');
            $table->index('is_active', 'idx_users_is_active');

            $table->foreign('role_id', 'fk_users_role')
                ->references('id')->on('roles')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
