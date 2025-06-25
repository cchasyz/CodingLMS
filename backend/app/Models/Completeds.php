<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Completeds extends Model
{
    protected $table = 'completed_lessons';

    protected $guarded = [];

    public function lesson(){
        return $this->belongsTo(Lessons::class, 'lesson_id');
    }
}
