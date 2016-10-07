<?php

namespace App\Console\Commands;

use App\DataComparison;
use App\GoogleCheckEmail;
use App\Jobs\PushEmailForCheckingScore;
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
        $import_id = $this->argument('import_id');

        $allDataComparison = DataComparison::where(['import_id' => $import_id, 'email' => 0])->select(['id', 'name', 'site'])->first();


        $allVariableEmailName = \App\DataComparison::getVariableEmailName($allDataComparison->name, $allDataComparison->site);

        foreach($allVariableEmailName as $nameEmail){
            GoogleCheckEmail::create([
                'import_id' => $allDataComparison->import_id,
                'email' => $nameEmail,
                'data_comparasion_id' => $allDataComparison->id
            ]);
        }

        $this->info('Start push in queue. Total ' . count($allDataComparison));

        $bar = $this->output->createProgressBar(count($allDataComparison));

        foreach ($allDataComparison as $key => $item){

            dispatch(
                (new PushEmailForCheckingScore([
                    'data_id' => $item->id,
                    'name' => $item->name,
                    'domain' => $item->site
                ]))->onQueue('default')
            );

            $bar->advance();
            unset($allDataComparison[$key]);
        }
    }
}
