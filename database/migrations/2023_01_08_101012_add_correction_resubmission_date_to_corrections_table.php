<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCorrectionResubmissionDateToCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corrections', function (Blueprint $table) {
            $table->dateTime('student_resubmission_date')->nullable()->after('expert_correction_feedback');
            $table->dateTime('expert_final_note_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corrections', function (Blueprint $table) {
            $table->dropColumn('student_resubmission_date');
            $table->dropColumn('expert_final_note_date');
        });
    }
}
