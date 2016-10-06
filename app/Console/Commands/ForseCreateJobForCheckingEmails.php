<?php

namespace App\Console\Commands;

use App\DataComparison;
use App\Jobs\PushEmailForCheckingScore;
use Illuminate\Console\Command;

class ForseCreateJobForCheckingEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:forceEmailChecker {import_id}';

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

        $allDataComparison = DataComparison::where(['import_id' => $import_id])->whereNull('email')->select(['id', 'name', 'site'])->get();

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
