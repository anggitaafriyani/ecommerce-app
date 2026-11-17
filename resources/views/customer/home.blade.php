<x-store-layout>
    <section class="mx-auto grid max-w-7xl items-center gap-8 px-5 py-12 sm:px-8 sm:py-16 lg:grid-cols-2 lg:gap-14">
        <div>
            <span class="inline-flex rounded-full bg-[#E8B98A]/30 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[#2F5D50] dark:text-green-300">Untuk mereka yang selalu ada</span>
            <h1 class="mt-6 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-6xl">Kecil kakinya.<br><span class="text-[#2F5D50] dark:text-green-300">Besar bahagianya.</span></h1>
            <p class="mt-6 max-w-lg text-base leading-8 text-gray-600 dark:text-gray-300">Dari mangkuk makan hingga waktu bermain, temukan kebutuhan untuk setiap momen bersama hewan kesayangan Anda.</p>
            <div class="mt-8 flex flex-wrap gap-3"><a class="pm-button" href="{{ route('products.index') }}">Jelajahi produk →</a><a class="pm-secondary" href="#categories">Pilih kategori</a></div>
            <p class="mt-6 text-xs text-gray-500">Makanan, perawatan, dan aksesori hewan peliharaan.</p>
        </div>
        <div class="relative overflow-hidden rounded-[2.5rem] bg-[#E8B98A]/30 p-6 sm:p-10">
            <svg viewBox="0 0 480 380" role="img" aria-label="Ilustrasi kucing dan anjing, sahabat kecil PawMart" class="mx-auto w-full max-w-lg">
                <circle cx="248" cy="176" r="150" fill="#F9F4E9"/><path d="M38 332c79-39 326-39 404 0" fill="none" stroke="#2F5D50" stroke-width="3"/>
                <path d="M75 317c-22-28-32-48-29-82m29 58c-38-2-48-16-43-35 25-1 39 11 43 35m-10-12c25-10 35-27 27-43-23 5-31 19-27 43" fill="#739386"/>
                <path d="M290 318c-5-22 0-57 15-79l-7-92 31 23c29-14 54-8 68 10l27-18-10 90c19 17 22 45 14 66z" fill="#C88657"/>
                <path d="M308 188l6-24 18 18m63 4 19-12-6 28" fill="#E8B98A"/>
                <ellipse cx="364" cy="238" rx="59" ry="58" fill="#E8B98A"/><ellipse cx="364" cy="294" rx="39" ry="29" fill="#FAF7F2"/>
                <path d="M335 226q7-9 14 0m31 0q7-9 14 0" fill="none" stroke="#25302B" stroke-width="4" stroke-linecap="round"/><path d="m358 240 7 7 7-7z" fill="#25302B"/><path d="M365 247v7m0 0q-11 9-18 0m18 0q11 9 18 0" fill="none" stroke="#25302B" stroke-width="3"/>
                <path d="M400 306c43 4 47-56 33-66" fill="none" stroke="#C88657" stroke-width="17" stroke-linecap="round"/>
                <path d="M129 318c-8-30-1-64 19-90l4-74h110l6 75c24 22 26 61 16 89z" fill="#2F5D50"/>
                <ellipse cx="206" cy="199" rx="68" ry="67" fill="#FAF7F2"/><path d="M160 137c-34-5-49 33-44 63 3 23 21 28 32 4l22-63m85-4c34-5 49 33 44 63-3 23-21 28-32 4l-22-63" fill="#2F5D50"/>
                <ellipse cx="182" cy="194" rx="5" ry="7" fill="#25302B"/><ellipse cx="231" cy="194" rx="5" ry="7" fill="#25302B"/><ellipse cx="207" cy="218" rx="11" ry="8" fill="#25302B"/><path d="M194 236q13 14 26 0" fill="none" stroke="#25302B" stroke-width="4" stroke-linecap="round"/><path d="M180 262h54" stroke="#E8B98A" stroke-width="12"/><circle cx="208" cy="274" r="9" fill="#E8B98A"/>
                <path d="M164 318v-27m85 27v-27" stroke="#FAF7F2" stroke-width="15" stroke-linecap="round"/>
                <path d="M117 94c-13-23-34-8-22 9l22 21 22-21c12-17-9-32-22-9z" fill="#E8B98A"/>
                <path d="m381 78 4 12 13 1-10 8 3 13-10-7-11 7 4-13-10-8 13-1z" fill="#739386"/>
            </svg>
            <div class="mx-auto w-fit rounded-full bg-white px-5 py-3 text-center text-sm font-semibold text-[#2F5D50]">Teman kecil. Cinta tanpa batas.</div>
        </div>
    </section>
    <section id="categories" class="mx-auto max-w-7xl scroll-mt-6 px-5 py-8 sm:px-8">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-widest text-[#2F5D50] dark:text-green-300">Sesuai kebutuhan mereka</p><h2 class="mt-2 text-3xl font-semibold tracking-tight">Mulai dari kategori</h2></div><a href="{{ route('products.index') }}" class="pm-link">Semua produk →</a></div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="pm-card flex items-center justify-between gap-4 p-6 transition hover:border-[#2F5D50]"><div><h3 class="font-semibold">{{ $category->name }}</h3><p class="mt-2 text-sm text-gray-500">{{ $category->products_count }} produk</p></div><span aria-hidden="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E8B98A]/30 text-[#2F5D50]">↗</span></a>
            @empty<p class="text-sm text-gray-500">Kategori sedang disiapkan. Silakan kembali lagi nanti.</p>@endforelse
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-5 py-10 sm:px-8">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-widest text-[#2F5D50] dark:text-green-300">Kenali isi toko kami</p><h2 class="mt-2 text-3xl font-semibold tracking-tight">Terbaru di PawMart</h2></div><a href="{{ route('products.index') }}" class="pm-link">Lihat seluruh katalog →</a></div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($products as $product)<x-product-card :product="$product" />@empty<p class="text-gray-500">Produk sedang disiapkan. Nantikan koleksi kami.</p>@endforelse
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-5 pt-8 sm:px-8"><div class="rounded-3xl bg-[#2F5D50] px-7 py-10 text-white sm:p-12"><p class="text-xs uppercase tracking-widest text-[#E8B98A]">Setiap hari, sepenuh hati</p><h2 class="mt-4 max-w-2xl text-3xl font-semibold leading-snug">Perhatian kecil untuk sahabat yang berarti besar.</h2><p class="mt-4 max-w-2xl text-sm leading-7 text-white/80">Jelajahi detail produk dan temukan kebutuhan yang sesuai dengan hewan kesayangan Anda. Tambahkan pilihan Anda ke keranjang dan buat pesanan.</p><a class="mt-6 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-semibold text-[#2F5D50] focus:ring-2 focus:ring-white" href="{{ route('products.index') }}">Temukan kebutuhannya →</a></div></section>
</x-store-layout>
