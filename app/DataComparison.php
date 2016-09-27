<?php

namespace App;

use App\Jobs\PushEmailForCheckingScore;
use Illuminate\Database\Eloquent\Model;

class DataComparison extends Model
{
    protected $fillable = ['import_id', 'name', 'row_data', 'site', 'company_name'];

	protected $casts = [
		'row_data' => 'json',
	];


	static public function getVariableEmailName($name = ''){

		$pattern = '/[-+;=:_~,!@#$%^&\*\(\)><\'"]/';

		$replacement = ' ';

		$name = trim(preg_replace($pattern, $replacement, strtolower($name)));
		$name = explode(' ', $name);

		$firstname = array_first($name);
		$lastname = array_last($name);


		$result = [
			$lastname.'.'.$firstname,//gutin.alexander
			$firstname.'.'.$lastname, //alexander.gutin
			$lastname.$firstname,//gutinalexander
			$firstname[0].$lastname,//agutin
			$lastname, //gutin
			$firstname, //alexander
		];

		return $result;
	}

	public static function boot()
	{
		parent::boot();
		static::created(function ($model) {

			if(empty($model->site)){
				$model->email = false;
				$model->score = 0;
				$model->save();
			} else {
				dispatch(
					(new PushEmailForCheckingScore([
						'data_id' => $model->id,
						'name' => $model->name,
						'domain' => $model->site
					]))->onQueue('default')
				);
			}

		});
	}


}
