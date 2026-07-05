<!-- Sidebar Container -->
<aside 
    id="sidebar" 
    class="flex flex-col w-64 h-screen px-4 py-6 overflow-y-auto bg-white border-r border-slate-200 transition-transform duration-300 ease-in-out transform -translate-x-full md:relative md:translate-x-0 z-40 fixed"
>
    <!-- Logo/Brand Area -->
    <div class="flex items-center justify-center mb-8 pb-4 border-b border-slate-100">
        <img src="{{ asset('assets/images/logo/logo-pge.png') }}" alt="Pertamina Geothermal Energy" class="h-10 w-auto object-contain">
    </div>

    <!-- Navigation Menu -->
    <div class="flex flex-col justify-between flex-1 mt-4">
        <nav class="space-y-1 text-sm font-medium">
            
            @if(auth()->user()->role === 'Admin HSSE' || auth()->user()->role === 'Manager HSSE')
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 mt-4">Dashboards</p>

                <a class="flex items-center px-3 py-2.5 transition-colors rounded-lg {{ request()->is('/') ? 'bg-[#9DBF2A] text-white shadow-md shadow-[#9DBF2A]/30' : 'text-slate-600 bg-white hover:bg-slate-50 hover:text-[#9DBF2A]' }}" href="{{ url('/') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="mx-3 flex-1 font-medium">Dashboard Global</span>
                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">New</span>
                </a>

                <div class="mt-2 space-y-1 pl-4 border-l-2 border-slate-100 ml-5">
                    @foreach(['Operation', 'Maintenance', 'HSSE', 'Business Support'] as $fn)
                    @php $isActive = request()->is('dashboard/fungsi/'.strtolower($fn)); @endphp
                    <a class="flex items-center px-3 py-2 transition-colors rounded-r-lg {{ $isActive ? 'bg-[#9DBF2A]/10 text-[#9DBF2A] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}" href="{{ route('dashboard.fungsi', $fn) }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 {{ $isActive ? 'bg-[#9DBF2A]' : 'bg-slate-300' }}"></div>
                        <span class="text-sm">{{ $fn }}</span>
                    </a>
                    @endforeach
                </div>
            @else
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 mt-4">Dashboard</p>
                <a class="flex items-center px-3 py-2.5 transition-colors rounded-lg {{ request()->is('dashboard/fungsi*') ? 'bg-[#9DBF2A] text-white shadow-md shadow-[#9DBF2A]/30' : 'text-slate-600 bg-white hover:bg-slate-50 hover:text-[#9DBF2A]' }}" href="{{ route('dashboard.fungsi', auth()->user()->fungsi) }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="mx-3 text-sm">Dashboard {{ auth()->user()->fungsi }}</span>
                </a>
            @endif

            <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 mt-8">Sistem SIPEKA</p>

            <a class="flex items-center px-3 py-2.5 transition-colors rounded-lg {{ request()->is('findings*') ? 'bg-[#9DBF2A] text-white shadow-md shadow-[#9DBF2A]/30' : 'text-slate-600 bg-white hover:bg-slate-50 hover:text-[#9DBF2A]' }}" href="{{ url('/findings') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="mx-3">Monitoring Temuan</span>
            </a>
            
            <a class="flex items-center px-3 py-2.5 transition-colors rounded-lg text-slate-600 bg-white hover:bg-slate-50 hover:text-[#9DBF2A]" href="#">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span class="mx-3">Export Excel</span>
            </a>

            @if(auth()->user()->role === 'Admin HSSE')
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 mt-8">Administrator</p>

                <a class="flex items-center px-3 py-2.5 transition-colors rounded-lg {{ request()->is('users*') ? 'bg-[#9DBF2A] text-white shadow-md shadow-[#9DBF2A]/30' : 'text-slate-600 bg-white hover:bg-slate-50 hover:text-[#9DBF2A]' }}" href="{{ url('/users') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="mx-3">Manajemen Akun</span>
                </a>
            @endif

        </nav>
    </div>
</aside>
