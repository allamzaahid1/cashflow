<header
class="flex h-16 items-center justify-between border-b border-border-base bg-bg-surface px-8 text-text-primary transition-colors duration-200">

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

        <div class="flex items-center gap-3">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 font-bold text-white shadow-sm">

                {{ strtoupper(substr(auth()->user()->name,0,1)) }}

            </div>

            <div>

                <p class="font-medium leading-none">

                    {{ auth()->user()->name }}

                </p>

                <p class="text-xs text-text-secondary mt-1">

                    Administrator

                </p>

            </div>
        </div>

    </div>

</header>