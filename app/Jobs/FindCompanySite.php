<?php

namespace App\Jobs;

use App\DataComparison;
use App\DetectedSiteCompany;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Serps\Core\Http\Proxy;

class FindCompanySite implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    protected $company_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $initData)
    {
        $this->setId($initData['id']);
        $this->setCompanyname($initData['company_name']);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $googleClient = new \Serps\SearchEngine\Google\GoogleClient(new \Serps\HttpClient\CurlClient());

        $company_name = $this->getCompanyname();

        // Tell the client to use a user agent
        $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
        $googleClient->request->setUserAgent($userAgent);

        $googleUrl = new \Serps\SearchEngine\Google\GoogleUrl();

        $googleUrl->setSearchTerm($company_name . ' company');

        $proxy = new Proxy(env('PROXY_HOST', '37.48.118.90'), env('PROXY_PORT', '13012'));
        $response = $googleClient->query($googleUrl, $proxy);

        if($response->cssQuery('.r a')->length > 0){
            $firstUrl = parse_url($response->cssQuery('.r a')->item(0)->getAttribute('href'));
            $firstUrl = str_replace('www.', '', $firstUrl['host']);
        } else {
            $firstUrl = 'false';
        }

        DataComparison::where(['company_name' => $company_name])->update(['site' => $firstUrl]);
        DetectedSiteCompany::where(['id' => $this->getId()])->update(['site' => $firstUrl]);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return FindCompanySite
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompanyname()
    {
        return $this->company_name;
    }

    /**
     * @param mixed $company_name
     * @return FindCompanySite
     */
    public function setCompanyname($company_name)
    {
        $this->company_name = $company_name;
        return $this;
    }


}
