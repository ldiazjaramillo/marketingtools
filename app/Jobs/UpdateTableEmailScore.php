<?php

namespace App\Jobs;

use App\DataComparison;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateTableEmailScore implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $id = 0;
    protected $score = 0;
    protected $email = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data = [])
    {
        $this->id = $data['id'];
        $this->score = $data['score'];
        $this->email = $data['email'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DataComparison::where(['id' => $this->id])->update([
            'score' => $this->score,
            'email' => $this->email
        ]);
    }
}
