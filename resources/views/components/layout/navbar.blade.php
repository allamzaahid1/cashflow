<header
class="flex h-16 items-center justify-between border-b border-border-base bg-bg-surface px-8 text-text-primary transition-colors duration-200 relative z-40">

    <div>

        <h2 class="text-xl font-semibold">

            @yield('title')

        </h2>

    </div>

    <div class="flex items-center gap-6">

        <!-- Theme Toggle Button -->
        <button @click="theme = (theme === 'light' ? 'dark' : 'light'); localStorage.setItem('theme', theme)"
                class="p-2 rounded-lg text-text-secondary hover:bg-bg-base transition-colors"
                type="button"
                title="Toggle Theme">
            <template x-if="theme === 'light'">
                <x-lucide-moon class="w-5 h-5" />
            </template>
            <template x-if="theme === 'dark'">
                <x-lucide-sun class="w-5 h-5" />
            </template>
        </button>

        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="flex items-center gap-3 text-left focus:outline-none select-none hover:opacity-90 transition-opacity cursor-pointer">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 font-bold text-white shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="hidden sm:block">
                    <p class="font-medium leading-none text-text-primary">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-text-secondary mt-1">
                        Administrator
                    </p>
                </div>
                <x-lucide-chevron-down class="w-4 h-4 text-text-secondary transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
            </button>

            <!-- Dropdown Menu -->
            <template x-teleport="#floating-layer">
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="fixed right-8 top-16 w-48 rounded-xl border border-border-base bg-bg-surface py-1 shadow-lg ring-1 ring-black/5 z-50 text-sm text-text-primary pointer-events-auto"
                     style="display: none;">
                     
                    <!-- Settings/Profile link -->
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-bg-base transition-colors font-medium">
                        Edit Profil
                    </a>
                    
                    <div class="h-px bg-border-base my-1"></div>
                    
                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left block px-4 py-2 hover:bg-bg-base transition-colors font-semibold text-danger-text">
                            Keluar Akun
                        </button>
                    </form>
                </div>
            </template>
        </div>

    </div>

</header>