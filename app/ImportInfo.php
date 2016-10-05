<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportInfo extends Model
{
	protected $fillable = ['name', 'total_row', 'file_name', 'type'];
}
