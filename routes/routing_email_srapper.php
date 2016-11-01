<?php

use App\Jobs\LinkedinSearchFromGoogle;

Route::post('/mapping_email', function (Illuminate\Http\Request $request) {

    $file = $request->file('import');

    $file->storeAs('/public/', $file->getFilename());

    $excel = Maatwebsite\Excel\Facades\Excel::load($file->getRealPath())->get()->toArray();

    $header = array_keys(array_shift($excel));

    $importInfo = \App\ImportInfo::create([
        'name' => $file->getClientOriginalName(),
        'total_row' => count($excel),
        'file_name' => $file->getFilename(),
        'type' => 'only_email_checker'
    ]);

    $url = '/create_email_jobs';

    return view('mapper', compact('header', 'importInfo', 'url'));
});

Route::post('/create_email_jobs', function (Illuminate\Http\Request $request){

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
            'type_import' => 'only_email',
            'input' => \Illuminate\Support\Facades\Input::get(),
        ]))->onQueue('import_file'));
    }

    return redirect('/results_email/'.$importInfo->id);
});

Route::get('/results_email/{id}', function (Illuminate\Http\Request $request, $id){

    $info = \App\ImportInfo::where(['id' => $id])->first();
    if($info->type == 'detected_phone'){ return redirect('/results/phone/'.$id); }
    if($info->type == 'find_company_site'){ return redirect('/results/company_name/'.$id); }

    $checkEmailInGoogle = \App\GoogleCheckEmail::where(['import_id' => $id])->whereNull('count_results')->get()->pluck('email','data_comparasion_id');

    $success = \App\DataComparison::where('score', '>', 0)->where(['import_id' => $id]);

    $bad = \App\DataComparison::where(['import_id' => $id])
        ->whereNotIn('id', array_keys($checkEmailInGoogle->toArray()))
        ->where('email', '=', '0');

    $queue = \App\DataComparison::whereNull('score')->where(['import_id' => $id]);

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
        }

    } else {
        return view('report_email', compact('success', 'bad', 'queue', 'id', 'checkEmailInGoogle'));
    }

});