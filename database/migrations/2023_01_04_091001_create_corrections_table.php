<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corrections', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('admin_id')->nullable();
            $table->bigInteger('school_id')->nullable();
            $table->bigInteger('topic_id');
            $table->bigInteger('package_id');
            $table->bigInteger('package_type_id');
            $table->bigInteger('expert_id');
            $table->dateTime('deadline');
            $table->boolean('is_accepted')->default(0);
            $table->dateTime('accepted_date');
            $table->boolean('is_seen_by_expert')->default(0);
            $table->boolean('is_seen_by_student')->default(0);
            $table->boolean('is_student_resubmited')->default(0);
            $table->enum('status', ['Submitted', 'Accepted', 'Corrected', 'Drafted'])->default('Submitted');
            $table->text('student_correction')->nullable();
            $table->text('expert_correction_note')->nullable();
            $table->text('expert_correction_feedback')->nullable();
            $table->enum('grade', ['BelowSatisfaction', 'Satisfactory', 'Good', 'Better', 'Excellent'])->default('BelowSatisfaction');
            $table->text('student_rewrite')->nullable();
            $table->text('expert_final_note')->nullable();
            $table->dateTime('student_correction_date');
            $table->dateTime('expert_correction_date');
            $table->dateTime('completed_date');
            $table->float('rating')->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('corrections');
    }
}
