<?php

namespace App\Console\Commands;

use App\DataComparison;
use App\Jobs\GooglePhoneFinder;
use Illuminate\Console\Command;

class ForceCreateJobForCheckingPhone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:forcePhoneChecker {import_id}';

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
        $phoneInQueue = \App\GoogleCheckPhone::whereNull('phone')->where(['import_id' => $import_id]);

        $count = $phoneInQueue->count();

        $this->info('Start push in queue. Total ' . $count);

        $bar = $this->output->createProgressBar($count);
        $phoneInQueue = $phoneInQueue->get();
        foreach($phoneInQueue as $key => $model){
            dispatch(
                (new GooglePhoneFinder([
                    'id' => $model->id,
                    'company_name' => $model->company_name,
                    'site' => $model->site
                ]))->onQueue('phone_finder')
            );
            $bar->advance();
            unset($phoneInQueue[$key]);
        }

    }
}
