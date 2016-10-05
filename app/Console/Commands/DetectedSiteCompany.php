<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DetectedSiteCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:companyInGoogle {company_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find site by company name';

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

        $googleUrl->setSearchTerm($company_name . ' company');

        $proxy = new Proxy(env('PROXY_HOST', '37.48.118.90'), env('PROXY_PORT', '13012'));
        $response = $googleClient->query($googleUrl, $proxy);

        $results = $response->getNaturalResults()->getItems();

        if(!empty($results)){
            $first = $results[0]->getData();

            $firstUrl = parse_url($first['url']);
            $firstUrl = $firstUrl['host'];

        } else {
            $firstUrl = 'false';
        }

        $this->info($firstUrl);

    }
}
