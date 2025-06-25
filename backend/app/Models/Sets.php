<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sets extends Model
{
    protected $table = 'sets';

    protected $guarded = [];

    public function lessons(){
        return $this->hasMany(Lessons::class, 'set_id');
    }
}
