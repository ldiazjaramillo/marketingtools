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
                display: flex;
                justify-content: center;
                margin-top: 100px;
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
                font-size: 24px;
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
                <table>
                    <tr>
                        <td width="30%">
                            <div class="title m-b-md">Find email</div>

                            <div class="links">
                                <form class="form-horizontal" role="form" method="POST" action="/mapping_email" enctype="multipart/form-data">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="phone" value="true">
                                    <input type="file" id="import" name="import" accept=".csv,.xls,.xlsx"><br/><br/>
                                    <input type="submit" id="submit" value="Submit"/>
                                    <p>Will check only email!</p>
                                </form>

                                <div style="height: 300px; overflow-y: scroll;">
                                    <table>
                                        @foreach($only_detected_email as $item)
                                            <tr>
                                                <td>
                                                    <a href="/results_email/{{$item['id']}}" target="_blank">{{$item['name']}}</a>
                                                </td>
                                                <td>
                                                    {{$item['total_row']}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>

                            </div>
                        </td>
                        <td width="5%"></td>
                        <td width="30%">
                            <div class="title m-b-md">Email and phone checker</div>

                            <div class="links">
                                <form class="form-horizontal" role="form" method="POST" action="/mapping" enctype="multipart/form-data">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="file" id="import" name="import" accept=".csv,.xls,.xlsx"><br/><br/>
                                    <input type="submit" id="submit" value="Submit"/>
                                    <p>Will check email and phone</p>
                                </form>

                                <div style="height: 300px; overflow-y: scroll;">
                                    <table>
                                        @foreach($detected_email as $item)
                                            <tr>
                                                <td>
                                                    <a href="/results/{{$item['id']}}" target="_blank">{{$item['name']}}</a>
                                                </td>
                                                <td>
                                                    {{$item['total_row']}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </td>
                        <td width="5%"></td>
                        <td width="30%">
                            <div class="title m-b-md">Find phone</div>

                            <div class="links">
                                <form class="form-horizontal" role="form" method="POST" action="/mapping_phone" enctype="multipart/form-data">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="phone" value="true">
                                    <input type="file" id="import" name="import" accept=".csv,.xls,.xlsx"><br/><br/>
                                    <input type="submit" id="submit" value="Submit"/>
                                    <p>Will check only phone!</p>
                                </form>

                                <div style="height: 300px; overflow-y: scroll;">
                                    <table>
                                        @foreach($detected_phone as $item)
                                            <tr>
                                                <td>
                                                    <a href="/results/phone/{{$item['id']}}" target="_blank">{{$item['name']}}</a>
                                                </td>
                                                <td>
                                                    {{$item['total_row']}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>

                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td width="30%">
                            <div class="title m-b-md">Find a linkedin profile</div>

                            <div class="links">
                                <form class="form-horizontal" role="form" method="POST" action="/mapping_linkedin" enctype="multipart/form-data">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="phone" value="true">
                                    <input type="file" id="import" name="import" accept=".csv,.xls,.xlsx"><br/><br/>
                                    <input type="submit" id="submit" value="Submit"/>
                                    <p>Will check only profile linkedin!</p>
                                </form>

                                <div style="height: 300px; overflow-y: scroll;">
                                    <table>
                                        @foreach($linkedin as $item)
                                            <tr>
                                                <td>
                                                    <a href="/results/linkedin/{{$item['id']}}" target="_blank">{{$item['name']}}</a>
                                                </td>
                                                <td>
                                                    {{$item['total_row']}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>

                            </div>
                        </td>
                        <td width="5%"></td>
                        <td width="30%">
                            <div class="title m-b-md">Search new contact</div>

                            <div class="links">
                                <form action="/create_new_session" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="file" id="import" name="import" accept=".csv,.xls,.xlsx"><br/><br/>
                                    <input type="submit" id="submit" value="Submit"/>
                                </form>

                                <div style="height: 300px; overflow-y: scroll;">
                                    <table>
                                        @foreach($linkedinGoogle as $itemGoogle)
                                            <tr>
                                                <td>
                                                    <a href="/lists_linkedin/{{$itemGoogle['id']}}" target="_blank">{{$itemGoogle['request']}}</a>
                                                </td>
                                                <td>
                                                    {{$itemGoogle['page']}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>

                            </div>
                        </td>
                        <td width="5%"></td>
                        <td width="30%">
                            <div class="title m-b-md">Find a site company</div>

                            <div class="links">
                                <form class="form-horizontal" role="form" method="POST" action="/mapping_company" enctype="multipart/form-data">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="phone" value="true">
                                    <input type="file" id="import" name="import" accept=".csv,.xls,.xlsx"><br/><br/>
                                    <input type="submit" id="submit" value="Submit"/>
                                    <p>Will check only site company!</p>
                                </form>

                                <div style="height: 300px; overflow-y: scroll;">
                                    <table>
                                        @foreach($site_company as $item)
                                            <tr>
                                                <td>
                                                    <a href="/results/company_name/{{$item['id']}}" target="_blank">{{$item['name']}}</a>
                                                </td>
                                                <td>
                                                    {{$item['total_row']}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>

                            </div>
                        </td>
                    </tr>

                </table>


            </div>
        </div>
    </body>
</html>
