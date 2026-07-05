<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Login') - HSSE PGE Area Lahendong</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-800 selection:bg-[#008744] selection:text-white">
    
    <!-- Background Image with Overlay -->
    <div class="relative flex items-center justify-center min-h-screen bg-slate-900 bg-bottom bg-cover" 
         style="background-image: url('{{ asset('bglogin.png') }}');">
        
        <!-- Dark Overlay -->
        <div class="absolute inset-0 bg-black/40"></div>
        
        <!-- Content Area -->
        <div class="relative z-10 w-full max-w-md px-6 py-4">
            @yield('content')
        </div>

    </div>

</body>
</html>
