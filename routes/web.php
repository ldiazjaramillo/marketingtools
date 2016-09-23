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

use App\Jobs\PushEmailForCheckingScore;

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

        $url = parse_url('//'.$data[\Illuminate\Support\Facades\Input::get('field_site')]);

        \App\DataComparison::create([
            'import_id' => \Illuminate\Support\Facades\Input::get('import_id'),
            'name' => $data[\Illuminate\Support\Facades\Input::get('field_name')],
            'site' => $url['host'],
            'row_data' => $data,
        ]);
    }

    $dataComparison = \App\DataComparison::where([
        'import_id' => $importInfo->id
    ])->get();

    foreach($dataComparison as $dataItem){

        if(empty($dataItem->site)){
            $dataItem->email = false;
            $dataItem->score = 0;
            $dataItem->save();
            continue;
        }

        dispatch(
            (new PushEmailForCheckingScore([
                'data_id' => $dataItem->id,
                'name' => $dataItem->name,
                'domain' => $dataItem->site
            ]))->onQueue('default')
        );
    }

    return redirect('/results/'.$importInfo->id);
});


Route::get('/results/{id}', function (Illuminate\Http\Request $request, $id){

    $success = \App\DataComparison::where('score', '>', 0)->where(['import_id' => $id]);
    $bad = \App\DataComparison::where(['import_id' => $id])->where('email', '=', '0');
    $queue = \App\DataComparison::whereNull('score')->where(['import_id' => $id]);

    $type_report = \Illuminate\Support\Facades\Input::get('type');

    $info = \App\ImportInfo::where(['id' => $id])->first();

    if($type_report == 'bad'){
        $bad = $bad->get();
        return Maatwebsite\Excel\Facades\Excel::create('Bad - ' . $info->name, function($excel) use ($bad){
            $excel->sheet('Sheetname', function($sheet) use ($bad){
                foreach ($bad as $item){
                    $array = (array) $item->row_data;
                    $array[] = $item->email;
                    $array[] = $item->score;
                    $sheet->appendRow($array);
                }
            });
        })->export('csv');

    } elseif($type_report == 'success'){

        $success = $success->get();
        return Maatwebsite\Excel\Facades\Excel::create('Success - ' . $info->name, function($excel) use ($success){
            $excel->sheet('Sheetname', function($sheet) use ($success){
                foreach ($success as $item){
                    $array = (array) $item->row_data;
                    $array[] = $item->email;
                    $array[] = $item->score;
                    $sheet->appendRow($array);
                }
            });
        })->export('csv');

    } else {
        return view('report', compact('success', 'bad', 'queue'));
    }

});