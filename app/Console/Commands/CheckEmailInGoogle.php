<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Serps\Core\Http\Proxy;

class CheckEmailInGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:emailInGoogle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check email in Google';

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

        

    }
}
