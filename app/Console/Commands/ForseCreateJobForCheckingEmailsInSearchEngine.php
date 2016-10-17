<?php

namespace App\Console\Commands;

use App\DataComparison;
use App\GoogleCheckEmail;
use App\Jobs\GoogleEmailChecker;
use App\Jobs\PushEmailForCheckingScore;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Console\Command;

class ForseCreateJobForCheckingEmailsInSearchEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:forceEmailCheckerSearchEngine {import_id}';

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
        try{

            $import_id = $this->argument('import_id');

            $inQueue = \App\GoogleCheckEmail::whereNull('count_results')->where(['import_id' => $import_id]);
            $this->info('Remove from queue' . $inQueue->count());
            $inQueue->delete();

            $allDataComparison = DataComparison::where(['import_id' => $import_id, 'email' => 0])->select(['id', 'name', 'site', 'import_id'])->get();

            $this->info('Start push in queue. Total ' . count($allDataComparison));

            $bar = $this->output->createProgressBar(count($allDataComparison));

            foreach ($allDataComparison as $key => $itemData){

                $allVariableEmailName = \App\DataComparison::getVariableEmailName($itemData->name, $itemData->site);

                foreach($allVariableEmailName as $nameEmail){
                    GoogleCheckEmail::create([
                        'import_id' => $itemData->import_id,
                        'email' => $nameEmail,
                        'data_comparasion_id' => $itemData->id
                    ]);
                }

                dispatch(
                    (new GoogleEmailChecker([
                        'name' => $itemData->name,
                        'domain' => $itemData->site,
                        'import_id' => $itemData->import_id,
                        'data_comparasion_id' => $itemData->id,
                    ]))->onQueue('email_checker_in_google')
                );

                $bar->advance();
                //unset($allDataComparison[$key]);
            }

        } catch (\Exception $e){
            print_r($e->getFile());
            print_r($e->getLine());
            print_r($e->getMessage());
            die;
            Bugsnag::notifyException($e);
        }

    }
}
