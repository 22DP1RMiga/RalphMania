<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
{{--    <link rel="icon" href="../../public/img/RoltonsLV_Icon.png">--}}
    <link rel="icon" href="{{ asset('img/RoltonsLV_Icon.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

{{--    <title inertia>{{ config('app.name', 'HOME | RalphMania') }}</title>--}}

    <title>RalphMania</title>


    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>
<body class="font-sans antialiased">
@inertia
</body>

<style>
    body {
        background-image: url('../../public/img/Coder_RoltonsLV.png');
        /*background: black;*/
    }
</style>

</html>
