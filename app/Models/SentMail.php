<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentMail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'subject_code',
        'section',
        'sent_at',
    ];
}
