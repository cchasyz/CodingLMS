<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Admins extends Model
{
    use HasApiTokens;

    protected $table = 'administrators';

    protected $guarded = [];
}
