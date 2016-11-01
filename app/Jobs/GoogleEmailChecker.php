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
    protected $request = '';
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
        try {
            $provider_name = 'google';

            $allVariantEmail = \App\DataComparison::getVariableEmailName($this->name, $this->domain);

            $emails = [];
            foreach ($allVariantEmail as $email){
                $emails[] = '"'.$email.'@'.$this->domain.'"';
            }
            $request = implode(' OR ', $emails);

            $client = new Client([
                'base_uri' => 'https://www.google.com.ua/'
            ]);

            $googleResponse = $client->get('search', [
                'query' => [
                    'sclient' => 'psy-ab',
                    'safe' => 'off',
                    'source' => 'hp',
                    'q' => $request
                ]
            ])->getBody()->getContents();

            foreach ($emails as $email){
                $email = str_replace('"', '', $email);

                if(strpos($googleResponse, '<b>'.$email.'</b>') !== false){
                    GoogleCheckEmail::where(['email' => trim($email), 'import_id' => $this->import_id])->update(['count_results' => 1, 'provider_name' => $provider_name]);
                    DataComparison::where(['id' => $this->data['data_comparasion_id']])->update(['email' => $email, 'score' => 99.99]);
                } else {
                    GoogleCheckEmail::where(['email' => trim($email), 'import_id' => $this->import_id])->update(['count_results' => 0, 'provider_name' => $provider_name]);
                }

            }

            return true;

        } catch (\Exception $e){
            Bugsnag::notifyException($e);
        }

	}
}
