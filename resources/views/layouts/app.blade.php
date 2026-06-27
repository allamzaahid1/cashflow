<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ theme: localStorage.getItem('theme') || 'light' }"
      :class="{ 'dark': theme === 'dark' }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
          rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="font-sans bg-bg-base text-text-primary antialiased transition-colors duration-200">

<div class="flex min-h-screen">

    @include('components.layout.sidebar')

    <div class="flex flex-1 flex-col">

        @include('components.layout.navbar')

        <main class="flex-1 overflow-y-auto bg-bg-base p-6">

            @yield('content')

        </main>

    </div>
</div>

<div id="floating-layer" class="fixed inset-0 pointer-events-none z-50"></div>
</body>
</html>