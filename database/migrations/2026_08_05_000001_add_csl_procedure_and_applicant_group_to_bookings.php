<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CSL booking module additions:
 * - applicant_group: the applicant's class/cohort group (e.g. "4A").
 * - csl_procedure: the procedure(s)/activity to be carried out during the
 *   session, so lab staff know what to prepare the room for. Free text, since
 *   a session often covers several procedures listed one per line.
 *
 * The old combined "BCC" discipline is also split into "BCC Surgery" and
 * "BCC Medicine"; existing rows keep a value that is no longer offered, so
 * they are relabelled to "BCC Surgery" (the surgical room set BCC bookings
 * historically used).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('applicant_group', 30)->default('')->after('applicant_role');
            $table->text('csl_procedure')->nullable()->after('csl_discipline');
        });

        DB::table('bookings')->where('csl_discipline', 'BCC')->update(['csl_discipline' => 'BCC Surgery']);
        DB::table('bookings')->where('csl_discipline', 'ILa')->update(['csl_discipline' => 'ILA']);
    }

    public function down(): void
    {
        DB::table('bookings')->whereIn('csl_discipline', ['BCC Surgery', 'BCC Medicine'])->update(['csl_discipline' => 'BCC']);
        DB::table('bookings')->where('csl_discipline', 'ILA')->update(['csl_discipline' => 'ILa']);

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['applicant_group', 'csl_procedure']);
        });
    }
};
