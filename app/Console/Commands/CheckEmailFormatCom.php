<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class CheckEmailFormatCom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:emailFormatCom {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $site = $this->argument('site');
        $site = str_replace(['http://', 'https://', 'www.'], ['','',''], $site);
        $site = parse_url($site);
        $site = $site['path'];

        $client = new Client([
            'base_uri' => 'http://www.email-format.com/d/'
        ]);

        $result = $client->get($site)->getBody()->getContents();

        $checkMediumConfidence = strpos($result, 'medium confidence');

        if($checkMediumConfidence !== false){
            $crawler = new Crawler($result);
            $first = $crawler->filter('.format.fl')->first();
            $pattern = str_replace(' ', '', $first->text());
            $this->line($pattern);
        } else {
            $this->line('Not found info');
        }

    }
}
