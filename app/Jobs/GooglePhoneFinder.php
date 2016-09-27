<?php

namespace App\Jobs;

use App\GoogleCheckPhone;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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

        try {
            $googleClient = new \Serps\SearchEngine\Google\GoogleClient(new \Serps\HttpClient\CurlClient());

            // Tell the client to use a user agent
            $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
            $googleClient->request->setUserAgent($userAgent);

            $googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();

            $googleUrl->setSearchTerm($this->data['company_name'] . ' phone');


            if(env('APP_ENV') == 'production'){
                $proxy = new Proxy('37.48.118.90', '13012');
                $response = $googleClient->query($googleUrl, $proxy);
            } else {
                $response = $googleClient->query($googleUrl);
            }

            //$proxy = new Proxy('72.252.14.163', '8080');
            //$response = $googleClient->query($googleUrl, $proxy);
            //$response = $googleClient->query($googleUrl);

            $blockWithPhone = $response->cssQuery('._RCm');

            if(!empty($blockWithPhone->length)){
                $str = $response->cssQuery('._RCm')->item(0)->parentNode->textContent;
                preg_match_all('!\d+!', $str, $matches);
                $number = implode('', $matches[0]);
            } else {
                $number = 0;
            }
            var_dump($number);

        } catch (\Exception $e){
            Log::warning('Error google phone finder for ' .  $this->data['id'] . ' ' . $this->data['company_name'] . '. '.$e->getMessage());
            $number = 0;
        } finally {

            $checkFone = GoogleCheckPhone::where(['id' => $this->data['id']])->first();
            $checkFone->phone = $number;
            $checkFone->save();

        }

        return true;
    }
}
