<x-admin-layout title="Kategori produk" subtitle="Susun kebutuhan hewan ke dalam kategori yang mudah dikelola.">
    <x-slot:actions>
        <a href="{{ route('admin.categories.create') }}" class="pm-button shadow-xs">
            <svg class="h-4 w-4 shrink-0" width="16" height="16" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Tambah kategori
        </a>
    </x-slot:actions>

    <p class="mb-3 text-xs text-pm-muted sm:hidden">Geser tabel ke samping untuk melihat semua kolom dan tindakan.</p>

    <div class="pm-card overflow-hidden">
        <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel dapat digulir secara horizontal">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Nama kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Produk</th>
                        <th class="text-right"><span class="sr-only">Tindakan</span>Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pm-border dark:divide-gray-800">
                    @forelse($categories as $category)
                        <tr class="transition-colors hover:bg-pm-cream/50 dark:hover:bg-gray-800/50">
                            <td class="font-bold text-pm-charcoal dark:text-gray-100 whitespace-nowrap">
                                <span class="inline-flex items-center gap-2">
                                    <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-pm-cream p-2">
                                        <x-category-image :category="$category" />
                                    </span>
                                    {{ $category->name }}
                                </span>
                            </td>
                            <td class="max-w-md text-sm text-pm-muted dark:text-gray-300">
                                {{ Str::limit($category->description, 100) ?: '—' }}
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <span class="inline-flex rounded-full bg-pm-green-lt px-3 py-0.5 text-xs font-semibold text-pm-green dark:bg-green-900/40 dark:text-green-300">
                                    {{ $category->products_count }} produk
                                </span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-3">
                                    <a class="pm-link text-xs font-semibold" href="{{ route('admin.categories.edit', $category) }}">
                                        Edit<span class="sr-only"> {{ $category->name }}</span>
                                    </a>
                                    <x-admin-delete :action="route('admin.categories.destroy', $category)"
                                                    :name="$category->name"
                                                    :id="'delete-category-'.$category->id"
                                                    message="Kategori hanya dapat dihapus jika tidak digunakan produk, termasuk produk yang diarsipkan." />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-16 text-center text-sm text-pm-muted dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-pm-cream text-pm-muted dark:bg-gray-800">📁</span>
                                    <p class="mt-3 font-semibold text-pm-charcoal dark:text-gray-200">Belum ada kategori. Mulai dengan menambahkan kategori pertama.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($categories->hasPages())
        <div class="mt-6">
            {{ $categories->links() }}
        </div>
    @endif
</x-admin-layout>
