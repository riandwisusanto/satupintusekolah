<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Shipping Management System</title>
            @vite(['resources/css/app.css'])
        <link rel="shortcut icon" href="{{ asset('assets/images/LogoUMKMSoft.jpg') }}" type="image/x-icon">
        
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
     
    </head>

    <body>
        <div class="wrapper" id="app">

        </div>

       
        @vite(['resources/js/app.js'])
    
    </body>
</html>
