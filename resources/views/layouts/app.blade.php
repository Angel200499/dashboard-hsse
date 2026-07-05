<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') - HSSE PGE Area Lahendong</title>

    <!-- Google Fonts: Inter for Professional/Corporate Look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets (TailwindCSS & Vanilla JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Base Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Enterprise Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F8FAFC; 
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8; 
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased overflow-hidden selection:bg-blue-600 selection:text-white">
    
    <!-- App Container -->
    <div class="flex h-screen" id="app-wrapper">
        
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Viewport Wrapper -->
        <div class="flex flex-col flex-1 w-full overflow-hidden relative">
            
            <!-- Header / Navbar -->
            @include('layouts.partials.navbar')

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F8FAFC]">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col min-h-[calc(100vh-64px)]">
                    
                    <!-- Page Content -->
                    <div class="flex-1">
                        @yield('content')
                    </div>
                    
                    <!-- Footer -->
                    @include('layouts.partials.footer')
                </div>
            </main>

        </div>
    </div>

    <!-- Stack for Page Specific Scripts -->
    @stack('scripts')
</body>
</html>