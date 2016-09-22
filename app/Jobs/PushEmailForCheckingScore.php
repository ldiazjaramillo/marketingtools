<?php

namespace App\Jobs;

use App\DataComparison;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class PushEmailForCheckingScore implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $data = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $name = $this->data['name'];
        $domain = $this->data['domain'];
        $data_id = $this->data['data_id'];

        $variableName = \App\DataComparison::getVariableEmailName($name);

        $result = [];
        $emails = [];

        foreach($variableName as $name){

            $email = $name.'@'.$domain;

            $request = json_decode(file_get_contents('https://apilayer.net/api/check?access_key='.env('MAILBOX_API_KEY').'&email=' . $email . '&smtp=1&format=1'), 1);

            $score = $request['score']*100;

            Log::info('Get score for '.$email . ' ' . $score);

            $result[$score] = $email;
            $emails[] = $email;

            if($score >= 85 && $request['catch_all'] == false){

                $keys = array_keys($result);

                $score = max($keys);
                $email = $result[$score];
                
                Log::notice('Select best score for ' . $email.' '.$score);
                Log::notice('JSON ' . json_encode($result));

                DataComparison::where(['id' => $data_id])->update([
                    'score' => $score,
                    'email' => $email
                ]);

                return true;
            }

        }

        Log::warning('All score equal. Brute force email failed');
        DataComparison::where(['id' => $data_id])->update([
            'score' => 0,
            'email' => false
        ]);

    }
}
