<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogCallApi extends Model
{
    protected $fillable = [
		'import_id',
		'email',
		'did_you_mean',
		'user',
		'domain',
		'format_valid',
		'mx_found',
		'smtp_check',
		'catch_all',
		'role',
		'disposable',
		'free',
		'score'
	];

}
