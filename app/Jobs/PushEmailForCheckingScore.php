<?php

namespace App\Jobs;

use App\DataComparison;
use App\GoogleCheckEmail;
use App\LogCallApi;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
//use Log;

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


		$variableName = \App\DataComparison::getVariableEmailName($name, $domain);

		//\Log::info('Run PushEmailForCheckingScore ' . json_encode($variableName));

		$result = [];
		$emails = [];

		foreach ($variableName as $name) {

			try {
				$importInfo = DataComparison::where(['id' => $data_id])->first();
				$path_file = storage_path('app/public/' . $importInfo->import_id);

				$email = $name . '@' . $domain;

				//\Log::info('Request to apilayer for ' . $email);


				file_put_contents($path_file, "\r\n" . 'https://apilayer.net/api/check?access_key=' . env('MAILBOX_API_KEY') . '&email=' . $email . '&smtp=1&format=1&catch_all=1' . "\r\n", FILE_APPEND);
				$request = json_decode(file_get_contents('https://apilayer.net/api/check?access_key=' . env('MAILBOX_API_KEY') . '&email=' . $email . '&smtp=1&format=1&catch_all=1'), 1);


				$log = ['import_id' => $importInfo->import_id, 'data_comparasion_id' => $importInfo->id];

				$request['score'] = $request['score'] * 100;
				LogCallApi::create(array_merge($log, $request));

				file_put_contents($path_file, 'Result response ' . "\r\n\r\n" . json_encode($request) . "\r\n\r\n", FILE_APPEND);

				if (!$request['format_valid']) {
					//Log::warning('Invalid email ' . $email);
					continue;
				} else {
					$score = $request['score'];

					//Log::info('Get score for ' . $email . ' ' . $score);

					$result[$score] = $email;
					$emails[] = $email;
				}

				if ($request['catch_all'] == false && $score > 85) {

					$keys = array_keys($result);

					$score = max($keys);
					$email = $result[$score];

					//Log::notice('Select best score for ' . $email . ' ' . $score);
					//Log::notice('JSON ' . json_encode($result));

					DataComparison::where(['id' => $data_id])->update([
						'score' => $score,
						'email' => $email
					]);

					return true;

				} elseif ($request['catch_all'] == true) {

					//Log::warning('Catch all for ' . $email . ' score ' . $score);

					DataComparison::where(['id' => $data_id])->update([
						'score' => 0,
						'email' => false
					]);

					//Create queue for checking google email
					$allVariantEmail = \App\DataComparison::getVariableEmailName($importInfo->name, $domain);

					foreach ($allVariantEmail as $nameForBadEmail) {
						GoogleCheckEmail::create([
							'import_id' => $importInfo->import_id,
							'email' => $nameForBadEmail . '@' . $domain,
							'data_comparasion_id' => $data_id
						]);
					}

					dispatch(
						(new GoogleEmailChecker([
							'name' => $importInfo->name,
							'domain' => $domain,
							'data_comparasion_id' => $data_id,
						]))->onQueue('email_checker_in_google')
					);

					return false;
				}


			} catch (\Exception $e) {
				//\Log::info('Failed request to apilayer.net ' . 'https://apilayer.net/api/check?access_key=' . env('MAILBOX_API_KEY') . '&email=' . $email . '&smtp=1&format=1&catch_all=1');
				Bugsnag::notifyException($e);

                $allVariantEmail = \App\DataComparison::getVariableEmailName($importInfo->name, $domain);

                foreach ($allVariantEmail as $nameForBadEmail) {
                    GoogleCheckEmail::create([
                        'import_id' => $importInfo->import_id,
                        'email' => $nameForBadEmail . '@' . $domain,
                        'data_comparasion_id' => $data_id
                    ]);
                }

                dispatch(
                    (new GoogleEmailChecker([
                        'name' => $importInfo->name,
                        'domain' => $domain,
                        'data_comparasion_id' => $data_id,
                    ]))->onQueue('email_checker_in_google')
                );

			}

		}

        $allVariantEmail = \App\DataComparison::getVariableEmailName($importInfo->name, $domain);

        foreach ($allVariantEmail as $nameForBadEmail) {
            GoogleCheckEmail::create([
                'import_id' => $importInfo->import_id,
                'email' => $nameForBadEmail . '@' . $domain,
                'data_comparasion_id' => $data_id
            ]);
        }

        dispatch(
            (new GoogleEmailChecker([
                'name' => $importInfo->name,
                'domain' => $domain,
                'data_comparasion_id' => $data_id,
            ]))->onQueue('email_checker_in_google')
        );

		//Log::warning('All score equal. Brute force email failed');
		DataComparison::where(['id' => $data_id])->update([
			'score' => 0,
			'email' => false
		]);

	}
}
