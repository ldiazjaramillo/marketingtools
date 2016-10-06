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
                font-size: 44px;
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
                        <td>
                            <div class="title m-b-md">Email checker</div>

                            <div class="links">
                                <form class="form-horizontal" role="form" method="POST" action="/mapping" enctype="multipart/form-data">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="file" id="import" name="import" accept=".csv,.xls,.xlsx"><br/><br/>
                                    <input type="submit" id="submit" value="Submit"/>
                                </form>
                            </div>
                        </td>
                        <td width="100px"></td>
                        <td>
                            <div class="title m-b-md">Find phone</div>

                            <div class="links">
                                <form class="form-horizontal" role="form" method="POST" action="/mapping_phone" enctype="multipart/form-data">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="phone" value="true">
                                    <input type="file" id="import" name="import" accept=".csv,.xls,.xlsx"><br/><br/>
                                    <input type="submit" id="submit" value="Submit"/>
                                </form>
                            </div>
                        </td>
                        <td width="100px"></td>
                        <td>
                            <div class="title m-b-md">Find a site company</div>

                            <div class="links">
                                <form class="form-horizontal" role="form" method="POST" action="/mapping_company" enctype="multipart/form-data">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="phone" value="true">
                                    <input type="file" id="import" name="import" accept=".csv,.xls,.xlsx"><br/><br/>
                                    <input type="submit" id="submit" value="Submit"/>
                                </form>
                            </div>
                        </td>
                    </tr>
                </table>


            </div>
        </div>
    </body>
</html>
