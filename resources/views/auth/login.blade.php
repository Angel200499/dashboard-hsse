@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="bg-white rounded-2xl shadow-2xl p-8 sm:p-10 w-full overflow-hidden">
    
    <!-- Header Area -->
    <div class="text-center mb-8">
        <!-- Brand Logo Placeholder -->
        <div class="flex justify-center mb-6">
            <!-- PGE Logo -->
            <img src="{{ asset('assets/images/logo/logo-pge.png') }}" alt="Pertamina Geothermal Energy Logo" class="h-16 w-auto object-contain">
        </div>

        <h2 class="text-2xl font-bold text-slate-800">HSSE Dashboard Monitoring 👋</h2>
        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
            Sistem monitoring progress tindak lanjut temuan lapangan dan audit HSSE PGE Area Lahendong.
        </p>
    </div>

    <!-- Login Form -->
    <form class="space-y-5" method="POST" action="{{ route('login.post') }}">
        @csrf

        <!-- Username Input -->
        <div>
            <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
            <input type="text" id="username" name="username" class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] p-2.5 transition-colors" placeholder="Masukkan username Anda..." required value="{{ old('username') }}">
            @error('username')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Input -->
        <div class="mb-5 relative">
            <label for="password" class="sr-only">Password</label>
            <input 
                id="password" 
                type="password" 
                name="password" 
                class="w-full px-4 py-3.5 border border-slate-300 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A] focus:border-transparent transition-all pr-12" 
                placeholder="Password" 
                required
            >
            <!-- Eye Icon Toggle -->
            <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </button>
        </div>



        <!-- Submit Button -->
        <div>
            <button 
                type="submit" 
                class="w-full px-4 py-3.5 text-sm font-bold text-white uppercase bg-[#9DBF2A] rounded-xl hover:bg-[#8ca825] focus:outline-none focus:ring-4 focus:ring-[#9DBF2A]/50 transition-all shadow-md shadow-[#9DBF2A]/20"
            >
                SIGN IN
            </button>
        </div>
        
    </form>
</div>
@endsection