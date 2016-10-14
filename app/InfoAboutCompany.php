<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfoAboutCompany extends Model
{
    protected $fillable = [
        'google_plus',
        'instagram',
        'phones',
        'twitter',
        'youtube',
        'linkedin',
        'facebook'
    ];

    protected $casts = [
        'phones' => 'json',
    ];
}
