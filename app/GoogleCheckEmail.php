<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GoogleCheckEmail extends Model
{
    protected $fillable = ['import_id', 'email', 'count_results', 'data_comparasion_id'];
}
