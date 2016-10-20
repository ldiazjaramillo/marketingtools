<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportInfo extends Model
{
	protected $fillable = ['name', 'total_row', 'file_name', 'type'];

	public static function boot()
	{
		parent::boot();
		static::created(function ($model) {
			//\Log::info('Create new ImportInfo ' . $model->id . ' ' . json_encode($model));
		});
	}

}
