<?php

namespace App\Jobs;

use App\DataComparison;
use App\GoogleCheckPhone;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
//use Log;
use Serps\Core\Http\Proxy;

class GooglePhoneFinder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    protected $data = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        //\Log::debug('Handle GooglePhoneFinder');

        $googleClient = new \Serps\SearchEngine\Google\GoogleClient(new \Serps\HttpClient\CurlClient());
        try{

            try {

                $providerName = 'privat_service';

                $client = new Client([
                    'base_uri' => env('SERVICE_PHONE_AND_SOCIAL', 'http://104.131.10.69:5000')
                ]);

                $results = $client->get('find_phones', [
                    'query' => [
                        'url' => 'http://'.$this->data['site']
                    ]
                ])->getBody()->getContents();


                $results = json_decode($results, 1);

                if (isset($results['phones'])) {
                    $number = array_first($results['phones']);
                } else {
                    $number = array_first($results);
                }

                var_dump($this->data['id']);
                var_dump($number);

                if(empty($number)){
                    throw new \Exception('Empty results from private service');
                }

            } catch (\Exception $e) {
                var_dump($e->getMessage());

                Bugsnag::notifyException($e);

                // Tell the client to use a user agent
                $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";

                $googleClient->request->setUserAgent($userAgent);
                $providerName = 'google';

                $googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();

                //\Log::debug('Search phone in Google "' . $this->data['company_name'] . ' phone number' . '"');

                $googleUrl->setSearchTerm($this->data['company_name'] . ' phone number');

                $proxy = new Proxy(env('PROXY_HOST', '37.48.118.90'), env('PROXY_PORT', '13012'));
                $response = $googleClient->query($googleUrl, $proxy);

                $blockWithPhone = $response->cssQuery('._RCm');

                if(!empty($blockWithPhone->length)){
                    $str = $response->cssQuery('._RCm')->item(0)->parentNode->textContent;
                    preg_match_all('!\d+!', $str, $matches);
                    $number = implode('', $matches[0]);
                } else {
                    $number = 0;
                }

            }

            $checkFone = GoogleCheckPhone::where(['id' => $this->data['id']])->first();
            $checkFone->phone = $number;
            $checkFone->site = $this->data['site'];
            $checkFone->provider_name = $providerName;
            $checkFone->save();

            DataComparison::where(['site' => $checkFone->site])->update(['phone' => $number]);

        } catch (\Exception $e){

            Bugsnag::notifyException($e);

            throw new \Exception($e->getMessage().' ' .$e->getFile() . ':' . $e->getLine(), $e->getCode());

        }

    }
}
