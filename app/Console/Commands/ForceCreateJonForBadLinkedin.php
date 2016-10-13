<?php

namespace App\Console\Commands;

use App\CheckLinkedin;
use App\Jobs\LinkedinFinder;
use Illuminate\Console\Command;

class ForceCreateJonForBadLinkedin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:forceLinkedinChecker {import_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check again Linkedin failed contact';

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

        $allLinkedinContact = CheckLinkedin::where([
            'import_id' => $import_id,
            'link' => 'false'
        ])->get();

        $this->info('Start push in queue. Total ' . count($allLinkedinContact));

        $bar = $this->output->createProgressBar(count($allLinkedinContact));

        foreach ($allLinkedinContact as $key => $item){

            $item->link = NULL;
            $item->save();

            dispatch(
                (new \App\Jobs\LinkedinFinder([
                    'id' => $item->id,
                    'import_id' => $item->import_id,
                    'site' => $item->site,
                    'title' => $item->title,
                    'company_name' => $item->company_name,
                ]))->onQueue('linkedin')
            );

            $bar->advance();
            unset($allLinkedinContact[$key]);
        }
    }
}
