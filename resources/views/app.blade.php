<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="/nusalens-logo.png">
        <link rel="apple-touch-icon" href="/nusalens-logo.png">
        <script>
            try {
                const appearance = localStorage.getItem('appearance');
                const light = appearance === 'light' || (appearance === 'system' && !matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', !light);
            } catch {}
        </script>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
