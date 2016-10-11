<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Email checker</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 34px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title  m-b-md">
            {{ $importInfo->name }}
        </div>

        <div class="links">

            <form class="form-horizontal" role="form" method="POST" action="{{$url}}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="import_id" value="{{ $importInfo->id }}">

                <select name="field_name">
                    <option value="" disabled selected>Please select column with site</option>
                    @foreach($header as $key => $item)
                        @if(!empty($item))
                            <option value="{{$key}}">{{$item}}</option>
                        @endif
                    @endforeach
                </select>

                <select name="field_title">
                    <option value="" disabled selected>Please select column with title</option>
                    @foreach($header as $key => $item)
                        @if(!empty($item))
                            <option value="{{$key}}">{{$item}}</option>
                        @endif
                    @endforeach
                </select>

                <select name="field_company_name">
                    <option value="" disabled selected>Please select column with company name</option>
                    @foreach($header as $key => $item)
                        @if(!empty($item))
                            <option value="{{$key}}">{{$item}}</option>
                        @endif
                    @endforeach
                </select>

                <input type="submit" id="submit" value="Start"/>
            </form>
        </div>
    </div>
</div>
</body>
</html>
