<?php

namespace App\Jobs;

use App\DataComparison;
use App\GoogleCheckPhone;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;
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

        $googleClient = new \Serps\SearchEngine\Google\GoogleClient(new \Serps\HttpClient\CurlClient());

        // Tell the client to use a user agent
        $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
        $googleClient->request->setUserAgent($userAgent);

        $googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();

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

    }
}
