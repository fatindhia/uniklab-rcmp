<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref', 20);
            $table->string('user_staff_id', 20)->nullable()->comment('NULL for unauthenticated submissions');
            $table->string('applicant_name', 150);
            $table->string('applicant_id', 30)->comment('Staff ID or Student ID');
            $table->string('applicant_email', 150);
            $table->string('applicant_phone', 30)->default('');
            $table->string('applicant_department', 150)->default('');
            $table->string('applicant_role', 50)->comment('Staff / Lecturer / Clinical Instructor / Year 1-4 etc.');
            $table->enum('lab_type', ['research', 'csl', 'pharma']);
            $table->enum('lab_block', ['az-research', 'av-research', 'csl', 'pharma']);
            $table->date('booking_date_from');
            $table->date('booking_date_to')->comment('Same as date_from for single-day bookings');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('research_pax')->default(0);
            $table->text('purpose_of_use')->nullable();
            $table->boolean('has_special_conditions')->default(false);
            $table->string('csl_session_type', 60)->default('');
            $table->string('csl_discipline', 60)->default('');
            $table->unsignedSmallInteger('csl_num_students')->default(0);
            $table->enum('pharma_primary_lab', ['CL', 'MDLP', 'PL1', 'PL2'])->nullable();
            $table->string('pharma_group_number', 30)->default('');
            $table->unsignedSmallInteger('pharma_num_students')->default(0);
            $table->boolean('pharma_tc_accepted')->default(false);
            $table->text('purpose');
            $table->text('applicant_remark')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('admin_remark')->nullable();
            $table->string('processed_by', 20)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('ref', 'uq_bookings_ref');
            $table->index('status', 'idx_bookings_status');
            $table->index('lab_type', 'idx_bookings_lab_type');
            $table->index('lab_block', 'idx_bookings_lab_block');
            $table->index('booking_date_from', 'idx_bookings_date_from');
            $table->index(['booking_date_from', 'booking_date_to'], 'idx_bookings_date_range');
            $table->index('applicant_email', 'idx_bookings_applicant_email');
            $table->index('applicant_id', 'idx_bookings_applicant_id');
            $table->index('user_staff_id', 'idx_bookings_user_staff_id');
            $table->index('processed_by', 'idx_bookings_processed_by');

            $table->foreign('user_staff_id', 'fk_bookings_user')
                ->references('staff_id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('processed_by', 'fk_bookings_processed_by')
                ->references('staff_id')->on('users')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
