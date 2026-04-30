<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';

    protected $casts = [
        'options'             => 'array',
        'branching_config'    => 'array',
        'mandatory'           => 'boolean',
        'is_contraindication' => 'boolean',
    ];
}
