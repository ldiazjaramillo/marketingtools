<?php

namespace App;

use App\Jobs\PushEmailForCheckingScore;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\DomCrawler\Crawler;

class DataComparison extends Model
{
    protected $fillable = ['import_id', 'name', 'row_data', 'site', 'company_name', 'phone', 'email', 'score'];

	protected $casts = [
		'row_data' => 'json',
	];


	static public function getVariableEmailName($name = '', $domain = ''){

		$pattern = '/[-+;=:_~,!@#$%^&\*\(\)><\'"]/';

		$replacement = ' ';

		$name = trim(preg_replace($pattern, $replacement, strtolower($name)));
		$name = explode(' ', $name);

		$firstname = array_first($name);
		$lastname = array_last($name);


		$allVariantEmailName = [
			$lastname.'.'.$firstname,//gutin.alexander
			$firstname.'.'.$lastname, //alexander.gutin
			$lastname.$firstname,//gutinalexander
			$firstname[0].$lastname,//agutin
			$lastname, //gutin
			$firstname, //alexander
		];


		$formatEmail = FormatEmailForDomain::where(['domain' => $domain]);

        try {
            if($formatEmail->count() == 1){

                $formatEmail = $formatEmail->first();

                $allVariantEmailName = array_merge([$formatEmail->getNameFromTemplate($firstname, $lastname)], $allVariantEmailName);
            } else {




                $site = str_replace(['http://', 'https://', 'www.'], ['', '', ''], $domain);
                $site = parse_url($site);
                $site = $site['path'];

                $client = new Client([
                    'base_uri' => 'http://www.email-format.com/d/'
                ]);

                $result = $client->get($site, [
                    'proxy' => [
                        'http'  => 'tcp://' . env('PROXY_HOST', '37.48.118.90') . ':' . env('PROXY_PORT', '13012')
                    ]
                ])->getBody()->getContents();

                $checkMediumConfidence = strpos($result, 'medium confidence');

                if ($checkMediumConfidence !== false) {
                    $crawler = new Crawler($result);
                    $first = $crawler->filter('.format.fl')->first();
                    $template = str_replace(' ', '', $first->text());

                    $suggestionTemplate = FormatEmailForDomain::detectedTypeEmail($template, $domain, $firstname, $lastname, 'email-format.com');

                    $allVariantEmailName = array_merge($suggestionTemplate, $allVariantEmailName);


                    $check = DataComparison::where([
                        'name' => $firstname.' '.$lastname,
                        'site' => $domain
                    ])->update([
                        'email' => $allVariantEmailName[0].'@'.$domain,
                        'score' => 99
                    ]);

                    return 'finish';

                } else {
                    $clientEmailBreaker = new Client(['base_uri' => 'http://www.emailbreaker.com/a/search/']);

                    $result = trim($clientEmailBreaker->get($domain, [
                        'proxy' => [
                            'http'  => 'tcp://' . env('PROXY_HOST', '37.48.118.90') . ':' . env('PROXY_PORT', '13012')
                        ]
                    ])->getBody()->getContents());

                    if($result == 'null'){
                        //var_dump($result);
                    } else {
                        $result = json_decode($result, 1);
                        $suggestion = array_first($result)['format'];
                        list($template, $domain) = explode('@', $suggestion);
                        $suggestionTemplate = FormatEmailForDomain::detectedTypeEmail($template, $domain, $firstname, $lastname, 'emailbreaker.com');
                        $allVariantEmailName = array_merge($suggestionTemplate, $allVariantEmailName);
                    }
                }

            }
        } catch (\Exception $e){
            Bugsnag::notifyException($e);
        }

		$allVariantEmailName = array_unique($allVariantEmailName);

		return $allVariantEmailName;
	}

	public static function boot()
	{
		parent::boot();

		static::updated(function($model){
			//\Log::debug('Update DataComparison ' . json_encode($model));
		});

		static::created(function ($model) {

			//\Log::info('Create new DataComparison ' . $model->id . ' ' . json_encode($model));

			if(empty($model->site)){
				$model->email = false;
				$model->score = 0;
				$model->save();
			} else {
				if($model->email != '0'){
					dispatch(
						(new PushEmailForCheckingScore([
							'data_id' => $model->id,
							'name' => $model->name,
							'domain' => $model->site
						]))->onQueue('default')
					);
				}
			}

		});
	}


}
