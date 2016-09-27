<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Serps\Core\Http\Proxy;

class CheckPhoneInGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:phoneInGoogle {company_name}';

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
        $googleClient = new \Serps\SearchEngine\Google\GoogleClient(new \Serps\HttpClient\CurlClient());


        $company_name = $this->argument('company_name');

        // Tell the client to use a user agent
        $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
        $googleClient->request->setUserAgent($userAgent);

        $googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();

        $googleUrl->setSearchTerm($company_name . ' phone number');

        $proxy = new Proxy(env('PROXY_HOST', '37.48.118.90'), env('PROXY_PORT', '13012'));
        $response = $googleClient->query($googleUrl, $proxy);

        $blockWithPhone = $response->cssQuery('._RCm');

        if(!empty($blockWithPhone->length)){
            $str = $response->cssQuery('._RCm')->item(0)->parentNode->textContent;
            preg_match_all('!\d+!', $str, $matches);
            $number = implode('', $matches[0]);
        } else {
            $this->info('block with phone empty');
            $number = 0;
        }

        $this->info('number '.$number);
    }
}
