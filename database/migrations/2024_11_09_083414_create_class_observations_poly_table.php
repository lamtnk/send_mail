<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassObservationsPolyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_observations_poly', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('location');
            $table->string('subject_code');
            $table->string('department');
            $table->string('section');
            $table->string('evaluated_teacher_code');
            $table->string('evaluator_teacher1');
            $table->string('score1');
            $table->string('evaluator_email1');
            $table->string('evaluator_teacher2')->nullable();
            $table->string('score2')->nullable();
            $table->string('evaluator_email2')->nullable();
            $table->string('lesson_name');
            $table->text('advantages')->nullable();
            $table->text('disadvantages')->nullable();
            $table->text('conclusion')->nullable();
            $table->string('block');
            $table->string('semester');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('send_by')->nullable();
            $table->timestamps();

            $table->unique(['date', 'subject_code', 'section', 'block', 'semester'], 'unique_class_observation_poly');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_observations_poly');
    }
}
