<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfoAboutCompany extends Model
{
    protected $fillable = [
        'google_plus',
        'instagram',
        'phone',
        'twitter',
        'youtube',
        'linkedin',
        'facebook'
    ];

    protected $casts = [
        'phone' => 'json',
    ];
}
