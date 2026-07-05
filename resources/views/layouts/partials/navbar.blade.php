<!-- Top Navbar -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-30 h-16 shadow-sm">
    <div class="flex items-center justify-between px-4 h-full sm:px-6 lg:px-8">
        
        <!-- Left Side: Search Bar -->
        <div class="flex items-center flex-1">
            <!-- Mobile Menu Toggle -->
            <button id="mobile-menu-btn" class="mr-4 text-slate-500 hover:text-slate-700 focus:outline-none md:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            
            <!-- Search Input -->
            <div class="relative w-full max-w-md hidden sm:block">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" class="w-full pl-10 pr-4 py-2 border-none bg-transparent text-sm text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none" placeholder="Search (Ctrl+/)">
            </div>
        </div>

        <!-- Right Side: Icons & Profile -->
        <div class="flex items-center space-x-3 sm:space-x-4">
            
            <!-- Translate Icon -->
            <button class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path></svg>
            </button>

            <!-- Theme Toggle (Moon) -->
            <button class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>

            <!-- Notifications (Bell) -->
            <button class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-full transition-colors relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Divider -->
            <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

            <!-- User Profile Avatar -->
            <button class="flex items-center focus:outline-none">
                <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 shadow-sm">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=3b5998&color=fff" alt="User Avatar" class="w-full h-full object-cover">
                </div>
            </button>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        
        if(btn && sidebar) {
            btn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }
    });
</script>
