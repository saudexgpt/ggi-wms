<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#0e750a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Warehouse Management System</title>

    <link rel="icon" type="image/png" sizes="192x192"  href="/favicon/favicon.png">
    <link rel="manifest" href="/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <link href="{{ asset('css/app.css') }}" type="text/css" rel="stylesheet" />
    {{-- <link href="{{ mix('css/app.css') }}" type="text/css" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="{{ asset('dist/css/bootstrap.min.css') }}">    <!-- Font Awesome -->


    <link rel="stylesheet" href="{{ asset('dist/font-awesome/css/font-awesome.min.css') }}">

    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.css') }}">
    <link href="{{ URL::asset('css/style.css') }}" type="text/css" rel="stylesheet" />
    <style>
        /* blue*/
        /*
        .box{
            border: 1px solid #409EFF;
        }
        .box-header{
            background-color: #409EFF;
            color: #ffffff;
        } */

        /*Orange*/
        .box{
            border: 1px solid #1f2d3d ;
        }
        .box-header{
            background-color: #1f2d3d ;
            color: #fff;
        }

    </style>
</head>
<body>
    <div id="app">
        <app></app>
    </div>

    <script src=/static/tinymce4.7.5/tinymce.min.js></script>
    <script src="{{ asset('js/vendor.js') }}"></script>
    <script src="{{ asset('js/manifest.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    {{-- <script src="https://js.pusher.com/6.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('ce87eae7b5bd426858ed', {
        cluster: 'eu'
        });

        var channel = pusher.subscribe('my-channel');
        channel.bind('App.Laravue.Models.User.'+1, function(data) {
        //app.messages.push(JSON.stringify(data));
        console.log(JSON.stringify(data));
        });


    </script>
     --}}
{{--
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script> --}}
</body>
</html>
