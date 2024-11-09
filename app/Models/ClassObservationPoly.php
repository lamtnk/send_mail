<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassObservationPoly extends Model
{
    use HasFactory;

    protected $table = 'class_observations_poly';

    // Các thuộc tính có thể gán hàng loạt
    protected $fillable = [
        'date',
        'location',
        'subject_code',
        'department',
        'section',
        'evaluated_teacher_code',
        'evaluator_teacher1',
        'score1',
        'evaluator_email1',
        'evaluator_teacher2',
        'score2',
        'evaluator_email2',
        'lesson_name',
        'advantages',
        'disadvantages',
        'conclusion',
        'block',
        'semester',
        'sent_at', // Thời gian gửi email
    ];

    // Định nghĩa kiểu của các thuộc tính
    protected $casts = [
        'date' => 'date',
        'sent_at' => 'datetime',
        'score1' => 'integer',
        'score2' => 'integer',
    ];
}
