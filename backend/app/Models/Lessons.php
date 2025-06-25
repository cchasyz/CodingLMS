<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lessons extends Model
{
    protected $table = 'lessons';

    protected $guarded = [];

    public function completed(){
        return $this->hasMany(Completeds::class, 'lesson_id');
    }

    public function contents(){
        return $this->hasMany(LessonContent::class, 'lesson_id');
    }

    public function set(){
        return $this->belongsTo(Sets::class, 'set_id');
    }
}
