@props(['product'])
<div {{ $attributes->merge(['class' => 'flex aspect-square items-center justify-center overflow-hidden bg-[#F0EDE6] dark:bg-gray-800']) }}>
    @if($product->image_path)
        <img src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-contain" loading="lazy">
    @else
        <div class="flex flex-col items-center gap-3 p-5 text-center text-[#63766A] dark:text-gray-400">
            <svg aria-hidden="true" width="54" height="54" viewBox="0 0 64 64" fill="currentColor"><ellipse cx="17" cy="22" rx="7" ry="10" transform="rotate(-25 17 22)"/><ellipse cx="32" cy="15" rx="7" ry="10"/><ellipse cx="47" cy="22" rx="7" ry="10" transform="rotate(25 47 22)"/><path d="M32 31c-7 0-10 5-15 11-8 11-1 18 8 14 5-2 9-2 14 0 9 4 16-3 8-14-5-6-8-11-15-11z"/></svg>
            <span class="text-xs">Foto produk belum tersedia</span>
        </div>
    @endif
</div>
