<?php

namespace App;

use App\Jobs\GoogleEmailChecker;
use Illuminate\Database\Eloquent\Model;

class GoogleCheckEmail extends Model
{
    protected $fillable = ['import_id', 'email', 'count_results', 'data_comparasion_id'];

    public static function boot()
    {
        parent::boot();
        static::created(function ($model) {

        });
    }
}