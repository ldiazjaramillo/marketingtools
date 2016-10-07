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

            // Tell the client to use a user agent
            $userAgent = config('user_agent')[array_rand(config('user_agent'), 1)];
            $googleClient->request->setUserAgent($userAgent);

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

            $checkFone = GoogleCheckPhone::where(['id' => $this->data['id']])->first();
            $checkFone->phone = $number;
            $checkFone->save();

            DataComparison::where(['site' => $checkFone->site])->update(['phone' => $number]);
        } catch (\Exception $e){
            Bugsnag::notifyException($e);
        }

    }
}
