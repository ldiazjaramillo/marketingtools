<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkedinParserSession extends Model
{
    protected $fillable = ['request', 'total_results'];
}
