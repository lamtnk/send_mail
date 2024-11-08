<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_observations', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('location');
            $table->string('subject_code');
            $table->string('department')->nullable();
            $table->string('section');
            $table->string('evaluated_teacher_code');
            $table->string('evaluator_teacher1');
            $table->integer('score1');
            $table->string('evaluator_email1');
            $table->string('evaluator_teacher2')->nullable();
            $table->integer('score2')->nullable();
            $table->string('evaluator_email2')->nullable();
            $table->string('lesson_name');
            $table->text('advantages')->nullable();
            $table->text('disadvantages')->nullable();
            $table->text('conclusion')->nullable();
            $table->string('block');
            $table->string('semester');
            $table->timestamp('sent_at')->nullable(); // Để kiểm tra email đã gửi chưa
            $table->timestamps();

            // Đảm bảo dữ liệu duy nhất với các trường liên quan, không có user_id
            $table->unique(['date', 'subject_code', 'section', 'block', 'semester'], 'unique_class_observation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_observations');
    }
}
