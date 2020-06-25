<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{asset('')}}">
        <title>{{config('app.name', 'Portfolio')}}</title>
    </head>
    <body>
        <h1>Portfolio</h1>
        @yield('content')
    </body>
</html>
