<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Serps\Core\Http\Proxy;

class CheckPhoneInGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:phoneInGoogle {company_name} {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check phone in google';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        try {
            $client = new Client([
                'base_uri' => env('SERVICE_PHONE_AND_SOCIAL', 'http://104.131.10.69:5000')
            ]);

            $results = $client->get('find_phones', [
                'query' => [
                    'url' => 'http://'.$this->argument('site')
                ]
            ])->getBody()->getContents();


            $results = \GuzzleHttp\json_decode($results);

            if (isset($results['phones'])) {
                $number = array_first($results['phones']);
            } else {
                $number = array_first($results);
            }

        } catch (\Exception $e) {
            $googleClient = new \Serps\SearchEngine\Google\GoogleClient(new \Serps\HttpClient\CurlClient());


            $company_name = $this->argument('company_name');

            // Tell the client to use a user agent
            $userAgent = config('user_agent')[array_rand(config('user_agent'), 1)];
            $googleClient->request->setUserAgent($userAgent);

            $googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();

            \Log::debug('Search phone in Google "' . $company_name . ' phone number' . '"');

            $googleUrl->setSearchTerm($company_name . ' phone number');

            $proxy = new Proxy(env('PROXY_HOST', '37.48.118.90'), env('PROXY_PORT', '13012'));
            $response = $googleClient->query($googleUrl, $proxy);

            $blockWithPhone = $response->cssQuery('._RCm');

            if (!empty($blockWithPhone->length)) {
                $str = $response->cssQuery('._RCm')->item(0)->parentNode->textContent;
                preg_match_all('!\d+!', $str, $matches);
                $number = implode('', $matches[0]);
            } else {
                $this->info('block with phone empty');
                $number = 0;
            }

            \Log::debug('Results in Google ' . $number . ' for ' . $company_name);
        }


        $this->info('number ' . $number);
    }
}
