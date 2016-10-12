<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

use App\GoogleCheckPhone;
use App\Jobs\PushEmailForCheckingScore;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    //\Log::debug('open main');
    $site_company = \App\ImportInfo::where(['type' => 'find_company_site'])->get()->sortByDesc('id')->toArray();
    $detected_phone = \App\ImportInfo::where(['type' => 'detected_phone'])->get()->sortByDesc('id')->toArray();
    $detected_email = \App\ImportInfo::where(['type' => 'email_checker'])->get()->sortByDesc('id')->toArray();
    $linkedin = \App\ImportInfo::where(['type' => 'linkedin'])->get()->sortByDesc('id')->toArray();

    return view('welcome', compact('site_company', 'detected_phone', 'detected_email', 'linkedin'));
});

Route::post('/mapping_linkedin', function (Illuminate\Http\Request $request) {

    $file = $request->file('import');

    $file->storeAs('/public/', $file->getFilename());

    $excel = Maatwebsite\Excel\Facades\Excel::load($file->getRealPath())->get()->toArray();

    $header = array_keys(array_shift($excel));

    $importInfo = \App\ImportInfo::create([
        'name' => $file->getClientOriginalName(),
        'total_row' => count($excel),
        'file_name' => $file->getFilename(),
        'type' => 'linkedin'
    ]);

    $url = '/detected_linkedin';

    return view('mapper_linkedin', compact('header', 'importInfo', 'url'));
});

Route::post('/mapping_phone', function (Illuminate\Http\Request $request) {
    //\Log::debug('open mapping_phone');

    $file = $request->file('import');

    $file->storeAs('/public/', $file->getFilename());

    $excel = Maatwebsite\Excel\Facades\Excel::load($file->getRealPath())->get()->toArray();

    $header = array_keys(array_shift($excel));

    $importInfo = \App\ImportInfo::create([
        'name' => $file->getClientOriginalName(),
        'total_row' => count($excel),
        'file_name' => $file->getFilename(),
        'type' => 'detected_phone'
    ]);

    $url = '/detected_phone';

    return view('mapper_phone', compact('header', 'importInfo', 'url'));
});

Route::post('/mapping_company', function (Illuminate\Http\Request $request) {

    $file = $request->file('import');

    $file->storeAs('/public/', $file->getFilename());

    $excel = Maatwebsite\Excel\Facades\Excel::load($file->getRealPath())->get()->toArray();

    $header = array_keys(array_shift($excel));

    $importInfo = \App\ImportInfo::create([
        'name' => $file->getClientOriginalName(),
        'total_row' => count($excel),
        'file_name' => $file->getFilename(),
        'type' => 'find_company_site'
    ]);

    $url = '/detected_site';

    return view('mapper_company', compact('header', 'importInfo', 'url'));
});


Route::post('/mapping', function (Illuminate\Http\Request $request) {

    $file = $request->file('import');

    $file->storeAs('/public/', $file->getFilename());

    $excel = Maatwebsite\Excel\Facades\Excel::load($file->getRealPath())->get()->toArray();

    $header = array_keys(array_shift($excel));
    
    $importInfo = \App\ImportInfo::create([
        'name' => $file->getClientOriginalName(),
        'total_row' => count($excel),
        'file_name' => $file->getFilename(),
        'type' => 'email_checker'
    ]);

    $url = '/create_jobs';

    return view('mapper', compact('header', 'importInfo', 'url'));
});

Route::post('/detected_site', function (){

    $importInfo = \App\ImportInfo::where([
        'id' => \Illuminate\Support\Facades\Input::get('import_id')
    ])->first();

    $path_file = 'storage/app/public/' . $importInfo->file_name;

    $excelData = Maatwebsite\Excel\Facades\Excel::load($path_file, function($reader){
        $reader->noHeading();
    })->get()->toArray();


    $header = array_shift($excelData);

    foreach(array_chunk($excelData, 200) as $arrayData){

        foreach ($arrayData as $itemData){
            $itemData = array_values($itemData);

            $companyName = $itemData[\Illuminate\Support\Facades\Input::get('field_company_name')];
            $companyName = trim(str_replace('_', '', $companyName));
            $site = '';

            $detectedCompanySite = \App\DetectedSiteCompany::where(['company_name' => $companyName]);

            if(!$detectedCompanySite->count()){

                \App\DetectedSiteCompany::create([
                    'company_name' => $companyName,
                    'import_id' => $importInfo->id
                ]);

            } else {
                $site = $detectedCompanySite->first()->site;
            }

            \App\DataComparison::create([
                'import_id' => $importInfo->id,
                'row_data' => array_values($itemData),
                'company_name' => $companyName,
                'site' => $site,
                'name' => ''
            ]);
        }

    }

    return redirect('/results/company_name/'.$importInfo->id);

});

Route::post('/detected_phone', function (){
    //\Log::debug('open detected_phone');

    $importInfo = \App\ImportInfo::where([
        'id' => \Illuminate\Support\Facades\Input::get('import_id')
    ])->first();

    $path_file = 'storage/app/public/' . $importInfo->file_name;

    $excelData = Maatwebsite\Excel\Facades\Excel::load($path_file, function($reader){
        $reader->noHeading();
    })->get()->toArray();


    $header = array_shift($excelData);

    foreach(array_chunk($excelData, 200) as $arrayData){

        //\Log::debug('Push '.count($arrayData).' phone for import ' . json_encode($arrayData));

        dispatch((new \App\Jobs\ImportFileInBackground([
            'data' => $arrayData,
            'type_import' => 'phone',
            'input' => \Illuminate\Support\Facades\Input::get(),
        ]))->onQueue('import_file'));
    }

    return redirect('/results/phone/'.$importInfo->id);

});

Route::post('/detected_linkedin', function (){
    //\Log::debug('open detected_phone');

    $importInfo = \App\ImportInfo::where([
        'id' => \Illuminate\Support\Facades\Input::get('import_id')
    ])->first();

    $path_file = 'storage/app/public/' . $importInfo->file_name;

    $excelData = Maatwebsite\Excel\Facades\Excel::load($path_file, function($reader){
        $reader->noHeading();
    })->get()->toArray();

    $header = array_shift($excelData);

    foreach($excelData as $arrayData){
        $arrayData = array_values($arrayData);

        $itemLinkedin = \App\CheckLinkedin::create([
            'import_id' => \Illuminate\Support\Facades\Input::get('import_id'),
            'site' => $arrayData[\Illuminate\Support\Facades\Input::get('field_name')],
            'title' => $arrayData[\Illuminate\Support\Facades\Input::get('field_title')],
            'company_name' => $arrayData[\Illuminate\Support\Facades\Input::get('field_company_name')],
            'provider' => 'google'
        ]);

        dispatch(
            (new \App\Jobs\LinkedinFinder([
                'id' => $itemLinkedin->id,
                'import_id' => \Illuminate\Support\Facades\Input::get('import_id'),
                'site' => $arrayData[\Illuminate\Support\Facades\Input::get('field_name')],
                'title' => $arrayData[\Illuminate\Support\Facades\Input::get('field_title')],
                'company_name' => $arrayData[\Illuminate\Support\Facades\Input::get('field_company_name')],
            ]))->onQueue('linkedin')
        );

    }

    return redirect('/results/linkedin/'.$importInfo->id);

});
//detected_linkedin

Route::post('/create_jobs', function (Illuminate\Http\Request $request){

    $importInfo = \App\ImportInfo::where([
        'id' => \Illuminate\Support\Facades\Input::get('import_id')
    ])->first();

    $path_file = 'storage/app/public/' . $importInfo->file_name;

    $excelData = Maatwebsite\Excel\Facades\Excel::load($path_file, function($reader){
        $reader->noHeading();
    })->get()->toArray();

    $header = array_shift($excelData);

    foreach(array_chunk($excelData, 200) as $arrayData){
        dispatch((new \App\Jobs\ImportFileInBackground([
            'data' => $arrayData,
            'type_import' => 'email',
            'input' => \Illuminate\Support\Facades\Input::get(),
        ]))->onQueue('import_file'));
    }

    return redirect('/results/'.$importInfo->id);
});

Route::get('/results/linkedin/{id}', function (Illuminate\Http\Request $request, $id){
    $info = \App\ImportInfo::where(['id' => $id])->first();

    if($info->type == 'detected_phone'){ return redirect('/results/phone/'.$id); }
    if($info->type == 'email_checker'){ return redirect('/results/'.$id); }

    $companySuccess = \App\CheckLinkedin::where('link', '!=', 'false')->where('link', '!=', '')->where(['import_id' => $id]);
    $companyBad = \App\CheckLinkedin::where(['import_id' => $id])->where('link', '=', 'false');
    $companyQueue = \App\CheckLinkedin::whereNull('link')->where(['import_id' => $id]);

    $type_report = \Illuminate\Support\Facades\Input::get('type');

    if($type_report == 'bad'){

        $companyBad = $companyBad->select(['title', 'company_name', 'link', 'full_name'])->get();
        return Maatwebsite\Excel\Facades\Excel::create('Bad - linkedin list ' . $info->name, function($excel) use ($companyBad){
            $excel->sheet('Sheetname', function($sheet) use ($companyBad){
                foreach ($companyBad as $item){
                    $sheet->appendRow($item->toArray());
                }
            });
        })->export('csv');

    } elseif($type_report == 'success'){

        $companySuccess = $companySuccess->select(['title', 'company_name', 'link', 'full_name'])->get();
        return Maatwebsite\Excel\Facades\Excel::create('Success - linkedin list ' . $info->name, function($excel) use ($companySuccess){
            $excel->sheet('Sheetname', function($sheet) use ($companySuccess){
                foreach ($companySuccess as $item){
                    $sheet->appendRow($item->toArray());
                }
            });
        })->export('csv');

    } else {
        return view('report_linkedin', compact('id', 'companySuccess', 'companyBad', 'companyQueue'));
    }

});

Route::get('/results/company_name/{id}', function (Illuminate\Http\Request $request, $id){

    $info = \App\ImportInfo::where(['id' => $id])->first();

    if($info->type == 'detected_phone'){ return redirect('/results/phone/'.$id); }
    if($info->type == 'email_checker'){ return redirect('/results/'.$id); }

    $companySuccess = \App\DataComparison::where('site', '!=', 'false')->where('site', '!=', '')->where(['import_id' => $id]);
    $companyBad = \App\DataComparison::where(['import_id' => $id])->where('site', '=', 'false');
    $companyQueue = \App\DetectedSiteCompany::whereNull('site')->where(['import_id' => $id]);

    $type_report = \Illuminate\Support\Facades\Input::get('type');

    if($type_report == 'bad'){

        $companyBad = $companyBad->get();
        return Maatwebsite\Excel\Facades\Excel::create('Bad - company site ' . $info->name, function($excel) use ($companyBad){
            $excel->sheet('Sheetname', function($sheet) use ($companyBad){
                foreach ($companyBad as $item){

                    $array = (array) $item->row_data;
                    $array[] = '';
                    $sheet->appendRow($array);
                }
            });
        })->export('csv');

    } elseif($type_report == 'success'){

        $companySuccess = $companySuccess->get();
        return Maatwebsite\Excel\Facades\Excel::create('Success - company site ' . $info->name, function($excel) use ($companySuccess){
            $excel->sheet('Sheetname', function($sheet) use ($companySuccess){
                foreach ($companySuccess as $item){
                    $array = (array) $item->row_data;
                    $array[] = $item->site;
                    $sheet->appendRow($array);
                }
            });
        })->export('csv');

    } else {
        return view('report_site', compact('id', 'companySuccess', 'companyBad', 'companyQueue'));
    }

});

Route::get('/results/phone/{id}', function (Illuminate\Http\Request $request, $id){

    $info = \App\ImportInfo::where(['id' => $id])->first();

    if($info->type == 'email_checker'){ return redirect('/results/'.$id); }
    if($info->type == 'find_company_site'){ return redirect('/results/company_name/'.$id); }

    $phoneSuccess = \App\DataComparison::where('phone', '!=', '0')->where(['import_id' => $id]);
    $phoneBad = \App\DataComparison::where(['import_id' => $id])->where('phone', '=', '0');
    $phoneQueue = \App\DataComparison::whereNull('phone')->where(['import_id' => $id]);

    $type_report = \Illuminate\Support\Facades\Input::get('type');
    $data_source = \Illuminate\Support\Facades\Input::get('data_source');



    if($type_report == 'bad'){

        $phoneBad = $phoneBad->get();
        return Maatwebsite\Excel\Facades\Excel::create('Bad - company phone ' . $info->name, function($excel) use ($phoneBad){
            $excel->sheet('Sheetname', function($sheet) use ($phoneBad){
                foreach ($phoneBad as $item){

                    $array = (array) $item->row_data;
                    $array[] = $item->phone;

                    $sheet->appendRow($array);
                }
            });
        })->export('csv');

    } elseif($type_report == 'success'){

        $phoneSuccess = $phoneSuccess->get();
        return Maatwebsite\Excel\Facades\Excel::create('Success - company phone ' . $info->name, function($excel) use ($phoneSuccess){
            $excel->sheet('Sheetname', function($sheet) use ($phoneSuccess){
                foreach ($phoneSuccess as $item){

                    $array = (array) $item->row_data;
                    $array[] = $item->phone;

                    $sheet->appendRow($array);
                }
            });
        })->export('csv');

    } else {
        return view('report_phone', compact('id', 'phoneSuccess', 'phoneBad', 'phoneQueue'));
    }

});

Route::get('/results/{id}', function (Illuminate\Http\Request $request, $id){

    $info = \App\ImportInfo::where(['id' => $id])->first();
    if($info->type == 'detected_phone'){ return redirect('/results/phone/'.$id); }
    if($info->type == 'find_company_site'){ return redirect('/results/company_name/'.$id); }

    $checkEmailInGoogle = \App\GoogleCheckEmail::where(['import_id' => $id])->whereNull('count_results')->get()->pluck('email','data_comparasion_id');

    $success = \App\DataComparison::where('score', '>', 0)->where(['import_id' => $id]);

    $bad = \App\DataComparison::where(['import_id' => $id])
        ->whereNotIn('id', array_keys($checkEmailInGoogle->toArray()))
        ->where('email', '=', '0');

    $queue = \App\DataComparison::whereNull('score')->where(['import_id' => $id]);

    $phoneSuccess = \App\GoogleCheckPhone::where('phone', '!=', '0')->where(['import_id' => $id]);
    $phoneBad = \App\GoogleCheckPhone::where(['import_id' => $id])->where('phone', '=', '0');
    $phoneQueue = \App\GoogleCheckPhone::whereNull('phone')->where(['import_id' => $id]);

    $type_report = \Illuminate\Support\Facades\Input::get('type');
    $data_source = \Illuminate\Support\Facades\Input::get('data_source');

    if($type_report == 'bad'){


        switch ($data_source){
            case 'email':
                $bad = $bad->get();
                return Maatwebsite\Excel\Facades\Excel::create('Bad - ' . $info->name, function($excel) use ($bad){
                    $excel->sheet('Sheetname', function($sheet) use ($bad){
                        foreach ($bad as $item){
                            $array = (array) $item->row_data;

                            $array[] = $item->email;
                            $array[] = $item->phone;
                            $array[] = $item->score;
                            $sheet->appendRow($array);
                        }
                    });
                })->export('csv');
                break;
            case 'phone_company':

                $phoneBad = $phoneBad->get();
                return Maatwebsite\Excel\Facades\Excel::create('Bad - company phone ' . $info->name, function($excel) use ($phoneBad){
                    $excel->sheet('Sheetname', function($sheet) use ($phoneBad){
                        foreach ($phoneBad as $item){

                            $fullData = \App\DataComparison::where(['id' => $item->data_comparasion_id])->first();
                            $array = (array) $fullData->row_data;

                            $array[] = $item->phone;
                            $sheet->appendRow($array);
                        }
                    });
                })->export('csv');

                break;
        }

    } elseif($type_report == 'success'){

        switch ($data_source) {
            case 'email':
                $success = $success->get();
                return Maatwebsite\Excel\Facades\Excel::create('Success - ' . $info->name, function($excel) use ($success){
                    $excel->sheet('Sheetname', function($sheet) use ($success){
                        foreach ($success as $item){
                            $array = (array) $item->row_data;
                            $array[] = $item->email;
                            $array[] = $item->phone;
                            $array[] = $item->score;
                            $sheet->appendRow($array);
                        }
                    });
                })->export('csv');
                break;
            case 'phone_company':
                $phoneSuccess = $phoneSuccess->get();
                return Maatwebsite\Excel\Facades\Excel::create('Success - company phone ' . $info->name, function($excel) use ($phoneSuccess){
                    $excel->sheet('Sheetname', function($sheet) use ($phoneSuccess){
                        foreach ($phoneSuccess as $item){

                            $fullData = \App\DataComparison::where(['id' => $item->data_comparasion_id])->first();
                            $array = (array) $fullData->row_data;

                            $array[] = $item->phone;
                            $sheet->appendRow($array);
                        }
                    });
                })->export('csv');

                break;
        }

    } else {
        return view('report', compact('success', 'bad', 'queue', 'id', 'phoneSuccess', 'phoneBad', 'phoneQueue', 'checkEmailInGoogle'));
    }

});