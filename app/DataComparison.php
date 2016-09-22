<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataComparison extends Model
{
    protected $fillable = ['import_id', 'name', 'row_data', 'site'];

	protected $casts = [
		'row_data' => 'json',
	];


	static public function getVariableEmailName($name = ''){

		$pattern = '/[-,!@#$%^&\*\(\)><]/';
		$replacement = ' ';

		$name = trim(preg_replace($pattern, $replacement, strtolower($name)));
		$name = explode(' ', $name);

		$firstname = array_first($name);
		$lastname = array_last($name);


		$result = [
			$firstname, //alexander
			$lastname, //gutin
			$firstname.'.'.$lastname, //alexander.gutin
			$lastname.'.'.$firstname,//gutin.alexander
			$lastname.$firstname,//gutinalexander
			$firstname[0].$lastname,//agutin
		];

		return $result;
	}
}
