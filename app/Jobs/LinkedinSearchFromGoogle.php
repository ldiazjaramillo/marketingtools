<?php

namespace App\Jobs;

use App\LinkedinFromGoogle;
use App\LinkedinParserSession;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Serps\Core\Http\Proxy;

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
            $googleUrl->setSearchTerm($this->query)->setPage($this->page)->setResultsPerPage(20);

            $proxy = new Proxy(env('PROXY_HOST', '37.48.118.90'), env('PROXY_PORT', '13012'));
            $response = $googleClient->query($googleUrl, $proxy);

            $resultObject = $response->getDom()->getElementById('resultStats');

            $results = $response->getNaturalResults();

            $count_result = 0;

            if (!empty($resultObject->firstChild->data)) {
                $result_string = $resultObject->firstChild->data;
                $result_string = str_replace([' ', ' '], ['', ''], $result_string);
                preg_match("/[0-9,]+/", $result_string, $result);
                $count_result = (int)$result[0];
            }

            $parserSession = LinkedinParserSession::where([
                'id' => $this->id,
                'total_results' => 0
            ])->update(['total_results' => $count_result]);

            $i = 0;

            if(count($results) == 0){
                return true;
            }

            foreach($results as $itemResult){
                $itemResult = $itemResult->getData();
                $fullname = explode(' | ', $itemResult['title']);



                try {
                    $descriptionJob = $response->cssQuery('.slp')->item($i)->textContent;
                    $snippet = preg_replace('%[^A-Za-z0-9- ,.]%', '', $descriptionJob);

                    $snippet = explode(' - ', $snippet);

                    $tmpSnippet = explode(' at ', $snippet[1]);

                    $title = $tmpSnippet[0];
                    $company_name = $tmpSnippet[1];
                } catch (\Exception $e){
                    $title = '';
                    $company_name = '';
                    $descriptionJob = '';
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

            $parserSession->page = $parserSession->page + count($results);
            $parserSession->save();

        } catch (\Exception $e){
            Bugsnag::notifyException($e);
            var_dump($e->getMessage());
            var_dump($e->getFile());
            var_dump($e->getLine());
            var_dump($e->getCode());
            var_dump('$e->getCode()');
            die;
        }

    }
}
