<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    protected $table = 'questionnaires';

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }
}
