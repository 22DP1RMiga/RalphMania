<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
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

<body class="font-sans antialiased" id="dynamic-background">
    @inertia

    <div id="background-dark-overlay"></div>
    <script src="{{ asset('js/background.js') }}"></script>
{{--    @vite('resources/js/background.js')--}}
</body>

<style>
    /* Default background */
    body {
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        transition: background 0.5s ease-in-out;
    }

    /* Dark overlay (will be toggled) */
    #background-dark-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7); /* 70% dark overlay */
        z-index: -1; /* Behind content */
        pointer-events: none;
        display: none; /* Hidden by default */
    }
</style>

</html>
