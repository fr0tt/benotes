<!doctype html>
<html lang="en" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>SPA</title>

        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        <link rel="stylesheet" type="text/css" href="/css/app.css">
    </head>
    <body class="h-full">
        <div id="app" class="h-full">
            <div class="w-full mx-auto h-full">
                <router-view></router-view>
            </div>
        </div>
        <script src="/js/app.js"></script>
    </body>
</html>