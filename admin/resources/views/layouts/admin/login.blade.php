<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('content.admin_panel') }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('assets/fonts/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <link href="{{ asset('assets/admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/admin.css') }}" rel="stylesheet">


</head>
<body class="login-resistration-area">


    <main class="main-login-area ">
        @yield('content')
    </main>


<script src="{{ asset('assets/js/jquery.min.js') }}" defer></script>
<script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}" defer></script>
<script src="{{ asset('assets/admin/js/scripts.js') }}" defer></script>

</body>
</html>
