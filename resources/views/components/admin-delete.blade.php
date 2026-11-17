@props(['action', 'name', 'id', 'message' => 'Tindakan ini tidak dapat dibatalkan melalui halaman ini.'])
<button type="button" class="pm-link text-red-700 dark:text-red-300" x-data x-on:click="$dispatch('open-modal', @js($id))">Hapus<span class="sr-only"> {{ $name }}</span></button>
<x-modal :name="$id" focusable>
    <form action="{{ $action }}" method="POST" class="p-6 text-gray-900 dark:text-gray-100">
        @csrf @method('DELETE')
        <h2 class="text-xl font-semibold">Hapus {{ $name }}?</h2>
        <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $message }}</p>
        <div class="mt-6 flex justify-end gap-3"><button type="button" class="pm-secondary" x-on:click="$dispatch('close')">Batal</button><button type="submit" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white focus:ring-2 focus:ring-red-500">Ya, hapus</button></div>
    </form>
</x-modal>
