<?php

namespace App;

use App\Jobs\FindCompanySite;
use Illuminate\Database\Eloquent\Model;

class DetectedSiteCompany extends Model
{
    protected $fillable = ['company_name', 'site', 'import_id'];

	public static function boot()
	{
		parent::boot();
		static::created(function ($model) {
			
			dispatch((new FindCompanySite([
				'id' => $model->id,
				'company_name' => $model->company_name,
			]))->onQueue('datected_company_site'));

		});
	}

}
