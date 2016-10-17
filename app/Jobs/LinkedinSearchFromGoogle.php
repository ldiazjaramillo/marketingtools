<?php

namespace App\Jobs;

use App\LinkedinFromGoogle;
use App\LinkedinParserSession;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LinkedinSearchFromGoogle implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $id = 0;
    protected $page = null;
    protected $query = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $query, $page)
    {
        $this->id = $id;
        $this->query = $query;
        $this->page = $page;
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

            $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
            $googleClient->request->setUserAgent($userAgent);

            $googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();
            $googleUrl->setSearchTerm('site:linkedin.com/in/ AND '.$this->query)->setPage($this->page);

            $response = $googleClient->query($googleUrl);

            $resultObject = $response->getDom()->getElementById('resultStats');

            $results = $response->getNaturalResults();

            $i = 0;
            foreach($results as $itemResult){
                $itemResult = $itemResult->getData();
                $fullname = explode(' | ', $itemResult['title']);

                try {
                    $descriptionJob = $response->cssQuery('.slp')->item($i)->textContent;
                    $tmpJob = explode(' - ', $descriptionJob);
                    if(count($tmpJob) == 2){
                        $title = $tmpJob[0];
                        $company_name = $tmpJob[1];
                    } elseif(count($tmpJob) > 2){
                        $title = $tmpJob[1];
                        $company_name = $tmpJob[2];
                    }
                } catch (\Exception $e){
                    $title = '';
                    $company_name = '';
                }

                LinkedinFromGoogle::create([
                    'import_id' => $this->id,
                    'site' => 'linkedin.com/in/',
                    'provider' => 'google',
                    'link' => $itemResult['url'],
                    'full_name' => $fullname[0],
                    'title' => $title,
                    'company_name' => $company_name,
                    'string_linkedin' => $descriptionJob
                ]);

                $i++;
            }

            $parserSession = LinkedinParserSession::where([
                'id' => $this->id
            ])->first();

            $parserSession->total_results = $parserSession->total_results + count($results);
            $parserSession->save();

        } catch (\Exception $e){
            var_dump($e->getMessage());
            var_dump($e->getLine());
            var_dump($e->getCode());
            var_dump('$e->getCode()');
            die;
        }

    }
}
