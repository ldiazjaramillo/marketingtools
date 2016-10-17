<?php

use App\Jobs\LinkedinSearchFromGoogle;

Route::post('/scrapper_contact_linkedin', function (Illuminate\Http\Request $request){
    return view('scrapper_linkedin.create_request', compact('success', 'bad', 'queue', 'id', 'phoneSuccess', 'phoneBad', 'phoneQueue', 'checkEmailInGoogle'));
});

Route::post('/create_new_session', function (Illuminate\Http\Request $request){
    $requestString = $request->input('request_name');

    $linkedinTask = \App\LinkedinParserSession::create([
        'request' => $request->input('request_name'),
    ]);

    for($i = 0; $i<=50; $i++){
        dispatch(
            (new LinkedinSearchFromGoogle($linkedinTask->id, $linkedinTask->request, $i))->onQueue('linkedin_search')
        );
    }

    return redirect('/lists_linkedin/'.$linkedinTask->id);
});

Route::get('/lists_linkedin/{id}', function ($id){

    $linkedin = \App\LinkedinParserSession::where([
        'id' => $id,
    ])->first();



    if(count($linkedin) == 0) abort(404);

    $type_report = \Illuminate\Support\Facades\Input::get('type');

    if($type_report == 'success'){

        $companySuccess = \App\LinkedinFromGoogle::where(['import_id' => $id])->select([
            'full_name',
            'title',
            'company_name',
            'link',
            'string_linkedin'
        ])->get();

        return Maatwebsite\Excel\Facades\Excel::create('Success - linkedin list ' . $id, function($excel) use ($companySuccess){
            $excel->sheet('Sheetname', function($sheet) use ($companySuccess){
                $sheet->appendRow([
                    'name',
                    'job title',
                    'company name',
                    'linkedin profile',
                    'snippet from google'
                ]);

                foreach ($companySuccess as $item){

                    $item = $item->toArray();

                    try {
                        $snippet = preg_replace('%[^A-Za-z0-9- ,.]%', '', $item['string_linkedin']);

                        $snippet = explode(' - ', $snippet);

                        $tmpSnippet = explode(' at ', $snippet[1]);

                        $item['title'] = $tmpSnippet[0];
                        $item['company_name'] = $tmpSnippet[1];

                    } catch (Exception $e){
                        $item['title'] = '';
                        $item['company_name'] = '';
                    }

                    $sheet->appendRow($item);
                }
            });
        })->export('csv');

    } else {

        return view('scrapper_linkedin.report', compact('linkedin', 'allLinkedin'));
    }

});