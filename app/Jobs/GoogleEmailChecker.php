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
			try {
				$email = $nameEmail . '@' . $this->domain;

				\Log::debug('Handle GoogleEmailChecker ' . $email);


				$count_result = 0;


				// Tell the client to use a user agent
				$userAgent = config('user_agent')[array_rand(config('user_agent'), 1)];
				$googleClient->request->setUserAgent($userAgent);

				$googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();
				$googleUrl->setSearchTerm('"' . $email . '"');

				\Log::debug('Start find email ' . $email . ' in Google');

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

			} catch (\Exception $e) {

				Bugsnag::notifyException($e);

				\Log::debug('Exception GoogleEmailChecker');

				\Log::debug(json_encode([
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'message' => $e->getMessage(),
				]));


				\Log::debug('Start find email ' . $email . ' in Bing');

				try {
					$bingClient = new Client([
						'base_uri' => 'https://bing.com/',
						'headers' => [
							'User-Agent' => config('user_agent')[array_rand(config('user_agent'), 1)]
						]
					]);

					$bingResultPage = $bingClient->get('search', [
						'query' => [
							'q' => '"' . $email . '"'
						],
						'proxy' => [
							'http' => 'tcp://' . env('PROXY_HOST', '37.48.118.90') . ':' . env('PROXY_PORT', '13012')
						]
					])->getBody()->getContents();

					$isResultBing = (!(bool)strpos($bingResultPage, 'class="b_no"'));

					$count_result = ($isResultBing) ? 1 : 0;
				} catch (\Exception $e) {
					Bugsnag::notifyException($e);
				}

			}


			try{
				\Log::debug('Total results for ' . $email . ' ' . $count_result);

				GoogleCheckEmail::where(['id' => $this->data['id']])->update(['count_results' => $count_result]);

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
