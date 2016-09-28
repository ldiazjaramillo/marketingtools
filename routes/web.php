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

Route::get('/', function () {
    return view('welcome');
});


Route::post('/mapping', function (Illuminate\Http\Request $request) {

    $file = $request->file('import');

    $file->storeAs('/public/', $file->getFilename());

    $excel = Maatwebsite\Excel\Facades\Excel::load($file->getRealPath())->get()->toArray();

    $header = array_keys(array_shift($excel));
    
    $importInfo = \App\ImportInfo::create([
        'name' => $file->getClientOriginalName(),
        'total_row' => count($excel),
        'file_name' => $file->getFilename()
    ]);

    return view('mapper', compact('header', 'importInfo'));
});


Route::post('/create_jobs', function (Illuminate\Http\Request $request){

    $importInfo = \App\ImportInfo::where([
        'id' => \Illuminate\Support\Facades\Input::get('import_id')
    ])->first();

    $path_file = 'storage/app/public/' . $importInfo->file_name;

    $excelData = Maatwebsite\Excel\Facades\Excel::load($path_file, function($reader){
        $reader->noHeading();
    })->get()->toArray();

    $header = array_shift($excelData);

    foreach ($excelData as $line){
        $data = array_values($line);

        $host = str_replace(['http://', '//', 'www.'], ['','', ''], strtolower($data[\Illuminate\Support\Facades\Input::get('field_site')]));

        $url = parse_url('//'.$host);

        $company_name = $data[\Illuminate\Support\Facades\Input::get('field_company_name')];

        $dataItem = \App\DataComparison::create([
            'import_id' => \Illuminate\Support\Facades\Input::get('import_id'),
            'name' => $data[\Illuminate\Support\Facades\Input::get('field_name')],
            'company_name' => $company_name,
            'site' => $url['host'],
            'row_data' => $data,
        ]);

        if(!empty($company_name) && GoogleCheckPhone::where(['company_name' => $company_name, 'import_id' => $dataItem->import_id])->count() == 0){
            GoogleCheckPhone::create([
                'import_id' => $dataItem->import_id,
                'site' => (empty($url['host'])? '' : $url['host']),
                'company_name' => $company_name,
                'data_comparasion_id' => $dataItem->id
            ]);
        }

    }

    return redirect('/results/'.$importInfo->id);
});


Route::get('/results/{id}', function (Illuminate\Http\Request $request, $id){

    $checkEmailInGoogle = \App\GoogleCheckEmail::where(['import_id' => $id])->whereNull('count_results')->get()->pluck('email','data_comparasion_id');

    $success = \App\DataComparison::where('score', '>', 0)->where(['import_id' => $id]);

    $bad = \App\DataComparison::where(['import_id' => $id])
        ->whereNotIn('id', array_keys($checkEmailInGoogle->toArray()))
        ->where('email', '=', '0');

    $queue = \App\DataComparison::whereNull('score')->where(['import_id' => $id]);

    $phoneSuccess = \App\GoogleCheckPhone::where('phone', '>', '0')->where(['import_id' => $id]);
    $phoneBad = \App\GoogleCheckPhone::where(['import_id' => $id])->where('phone', '=', '0');
    $phoneQueue = \App\GoogleCheckPhone::whereNull('phone')->where(['import_id' => $id]);

    $type_report = \Illuminate\Support\Facades\Input::get('type');
    $data_source = \Illuminate\Support\Facades\Input::get('data_source');

    $info = \App\ImportInfo::where(['id' => $id])->first();

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
                            $array = (array) $item->row_data;
                            $array[] = $item->site;
                            $array[] = $item->company_name;
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
                            $array = (array) $item->row_data;
                            $array[] = $item->site;
                            $array[] = $item->company_name;
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