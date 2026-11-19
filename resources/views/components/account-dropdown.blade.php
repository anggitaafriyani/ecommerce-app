@props(['user'])

<div class="relative" x-data="{ userMenuOpen: false }" x-id="['account-menu']" @click.outside="userMenuOpen = false" @keydown.escape.window="userMenuOpen = false">
    <button
        type="button"
        @click="userMenuOpen = !userMenuOpen"
        :aria-expanded="userMenuOpen.toString()"
        :id="$id('account-menu')"
        aria-haspopup="true"
        aria-label="Menu akun {{ $user->name }}"
        class="group flex items-center gap-2 rounded-full border border-[#F3D6C3] bg-[#FFF3EB] p-1 pr-3 shadow-xs transition-all duration-200 hover:bg-[#FDEBDD] hover:border-pm-orange/50 focus:outline-none focus:ring-2 focus:ring-pm-orange/30 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">

        {{-- Avatar bulat berwarna oranye --}}
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pm-orange text-xs font-bold text-white shadow-xs">
            {{ strtoupper(mb_substr($user->name ?? 'P', 0, 1)) }}
        </span>

        {{-- Chevron down icon --}}
        <svg class="h-4 w-4 text-pm-charcoal/70 transition-transform duration-200 group-hover:text-pm-charcoal dark:text-gray-300"
             :class="userMenuOpen ? 'rotate-180' : ''"
             viewBox="0 0 20 20"
             fill="currentColor"
             aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>

    {{-- Dropdown Card --}}
    <div
        x-show="userMenuOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute right-0 mt-2 w-[280px] max-w-[calc(100vw-2rem)] rounded-[1.25rem] border border-[#F3D6C3] bg-white p-4 shadow-xl shadow-pm-charcoal/5 z-50 dark:border-gray-800 dark:bg-gray-900"
        role="menu"
        aria-orientation="vertical"
        :aria-labelledby="$id('account-menu')">

        {{-- A. Header Profil --}}
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-pm-orange text-base font-bold text-white shadow-xs">
                {{ strtoupper(mb_substr($user->name ?? 'P', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-pm-charcoal dark:text-gray-100">
                    {{ $user->name }}
                </p>
                <p class="truncate text-xs text-pm-muted dark:text-gray-400">
                    {{ $user->email }}
                </p>
            </div>
        </div>

        {{-- B. Garis Pemisah --}}
        <hr class="my-3 border-t border-[#F3D6C3]/80 dark:border-gray-800" />

        {{-- Menu Items --}}
        <div class="flex flex-col gap-1">
            {{-- C. Menu Profil Saya --}}
            <a href="{{ route('profile.edit') }}"
               role="menuitem"
               @click="userMenuOpen = false"
               class="group flex h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-pm-charcoal transition-colors hover:bg-[#FFF3EB] hover:text-pm-orange dark:text-gray-200 dark:hover:bg-gray-800 dark:hover:text-orange-400">
                <svg class="h-5 w-5 shrink-0 text-pm-muted transition-colors group-hover:text-pm-orange dark:text-gray-400 dark:group-hover:text-orange-400" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                <span>Profil Saya</span>
            </a>

            {{-- D. Menu Keluar --}}
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit"
                        role="menuitem"
                        class="group flex h-11 w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 transition-colors hover:bg-[#FFF3EB] hover:text-red-700 dark:text-red-400 dark:hover:bg-gray-800 dark:hover:text-red-300">
                    <svg class="h-5 w-5 shrink-0 text-red-500 transition-colors group-hover:text-red-600 dark:text-red-400" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd" />
                        <path fill-rule="evenodd" d="M19 10a.75.75 0 00-.75-.75H8.704l2.47-2.47a.75.75 0 10-1.06-1.06l-3.75 3.75a.75.75 0 000 1.06l3.75 3.75a.75.75 0 101.06-1.06l-2.47-2.47H18.25A.75.75 0 0019 10z" clip-rule="evenodd" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>
