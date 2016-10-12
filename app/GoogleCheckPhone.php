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
		'data_comparasion_id',
        'provider_name'
	];

	public static function boot()
	{
		parent::boot();

		static::updated(function($model){
			\Log::debug('Update GoogleCheckPhone ' . json_encode($model));
		});

		static::created(function ($model) {

			\Log::debug('Create GoogleCheckPhone ' . json_encode($model));
			\Log::debug('Push GoogleCheckPhone in phone_finder');

			dispatch(
				(new GooglePhoneFinder([
					'id' => $model->id,
					'company_name' => $model->company_name,
                    'site' => $model->site
				]))->onQueue('phone_finder')
			);
		});
	}
}
