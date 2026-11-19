@props(['action', 'name', 'id', 'message' => 'Tindakan ini tidak dapat dibatalkan melalui halaman ini.'])
<button type="button"
        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-red-700 transition-colors hover:bg-red-50 hover:text-red-800 dark:text-red-400 dark:hover:bg-red-900/30"
        x-data
        x-on:click="$dispatch('open-modal', @js($id))">
    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34 0a.75.75 0 00-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
    </svg>
    Hapus<span class="sr-only"> {{ $name }}</span>
</button>

<x-modal :name="$id" focusable>
    <form action="{{ $action }}" method="POST" class="p-6 sm:p-7 text-pm-charcoal dark:text-gray-100">
        @csrf
        @method('DELETE')

        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34 0a.75.75 0 00-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                </svg>
            </span>
            <h2 class="text-xl font-bold text-pm-charcoal dark:text-gray-100">
                Hapus {{ $name }}?
            </h2>
        </div>

        <p class="mt-3.5 text-sm leading-6 text-pm-muted dark:text-gray-300">
            {{ $message }}
        </p>

        <div class="mt-7 flex justify-end gap-3 border-t border-pm-border pt-4 dark:border-gray-800">
            <button type="button" class="pm-secondary !py-2 !px-4 text-xs" x-on:click="$dispatch('close')">
                Batal
            </button>
            <button type="submit" class="pm-danger !py-2 !px-4 text-xs">
                Ya, hapus
            </button>
        </div>
    </form>
</x-modal>
