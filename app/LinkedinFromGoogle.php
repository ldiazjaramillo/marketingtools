<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkedinFromGoogle extends Model
{
    protected $fillable = [
        'import_id',
        'site',
        'title',
        'import_id',
        'company_name',
        'provider',
        'link',
        'full_name',
        'string_query',
        'string_linkedin'
    ];
}
