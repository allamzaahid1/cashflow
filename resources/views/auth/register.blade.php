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
                    <div x-data="{ step: 1, showPw: false }">
                        <!-- Step indicator -->
                        <div class="flex items-center gap-2 mb-5">
                            <!-- Step 1 Indicator -->
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="step = 1"
                                    class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all border"
                                    :class="step >= 1 ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-400'"
                                >
                                    <span x-show="step > 1">✓</span>
                                    <span x-show="step === 1">1</span>
                                </button>
                                <span class="text-xs font-semibold" :class="step === 1 ? 'text-slate-900 dark:text-white' : 'text-slate-400'">Info Toko</span>
                            </div>
                            <div class="w-8 h-px bg-slate-200 dark:bg-slate-800" :class="step > 1 && 'bg-emerald-600'"></div>
                            <!-- Step 2 Indicator -->
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all border"
                                    :class="step >= 2 ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-400'"
                                >
                                    2
                                </div>
                                <span class="text-xs font-semibold" :class="step === 2 ? 'text-slate-900 dark:text-white' : 'text-slate-400'">Akun</span>
                            </div>
                        </div>

                        <h2 class="text-lg font-bold mb-1 text-slate-900 dark:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;" x-text="step === 1 ? 'Buat Akun Baru' : 'Atur Password'">
                            Buat Akun Baru
                        </h2>
                        <p class="text-xs mb-6 text-slate-500 dark:text-slate-400" x-text="step === 1 ? 'Lengkapi informasi dasar Anda' : 'Buat email dan password yang aman'">
                            Lengkapi informasi dasar Anda
                        </p>

                        <form method="POST" action="{{ route('register') }}" class="space-y-4">
                            @csrf

                            <!-- Step 1: Info Toko -->
                            <div x-show="step === 1" class="space-y-4">
                                <div>
                                    <label for="name" class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Nama Pengguna / Toko
                                    </label>
                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="cth: Warung Barokah"
                                        required
                                        autofocus
                                        autocomplete="name"
                                        class="w-full px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 bg-slate-50/50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                                    />
                                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                </div>

                                <button
                                    type="button"
                                    @click="if (document.getElementById('name').value.trim() !== '') { step = 2; } else { alert('Nama wajib diisi.'); }"
                                    class="w-full py-3.5 rounded-xl font-bold text-sm text-white transition-all bg-gradient-to-br from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-lg shadow-emerald-500/35"
                                    style="font-family: 'Plus Jakarta Sans', sans-serif;"
                                >
                                    Lanjut →
                                </button>
                            </div>

                            <!-- Step 2: Akun & Keamanan -->
                            <div x-show="step === 2" class="space-y-4" style="display: none;" x-cloak>
                                <!-- Email -->
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
                                        autocomplete="username"
                                        class="w-full px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 bg-slate-50/50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                                    />
                                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                </div>

                                <!-- Password -->
                                <div>
                                    <label for="password" class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Password
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="password"
                                            :type="showPw ? 'text' : 'password'"
                                            name="password"
                                            placeholder="Min. 8 karakter"
                                            required
                                            autocomplete="new-password"
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

                                <!-- Confirm Password -->
                                <div>
                                    <label for="password_confirmation" class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Konfirmasi Password
                                    </label>
                                    <input
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        placeholder="Ulangi password"
                                        required
                                        autocomplete="new-password"
                                        class="w-full px-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 bg-slate-50/50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                                    />
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                                </div>

                                <!-- Buttons -->
                                <div class="flex gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="step = 1"
                                        class="flex-1 py-3.5 rounded-xl font-semibold text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900 transition-all"
                                    >
                                        ← Kembali
                                    </button>
                                    <button
                                        type="submit"
                                        class="flex-1 py-3.5 rounded-xl font-bold text-sm text-white transition-all bg-gradient-to-br from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-lg shadow-emerald-500/35"
                                        style="font-family: 'Plus Jakarta Sans', sans-serif;"
                                    >
                                        Daftar
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Login Link -->
                        <p class="text-center text-sm mt-6 text-slate-600 dark:text-slate-300">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline transition-colors">
                                Masuk di sini
                            </a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </body>
</html>
