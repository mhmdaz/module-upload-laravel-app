<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
    </head>
    <body class="antialiased">
         @if(Session::has('message'))
            <p >{{ Session::get('message') }}</p>
         @endif

        <form action="upload" method="post" enctype="multipart/form-data">
            @csrf

            <label for="csv_file"></label>
            <input type="file" name="csv_file" />

            <button type="submit">Upload</button>
        </form>
    </body>
</html>
