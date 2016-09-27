<?php

namespace App;

use App\Jobs\GooglePhoneFinder;
use Illuminate\Database\Eloquent\Model;

class GoogleCheckPhone extends Model
{
    protected $fillable = [
		'import_id',
		'site',
		'company_name',
		'phone',
		'data_comparasion_id'
	];

	public static function boot()
	{
		parent::boot();
		static::created(function ($model) {
			dispatch(
				(new GooglePhoneFinder([
					'id' => $model->id,
					'company_name' => $model->company_name,
				]))->onQueue('phone_finder')
			);
		});
	}
}
