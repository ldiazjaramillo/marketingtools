<?php

namespace App\Jobs;

use App\GoogleCheckPhone;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Input;
use Log;

class ImportFileInBackground implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Input
     */
    protected $input = null;

    /**
     * @var array
     */
    protected $data = null;

    /**
     * @var null
     */
    protected $type_import = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $initData)
    {
        $this->setInput($initData['input']);
        $this->setData($initData['data']);
        $this->setTypeImport($initData['type_import']);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $excelData = $this->data;

        foreach ($excelData as $line) {
            $data = array_values($line);

            $host = str_replace(['http://', '//', 'www.'], ['', '', ''], strtolower($data[$this->getInput('field_site')]));

            $url = parse_url('//' . $host);

            $company_name = $data[$this->getInput('field_company_name')];

            try {

                $dataComparation = [
                    'import_id' => $this->getInput('import_id'),
                    'name' => $data[$this->getInput('field_name')],
                    'company_name' => $company_name,
                    'site' => $url['host'],
                    'row_data' => $data,
                ];

                if($this->getTypeImport() == 'phone'){
                    $dataComparation['email'] = '0';
                }

                $dataItem = \App\DataComparison::create($dataComparation);
                
            } catch (\Exception $e){
                Log::debug('Don\'t can import contact');
                Log::debug(json_encode($data));
            }

            if (!empty($company_name) && GoogleCheckPhone::where(['company_name' => $company_name, 'import_id' => $dataItem->import_id])->count() == 0) {
                GoogleCheckPhone::create([
                    'import_id' => $dataItem->import_id,
                    'site' => (empty($url['host']) ? '' : $url['host']),
                    'company_name' => $company_name,
                    'data_comparasion_id' => $dataItem->id
                ]);
            }

        }
    }

    /**
     * @param string $name
     * @return Input
     */
    public function getInput($name = '')
    {
        return isset($this->input[$name])? $this->input[$name] : '';
    }

    /**
     * @param Input $input
     * @return ImportFileInBackground
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return ImportFileInBackground
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return null
     */
    public function getTypeImport()
    {
        return $this->type_import;
    }

    /**
     * @param null $type_import
     * @return ImportFileInBackground
     */
    public function setTypeImport($type_import)
    {
        $this->type_import = $type_import;
        return $this;
    }

}
