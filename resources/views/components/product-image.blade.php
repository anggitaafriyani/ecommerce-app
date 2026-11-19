@props(['product'])
<div {{ $attributes->merge(['class' => 'flex aspect-square items-center justify-center overflow-hidden bg-pm-cream/70 dark:bg-gray-800']) }}>
    @if($product->image_path)
        <img src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
    @else
        <div class="flex flex-col items-center justify-center gap-2.5 p-6 text-center text-pm-muted/60 dark:text-gray-500">
            <svg class="h-12 w-12 text-pm-orange/30 dark:text-orange-500/20" viewBox="0 0 64 64" fill="currentColor" aria-hidden="true">
                <ellipse cx="17" cy="22" rx="7" ry="10" transform="rotate(-25 17 22)"/>
                <ellipse cx="32" cy="15" rx="7" ry="10"/>
                <ellipse cx="47" cy="22" rx="7" ry="10" transform="rotate(25 47 22)"/>
                <path d="M32 31c-7 0-10 5-15 11-8 11-1 18 8 14 5-2 9-2 14 0 9 4 16-3 8-14-5-6-8-11-15-11z"/>
            </svg>
            <span class="text-xs font-medium text-pm-muted">Foto produk belum tersedia</span>
        </div>
    @endif
</div>