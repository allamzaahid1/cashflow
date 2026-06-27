<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ theme: localStorage.getItem('theme') || 'light' }"
      :class="{ 'dark': theme === 'dark' }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Catetin') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased transition-colors duration-200">
        <div class="min-h-screen flex items-center justify-center p-4 relative">
            
            <!-- Background glow blobs -->
            <div class="fixed inset-0 pointer-events-none overflow-hidden">
                <div class="absolute rounded-full blur-3xl opacity-20 bg-emerald-500 w-[480px] h-[480px] -top-[10%] -left-[8%]"></div>
                <div class="absolute rounded-full blur-3xl opacity-10 bg-blue-500 w-[360px] h-[360px] bottom-[5%] -right-[5%]"></div>
            </div>

            <div class="relative w-full max-w-[500px]">
                <!-- Brand mark -->
                <div class="text-center mb-8 flex flex-col items-center justify-center gap-2">
                    <img src="{{ asset('brand/Catetin.png') }}" alt="Catetin Logo" class="w-[230px] h-auto">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Management System
                    </p>
                </div>

                <!-- Card Content -->
                <div class="rounded-2xl p-7 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-2xl">
                    <div x-data="{ showPw: false }">
                        <h2 class="text-lg font-bold mb-1 text-slate-900 dark:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            Selamat Datang Kembali
                        </h2>
                        <p class="text-xs mb-6 text-slate-500 dark:text-slate-400">
                            Masuk ke akun toko Anda untuk melanjutkan
                        </p>

                        <!-- Session Status -->
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf

                            <!-- Email Address -->
                            <div>
                                <label for="email" class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="warungbarokah@gmail.com"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    class="w-full px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 bg-slate-50/50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                                />
                                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            </div>

                            <!-- Password -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="password" class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Password
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline transition-colors">
                                            Lupa password?
                                        </a>
                                    @endif
                                </div>
                                <div class="relative">
                                    <input
                                        id="password"
                                        :type="showPw ? 'text' : 'password'"
                                        name="password"
                                        placeholder="••••••••"
                                        required
                                        autocomplete="current-password"
                                        class="w-full px-4 py-2.5 pr-10 text-sm text-slate-800 dark:text-slate-200 bg-slate-50/50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                                    />
                                    <button
                                        type="button"
                                        @click="showPw = !showPw"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                    >
                                        <template x-if="showPw">
                                            <x-lucide-eye-off class="w-4 h-4" />
                                        </template>
                                        <template x-if="!showPw">
                                            <x-lucide-eye class="w-4 h-4" />
                                        </template>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-1" />
                            </div>

                            <!-- Remember Me -->
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    name="remember"
                                    class="rounded border-slate-200 dark:border-slate-800 text-emerald-600 bg-slate-50/50 dark:bg-slate-950/40 focus:ring-emerald-500"
                                />
                                <span class="text-xs text-slate-600 dark:text-slate-300">Ingat saya di perangkat ini</span>
                            </label>

                            <!-- Submit -->
                            <button
                                type="submit"
                                class="w-full py-3.5 rounded-xl font-bold text-sm text-white transition-all mt-2 bg-gradient-to-br from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-lg shadow-emerald-500/35"
                                style="font-family: 'Plus Jakarta Sans', sans-serif;"
                            >
                                Masuk ke Dashboard
                            </button>
                        </form>

                        <!-- Divider -->
                        <div class="flex items-center gap-3 my-5">
                            <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
                            <span class="text-xs text-slate-400">atau</span>
                            <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
                        </div>

                        <!-- Register Link -->
                        <p class="text-center text-sm text-slate-600 dark:text-slate-300">
                            Belum punya akun?
                            <a href="{{ route('register') }}" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline transition-colors">
                                Daftar Sekarang
                            </a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </body>
</html>
