<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('oid', 191)->nullable()->unique()->after('staff_id')
                ->comment('Microsoft Entra ID (Azure AD) object ID, set on first SSO login');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->dropColumn('department');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['oid', 'last_login_at']);
            $table->string('department', 150)->default('');
        });
    }
};
