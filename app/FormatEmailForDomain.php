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
				$result = $firstname.$lastname;
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
			'Firstname.Lastname' => [
				'name' => $firstname.'.'.$lastname,
				'template_id' => 'firstName_dot_lastName'
			],
			'Firstname' => [
				'name' => $firstname,
				'template_id' => 'firstName'
			],
			'Firstname_Lastname' => [
				'name' => $firstname.'_'.$lastname,
				'template_id' => 'Firstname_Lastname'
			],
			'Lastname'  => [
				'name' => $lastname,
				'template_id' => 'lastName'
			],
            'first_name' => [
                'name' => $firstname,
                'template_id' => 'firstName'
            ],
            'last_name' => [
                'name' => $lastname,
                'template_id' => 'lastName'
            ],
            'first_initiallast_name' => [
                'name' => $firstname[0].$lastname,
                'template_id' => 'firstInitial_lastName'
            ],
            'first_name_last_name' => [
                'name' => $firstname.'_'.$lastname,
                'template_id' => 'Firstname_Lastname'
            ],
            'first_name.last_name' => [
                'name' => $firstname.'.'.$lastname,
                'template_id' => 'firstName_dot_lastName'
            ],
            'FirstInitial' => [
                'name' => $firstname[0],
                'template_id' => 'FirstInitial'
            ],
            'FirstInitial_dot_Lastname' => [
                'name' => $firstname[0].'.'.$lastname,
                'template_id' => 'FirstInitial_dot_Lastname'
            ],
            'Firstname_dot_LastInitial' => [
                'name' => $firstname.'.'.$lastname[0],
                'template_id' => 'Firstname_dot_LastInitial'
            ],
            'FirstnameLastInitial' => [
                'name' => $firstname.$lastname[0],
                'template_id' => 'FirstnameLastInitial'
            ],
            'FirstnameLastname' => [
                'name' => $firstname.$lastname,
                'template_id' => 'FirstnameLastname'
            ],
            'Lastname_Firstname' => [
                'name' => $lastname.'_'.$firstname,
                'template_id' => 'Lastname_Firstname'
            ],
            'Lastname_dot_FirstInitial' => [
                'name' => $lastname.'.'.$firstname[0],
                'template_id' => 'Lastname_dot_FirstInitial'
            ],
            'LastnameFirstInitial' => [
                'name' => $lastname.$firstname[0],
                'template_id' => 'LastnameFirstInitial'
            ],
            'first_namelast_name' => [
                'name' => $firstname.$lastname,
                'template_id' => 'FirstnameLastname'
            ],
            'last_namefirst_initial' => [
                'name' => $lastname.$firstname[0],
                'template_id' => 'LastnameFirstInitial'
            ]
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
