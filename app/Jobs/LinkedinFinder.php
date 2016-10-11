<?php

namespace App\Jobs;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Serps\Core\Http\Proxy;

class LinkedinFinder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $import_id;
    protected $site;
    protected $title;
    protected $company_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->import_id = $data['import_id'];
        $this->site = trim($data['site']);
        $this->title = trim($data['title']);
        $this->company_name = trim($data['company_name']);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {

            $query = "site:{$this->site} AND \"{$this->title}\" AND \"{$this->company_name}\"";
            $googleClient = new \Serps\SearchEngine\Google\GoogleClient(new \Serps\HttpClient\CurlClient());

            $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
            $googleClient->request->setUserAgent($userAgent);

            $googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();
            $googleUrl->setSearchTerm($query);

            $proxy = new Proxy(env('PROXY_HOST', '37.48.118.90'), env('PROXY_PORT', '13012'));
            $response = $googleClient->query($googleUrl, $proxy);

            $resultObject = $response->getDom()->getElementById('resultStats');

            $count_result = 0;

            if (!empty($resultObject->firstChild->data)) {
                $result_string = $resultObject->firstChild->data;
                $result_string = str_replace([' ', ' '], ['', ''], $result_string);
                preg_match("/[0-9,]+/", $result_string, $result);
                $count_result = (int)$result[0];
            }

            $firstResult = $response->getNaturalResults()->getItems(0)[0]->getData();

            $fullname = explode(' | ', $firstResult['title']);

            \App\CheckLinkedin::where(['id' => $this->id])->update([
                'link' => $firstResult['url'],
                'full_name' => $fullname[0],
                'string_query' => $query
            ]);

        } catch (\Exception $e){

            \App\CheckLinkedin::where(['id' => $this->id])->update([
                'link' => 'false',
            ]);

            Bugsnag::notifyException($e);

        }
    }
}
