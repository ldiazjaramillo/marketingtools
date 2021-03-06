<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Serps\Core\Http\Proxy;

class CheckEmailInGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:emailInGoogle {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check email in Google';

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

        $email = $this->argument('email');

        $googleClient = new \Serps\SearchEngine\Google\GoogleClient(new \Serps\HttpClient\CurlClient());

        // Tell the client to use a user agent
        $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
        $googleClient->request->setUserAgent($userAgent);

        $googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();
        $googleUrl->setSearchTerm('"'.$email.'"');

        $proxy = new Proxy(env('PROXY_HOST', '37.48.118.90'), env('PROXY_PORT', '13012'));
        $response = $googleClient->query($googleUrl, $proxy);

        $resultObject = $response->getDom()->getElementById('resultStats');//->textContent;

        $isSuggestionResult = $response->cssQuery('.ct-cs .med')->length;//

        $count_result = 0;

        if(!is_null($resultObject) && $isSuggestionResult == false){
            $result_string = $resultObject->textContent;
            $this->info($result_string);
            $result_string = str_replace([' ', ' '], ['', ''], $result_string);
            preg_match("/[0-9,]+/", $result_string, $result);
            $count_result = (int) $result[0];
        }
        $this->info($count_result);
        die;
    }
}
