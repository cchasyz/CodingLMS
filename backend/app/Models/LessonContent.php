<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonContent extends Model
{
    protected $table = 'lesson_contents';

    protected $guarded = [];

    public function options(){
        return $this->hasMany(Options::class, 'lesson_content_id');
    }

    
}
