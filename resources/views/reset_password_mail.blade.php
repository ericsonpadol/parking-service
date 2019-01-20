<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Montserrat', sans-serif;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: left;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }

            .tokenize {
                color: #D4886A;
            }

            .mail_important {
                color: #801515;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                {!! $mail_content !!}
            </div>
        </div>
    </body>
</html>
