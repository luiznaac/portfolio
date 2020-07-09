<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{secure_asset('site/style.css')}}">
        <script src="{{secure_asset('site/jquery.js')}}"></script>
        <script src="{{secure_asset('site/bootstrap.js')}}"></script>
        <title>{{config('app.name', 'Portfolio')}}</title>
    </head>
    <body>
        @include('inc.navbar')
        <div class="container" style="margin-top: 30px">
            @include('inc.messages')
            @yield('content')
        </div>
    </body>
</html>
