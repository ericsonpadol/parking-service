<!DOCTYPE html>
<html>
    <head>
        <title>PARKIT: End User License Agreement</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

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
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
                padding: 0 15px 0 15px;
            }

            .content h1, h2 {
                text-align: left;
                font-weight: bold;
            }

            .content p {
                text-align: left;
                display: block;
            }

            .title {
                font-size: 96px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">{!! $eula_title !!}</div>
                <hr>
                <h1> {!! $eula_header !!}</h1>
                <p>
                    {!! $eula_header_desc !!}
                </p>
                <h2> {!! $eula_license_header !!}</h2>
                <p>
                    {!! $eula_license_content !!}
                </p>
                <h2> {!! $eula_restriction_header !!} </h2>
                <p>
                    {!! $eula_restriction_content !!}
                </p>
                <h2> {!! $eula_mods_to_application_header !!} </h2>
                <p>
                    {!! $eula_mods_to_application_content !!}
                </p>
                <h2> {!! $eula_term_and_termination_header !!} </h2>
                <p>
                    {!! $eula_term_and_termination_content !!}
                </p>
                <h2> {!! $eula_severability_header!!} </h2>
                <p>
                    {!! $eula_severability_content !!}
                </p>
                <h2> {!! $eula_amends_agreements_header !!}</h2>
                <p>
                    {!! $eula_amends_agreements_content !!}
                </p>
                <h2> {!! $eula_contact_info_header !!} </h2>
                <p>
                    {!! $eula_contact_info_content !!}
                </p>
            </div>
        </div>
    </body>
</html>
