<?php

namespace App\Jobs;

use App\DataComparison;
use App\GoogleCheckEmail;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Serps\Core\Http\Proxy;

class GoogleEmailChecker implements ShouldQueue
{
	use InteractsWithQueue, Queueable, SerializesModels;

	protected $data = [];

	protected $name = '';
	protected $domain = '';
	protected $import_id = '';

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(array $data)
	{
		$this->name = $data['name'];
		$this->domain = $data['domain'];
		$this->data = $data;
		$this->import_id = $data['import_id'];
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{

		$allVariantEmail = \App\DataComparison::getVariableEmailName($this->name, $this->domain);

		$googleClient = new \Serps\SearchEngine\Google\GoogleClient(new \Serps\HttpClient\CurlClient());

		foreach ($allVariantEmail as $nameEmail) {

		    $email = $nameEmail . '@' . $this->domain;
            $count_result = 0;



                $provider_name = 'google';
				//\Log::debug('Handle GoogleEmailChecker ' . $email);
				// Tell the client to use a user agent
                $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
                $googleClient->request->setUserAgent($userAgent);

				$googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();
				$googleUrl->setSearchTerm('"' . $email . '"');

				//\Log::debug('Start find email ' . $email . ' in Google');

				$proxy = new Proxy(env('PROXY_HOST', '37.48.118.90'), env('PROXY_PORT', '13012'));
				$response = $googleClient->query($googleUrl, $proxy);

				$resultObject = $response->getDom()->getElementById('resultStats');

				$isSuggestionResult = $response->cssQuery('.ct-cs .med')->length;

				if (!is_null($resultObject) && $isSuggestionResult == false) {
					$result_string = $resultObject->textContent;
					$result_string = str_replace([' ', ' '], ['', ''], $result_string);
					preg_match("/[0-9,]+/", $result_string, $result);
					$count_result = (int)$result[0];
				}


			try{
				//\Log::debug('Total results for ' . $email . ' ' . $count_result);

                GoogleCheckEmail::where(['data_comparasion_id' => $this->data['data_comparasion_id'], 'count_results' => NULL])->update(['count_results' => 0]);
				GoogleCheckEmail::where(['email' => trim($email), 'import_id' => $this->import_id])->update(['count_results' => $count_result, 'provider_name' => $provider_name]);

				if ($count_result > 0) {
					DataComparison::where(['id' => $this->data['data_comparasion_id']])->update(['email' => $email, 'score' => 99.99]);
					return true;
				}
			} catch (\Exception $e){
				Bugsnag::notifyException($e);
			}
			
		}

	}
}
