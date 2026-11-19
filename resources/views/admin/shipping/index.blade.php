<x-admin-layout title="Pengaturan Pengiriman" subtitle="Kelola kurir ekspedisi, tipe layanan, dan aturan tarif ongkir internal PawMart.">
    <x-slot:actions>
        <div class="flex w-full flex-col gap-2.5 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center">
            <button type="button"
                    class="pm-button h-11 w-full justify-center px-4 text-sm sm:w-auto"
                    x-data
                    x-on:click="$dispatch('open-modal', 'add-courier-modal')">
                <svg class="h-4 w-4 shrink-0" width="16" height="16" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Ekspedisi Kurir
            </button>
            <button type="button"
                    class="pm-button h-11 w-full justify-center px-4 text-sm sm:w-auto"
                    x-data
                    x-on:click="$dispatch('open-modal', 'add-service-modal')">
                <svg class="h-4 w-4 shrink-0" width="16" height="16" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Tambah Layanan Kurir
            </button>
            <button type="button"
                    class="pm-button h-11 w-full justify-center px-4 text-sm sm:w-auto"
                    x-data
                    x-on:click="$dispatch('open-modal', 'add-rate-modal')">
                <svg class="h-4 w-4 shrink-0" width="16" height="16" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Tambah Tarif Baru
            </button>
        </div>
    </x-slot:actions>

    {{-- Flash Message --}}
    @if(session('status'))
        <div class="mb-6 flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800 shadow-xs dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300">
            <div class="flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-500 text-white">✓</span>
                <span class="font-medium">{{ session('status') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">✕</button>
        </div>
    @endif

    {{-- ════════════════════════════════════════
         SECTION 1: DAFTAR EKSPEDISI & LAYANAN
    ════════════════════════════════════════ --}}
    <div class="mb-8">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-pm-charcoal dark:text-gray-100">Ekspedisi & Layanan Aktif</h2>
                <p class="text-xs text-pm-muted dark:text-gray-400">Opsi kurir yang tersedia untuk dipilih pelanggan saat proses checkout.</p>
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($couriers as $courier)
                <div class="pm-card flex flex-col justify-between p-5 border border-pm-border dark:border-gray-800">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-pm-border/60 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-pm-orange-lt text-pm-orange font-bold text-xs uppercase dark:bg-orange-950/60 dark:text-orange-400">
                                    {{ substr($courier->code, 0, 3) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-pm-charcoal dark:text-gray-100">{{ $courier->name }}</h3>
                                    <span class="text-[11px] font-mono uppercase text-pm-muted">{{ $courier->code }}</span>
                                </div>
                            </div>
                            <form action="{{ route('admin.shipping.couriers.update', $courier) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $courier->is_active ? '0' : '1' }}">
                                <button type="submit"
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold transition-all {{ $courier->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400' }}"
                                        title="Klik untuk ubah status aktif">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $courier->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ $courier->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </div>

                        {{-- Services list --}}
                        <div class="mt-3.5 space-y-2">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-pm-muted">Layanan Ekspedisi</p>
                            @forelse($courier->services as $service)
                                <div class="flex items-center justify-between rounded-xl bg-pm-cream/50 p-2.5 text-xs dark:bg-gray-800/60">
                                    <div>
                                        <p class="font-bold text-pm-charcoal dark:text-gray-200">{{ $service->service_name }}</p>
                                        <p class="text-[10px] text-pm-muted">
                                            Kode: <span class="font-mono uppercase">{{ $service->service_code }}</span>
                                            @if($service->estimated_days)
                                                · Estimasi: {{ $service->estimated_days }}
                                            @endif
                                            · {{ $service->rates->count() }} tarif
                                        </p>
                                    </div>
                                    <form action="{{ route('admin.shipping.services.update', $service) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $service->is_active ? '0' : '1' }}">
                                        <button type="submit"
                                                class="rounded-lg px-2 py-0.5 text-[10px] font-bold {{ $service->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $service->is_active ? 'Aktif' : 'Off' }}
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-[11px] text-pm-muted italic">Belum ada layanan untuk kurir ini.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-pm-border p-8 text-center text-sm text-pm-muted dark:border-gray-800">
                    Belum ada ekspedisi kurir terdaftar. Tambahkan kurir pertama di atas.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ════════════════════════════════════════
         SECTION 2: MANAJEMEN TARIF ONGKIR
    ════════════════════════════════════════ --}}
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-pm-charcoal dark:text-gray-100">Aturan & Tarif Pengiriman Wilayah</h2>
            <p class="text-xs text-pm-muted dark:text-gray-400">Tarif otomatis dihitung berdasarkan alamat kota/provinsi tujuan dan berat produk.</p>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('admin.shipping.index') }}" class="flex flex-wrap items-center gap-2">
            <select name="courier_id" class="pm-input !py-1.5 !px-3 text-xs w-auto">
                <option value="">Semua Kurir</option>
                @foreach($couriers as $c)
                    <option value="{{ $c->id }}" @selected(request('courier_id') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="text"
                   name="province"
                   value="{{ request('province') }}"
                   placeholder="Cari provinsi..."
                   class="pm-input !py-1.5 !px-3 text-xs w-36 sm:w-48">
            <button type="submit" class="pm-button !py-1.5 !px-3 text-xs">Filter</button>
            @if(request()->hasAny(['courier_id', 'province']))
                <a href="{{ route('admin.shipping.index') }}" class="pm-secondary !py-1.5 !px-3 text-xs">Reset</a>
            @endif
        </form>
    </div>

    <p class="mb-3 text-xs text-pm-muted sm:hidden">Geser tabel ke samping untuk melihat detail tarif dan aksi.</p>

    <div class="pm-card overflow-hidden">
        <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel Tarif Pengiriman">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Kurir & Layanan</th>
                        <th>Wilayah Tujuan</th>
                        <th>Tarif Dasar (1 kg)</th>
                        <th>Tarif Tambahan / kg</th>
                        <th>Batas Berat</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pm-border dark:divide-gray-800">
                    @forelse($rates as $rate)
                        <tr class="transition-colors hover:bg-pm-cream/50 dark:hover:bg-gray-800/50">
                            <td class="font-bold text-pm-charcoal dark:text-gray-100 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-pm-orange-lt px-1.5 py-0.5 text-[10px] font-bold text-pm-orange uppercase">
                                        {{ $rate->service?->courier?->name ?? 'Kurir' }}
                                    </span>
                                    <span class="text-xs">{{ $rate->service?->service_name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                <p class="text-xs font-semibold text-pm-charcoal dark:text-gray-200">{{ $rate->province }}</p>
                                <p class="text-[11px] text-pm-muted">{{ $rate->city ?: 'Seluruh Kota / Kabupaten' }}</p>
                            </td>
                            <td class="text-xs font-bold text-pm-charcoal dark:text-gray-200 whitespace-nowrap">
                                Rp{{ number_format($rate->base_cost, 0, ',', '.') }}
                            </td>
                            <td class="text-xs text-pm-charcoal dark:text-gray-200 whitespace-nowrap">
                                Rp{{ number_format($rate->cost_per_kg, 0, ',', '.') }} <span class="text-[10px] text-pm-muted">/ kg</span>
                            </td>
                            <td class="text-xs text-pm-muted whitespace-nowrap">
                                {{ number_format($rate->min_weight) }}g – {{ number_format($rate->max_weight) }}g
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $rate->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                    {{ $rate->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2" x-data="{ editing: false }">
                                    <button type="button"
                                            @click="editing = !editing"
                                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-pm-orange hover:bg-pm-orange-lt">
                                        Edit
                                    </button>

                                    <x-admin-delete :action="route('admin.shipping.rates.destroy', $rate)"
                                                    :name="'Tarif '.$rate->province.($rate->city ? ' - '.$rate->city : '')"
                                                    :id="'delete-rate-'.$rate->id"
                                                    message="Tarif ongkir wilayah ini akan dihapus dari perhitungan otomatis checkout." />

                                    {{-- Quick Edit Modal / Popover --}}
                                    <div x-show="editing"
                                         x-transition
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                                         @click.self="editing = false">
                                        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-hover dark:bg-gray-900 text-left">
                                            <div class="flex items-center justify-between pb-4 border-b border-pm-border dark:border-gray-800">
                                                <h3 class="font-bold text-sm text-pm-charcoal dark:text-gray-100">
                                                    Edit Tarif: {{ $rate->province }} ({{ $rate->service?->courier?->name }} {{ $rate->service?->service_name }})
                                                </h3>
                                                <button type="button" @click="editing = false" class="text-pm-muted hover:text-pm-charcoal">✕</button>
                                            </div>
                                            <form action="{{ route('admin.shipping.rates.update', $rate) }}" method="POST" class="mt-4 space-y-4">
                                                @csrf
                                                @method('PATCH')
                                                <div>
                                                    <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Tarif Dasar (1 kg pertama)</label>
                                                    <div class="relative">
                                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-pm-muted">Rp</span>
                                                        <input type="number" name="base_cost" value="{{ (int)$rate->base_cost }}" required min="0" class="pm-input !pl-9 text-xs">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Tarif Tambahan per Kg</label>
                                                    <div class="relative">
                                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-pm-muted">Rp</span>
                                                        <input type="number" name="cost_per_kg" value="{{ (int)$rate->cost_per_kg }}" required min="0" class="pm-input !pl-9 text-xs">
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 pt-2">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input type="checkbox" id="rate-active-{{ $rate->id }}" name="is_active" value="1" @checked($rate->is_active) class="h-4 w-4 rounded border-pm-border text-pm-orange focus:ring-pm-orange">
                                                    <label for="rate-active-{{ $rate->id }}" class="text-xs font-semibold text-pm-charcoal dark:text-gray-200">Tarif Aktif Digunakan</label>
                                                </div>
                                                <div class="flex items-center justify-end gap-2 pt-4 border-t border-pm-border dark:border-gray-800">
                                                    <button type="button" @click="editing = false" class="pm-secondary !py-2 !px-4 text-xs">Batal</button>
                                                    <button type="submit" class="pm-button !py-2 !px-4 text-xs">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-sm text-pm-muted dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-pm-cream text-pm-muted dark:bg-gray-800">🚚</span>
                                    <p class="mt-3 font-semibold text-pm-charcoal dark:text-gray-200">Belum ada tarif pengiriman yang sesuai.</p>
                                    <p class="text-xs mt-1">Tambahkan tarif baru menggunakan tombol di sudut kanan atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($rates->hasPages())
        <div class="mt-6">
            {{ $rates->links() }}
        </div>
    @endif

    {{-- ════════════════════════════════════════
         MODAL 1: TAMBAH EKSPEDISI KURIR
    ════════════════════════════════════════ --}}
    <x-modal name="add-courier-modal" focusable>
        <form action="{{ route('admin.shipping.couriers.store') }}" method="POST" class="p-6 sm:p-7 text-pm-charcoal dark:text-gray-100">
            @csrf
            <div class="flex items-center justify-between pb-4 border-b border-pm-border dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-pm-orange-lt text-pm-orange">🚚</span>
                    <div>
                        <h2 class="text-base font-bold text-pm-charcoal dark:text-gray-100">Tambah Ekspedisi Kurir</h2>
                        <p class="text-xs text-pm-muted">Daftarkan mitra ekspedisi baru ke sistem PawMart.</p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('close')" class="text-pm-muted hover:text-pm-charcoal">✕</button>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Nama Kurir</label>
                    <input type="text" name="name" required placeholder="Contoh: Anteraja" class="pm-input text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Kode Kurir (Singkat/Unik)</label>
                    <input type="text" name="code" required placeholder="Contoh: anteraja" class="pm-input text-xs font-mono uppercase">
                    <p class="text-[11px] text-pm-muted mt-1">Hanya huruf kecil, angka, atau tanda strip.</p>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="courier_active" name="is_active" value="1" checked class="h-4 w-4 rounded border-pm-border text-pm-orange focus:ring-pm-orange">
                    <label for="courier_active" class="text-xs font-semibold text-pm-charcoal dark:text-gray-200">Langsung Aktifkan Ekspedisi</label>
                </div>
            </div>

            <div class="mt-7 flex justify-end gap-3 border-t border-pm-border pt-4 dark:border-gray-800">
                <button type="button" class="pm-secondary !py-2 !px-4 text-xs" x-on:click="$dispatch('close')">Batal</button>
                <button type="submit" class="pm-button !py-2 !px-4 text-xs">Simpan Ekspedisi</button>
            </div>
        </form>
    </x-modal>

    {{-- ════════════════════════════════════════
         MODAL 2: TAMBAH LAYANAN KURIR
    ════════════════════════════════════════ --}}
    <x-modal name="add-service-modal" focusable>
        <form action="{{ route('admin.shipping.services.store') }}" method="POST" class="p-6 sm:p-7 text-pm-charcoal dark:text-gray-100">
            @csrf
            <div class="flex items-center justify-between pb-4 border-b border-pm-border dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-pm-orange-lt text-pm-orange">📦</span>
                    <div>
                        <h2 class="text-base font-bold text-pm-charcoal dark:text-gray-100">Tambah Layanan Kurir</h2>
                        <p class="text-xs text-pm-muted">Contoh: JNE YES, J&T EZ, SiCepat BEST.</p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('close')" class="text-pm-muted hover:text-pm-charcoal">✕</button>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Pilih Mitra Kurir</label>
                    <select name="courier_id" required class="pm-input text-xs">
                        <option value="">-- Pilih Kurir --</option>
                        @foreach($couriers as $courier)
                            <option value="{{ $courier->id }}">{{ $courier->name }} ({{ strtoupper($courier->code) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Nama Layanan</label>
                    <input type="text" name="service_name" required placeholder="Contoh: Next Day" class="pm-input text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Kode Layanan</label>
                    <input type="text" name="service_code" required placeholder="Contoh: nd / express" class="pm-input text-xs font-mono uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Estimasi Pengiriman (Opsional)</label>
                    <input type="text" name="estimated_days" placeholder="Contoh: 1 hari kerja" class="pm-input text-xs">
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="service_active" name="is_active" value="1" checked class="h-4 w-4 rounded border-pm-border text-pm-orange focus:ring-pm-orange">
                    <label for="service_active" class="text-xs font-semibold text-pm-charcoal dark:text-gray-200">Aktifkan Layanan Ini</label>
                </div>
            </div>

            <div class="mt-7 flex justify-end gap-3 border-t border-pm-border pt-4 dark:border-gray-800">
                <button type="button" class="pm-secondary !py-2 !px-4 text-xs" x-on:click="$dispatch('close')">Batal</button>
                <button type="submit" class="pm-button !py-2 !px-4 text-xs">Simpan Layanan</button>
            </div>
        </form>
    </x-modal>

    {{-- ════════════════════════════════════════
         MODAL 3: TAMBAH TARIF PENGIRIMAN
    ════════════════════════════════════════ --}}
    <x-modal name="add-rate-modal" focusable>
        <form action="{{ route('admin.shipping.rates.store') }}" method="POST" class="p-6 sm:p-7 text-pm-charcoal dark:text-gray-100">
            @csrf
            <div class="flex items-center justify-between pb-4 border-b border-pm-border dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-pm-orange-lt text-pm-orange">🏷️</span>
                    <div>
                        <h2 class="text-base font-bold text-pm-charcoal dark:text-gray-100">Tambah Tarif Ongkos Kirim</h2>
                        <p class="text-xs text-pm-muted">Atur tarif dasar (1kg) dan biaya per kg tambahan untuk wilayah tertentu.</p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('close')" class="text-pm-muted hover:text-pm-charcoal">✕</button>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Pilih Layanan Ekspedisi</label>
                    <select name="courier_service_id" required class="pm-input text-xs">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach($couriers as $courier)
                            <optgroup label="{{ $courier->name }}">
                                @foreach($courier->services as $service)
                                    <option value="{{ $service->id }}">{{ $courier->name }} - {{ $service->service_name }} ({{ strtoupper($service->service_code) }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Provinsi Tujuan</label>
                        <input type="text" name="province" required placeholder="Contoh: Jawa Barat" class="pm-input text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Kota / Kabupaten (Opsional)</label>
                        <input type="text" name="city" placeholder="Kosongkan untuk se-provinsi" class="pm-input text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Tarif Dasar (1 kg pertama)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-pm-muted">Rp</span>
                            <input type="number" name="base_cost" required min="0" placeholder="15000" class="pm-input !pl-9 text-xs">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Tarif Tambahan / kg</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-pm-muted">Rp</span>
                            <input type="number" name="cost_per_kg" required min="0" placeholder="15000" class="pm-input !pl-9 text-xs">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Berat Minimum (gram)</label>
                        <input type="number" name="min_weight" value="0" min="0" class="pm-input text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">Berat Maksimum (gram)</label>
                        <input type="number" name="max_weight" value="30000" min="1" class="pm-input text-xs">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="rate_active" name="is_active" value="1" checked class="h-4 w-4 rounded border-pm-border text-pm-orange focus:ring-pm-orange">
                    <label for="rate_active" class="text-xs font-semibold text-pm-charcoal dark:text-gray-200">Aktifkan Tarif Ini</label>
                </div>
            </div>

            <div class="mt-7 flex justify-end gap-3 border-t border-pm-border pt-4 dark:border-gray-800">
                <button type="button" class="pm-secondary !py-2 !px-4 text-xs" x-on:click="$dispatch('close')">Batal</button>
                <button type="submit" class="pm-button !py-2 !px-4 text-xs">Simpan Tarif</button>
            </div>
        </form>
    </x-modal>
</x-admin-layout>
