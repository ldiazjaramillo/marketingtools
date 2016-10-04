<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormatEmailForDomain extends Model
{

	protected $fillable = [
		'domain', 'provider', 'note',
		'firstInitial_lastName',
		'firstInitial_lastInitial',
		'firstName',
		'lastName',
		'lastName_firstInitial',
		'firstName_lastInitial',
		'firstName_dot_lastName',
		'firstName-lastName'
	];

	public function getNameFromTemplate($firstname, $lastname){

		$result = $firstname.$lastname;

		switch (1) {
			case ($this->firstInitial_lastName):
				$result = $firstname[0].$lastname;
				break;

			case ($this->firstInitial_lastInitial):
				$result = $firstname[0].$lastname[0];
				break;

			case ($this->firstName):
				$result = $firstname;
				break;

			case ($this->lastName):
				$result = $lastname;
				break;

			case ($this->lastName_firstInitial):
				$result = $lastname.$firstname[0];
				break;

			case ($this->firstName_lastInitial):
				$result = $firstname.$lastname[0];
				break;

			case ($this->firstName_dot_lastName):
				$result = $firstname.'.'.$lastname;
				break;

			case ($this->{"firstName-lastName"}):
				$result = $firstname.'-'.$lastname;
				break;

			default:
				$result = $firstname.'.'.$lastname;
				break;
		}

		return $result;
	}

	public static function detectedTypeEmail($name = '', $domain = '', $firstname = '', $lastname = '', $provider = '')
	{

		$result = [];

		$allVariant = [
			'FirstInitialLastInitial' => [
				'name' => $firstname[0].$lastname[0],
				'template_id' => 'firstInitial_lastInitial'
			],
			'FirstInitialLastname' => [
				'name' => $firstname[0].$lastname,
				'template_id' => 'firstInitial_lastName'
			],
		];

		$extraQuerty = ['note' => $name];

		if(isset($allVariant[$name])){
			$extraQuerty = [
				$allVariant[$name]['template_id'] => 1
			];

			$result = [$allVariant[$name]['name']];
		}

		FormatEmailForDomain::create(
			array_merge([
				'domain' => $domain,
				'provider' => $provider,
			], $extraQuerty)
		);

		return $result;
	}
}
