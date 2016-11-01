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
            font-size: 84px;
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
        <div class="title m-b-md">
            Report
        </div>
        <div class="links">

            <table width="100%">
                <tr>
                    <td>Success <br/>email</td>
                    <td>Bad <br/>contacts</td>
                    <td>In queue</td>
                    <td>Google <br/> checker</td>
                </tr>
                <tr>
                    <td>
                        <h2>{{$success->count()}}</h2>
                        <a href="?type=success&data_source=email" target="_blank">download</a>
                    </td>
                    <td>
                        <h2>{{$bad->count()}}</h2>
                        <a href="?type=bad&data_source=email" target="_blank">download</a>
                    </td>
                    <td>
                        <h2>{{$queue->count()}}</h2>
                    </td>
                    <td>
                        <h2>{{$checkEmailInGoogle->count()}}</h2>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <a href="/storage/{{$id}}" target="_blank">API log</a>
                    </td>
                </tr>
            </table>
        </div>
        <br/>
    </div>
</div>
<script type="text/javascript">
    var timeout = setTimeout("location.reload(true);", 90000);
</script>
</body>
</html>