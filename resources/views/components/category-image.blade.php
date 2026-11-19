@props(['category', 'illustration' => false])

@if($category->image_path)
    <img src="{{ Storage::disk('public')->url($category->image_path) }}" alt="{{ $category->name }}" class="h-full w-full rounded-full object-contain" loading="lazy">
@elseif($illustration)
    <svg class="h-full w-full rounded-full" viewBox="0 0 120 120" role="img" aria-label="{{ $category->name }}">
        <circle cx="60" cy="60" r="60" fill="#fff7ed" />
        @switch(\Illuminate\Support\Str::lower(\Illuminate\Support\Str::squish($category->name)))
            @case('makanan hewan')
                <path d="M37 24h46l5 50H32z" fill="#fed7aa" stroke="#c2410c" stroke-width="3" stroke-linejoin="round" />
                <path d="M37 33h46" stroke="#c2410c" stroke-width="3" />
                <circle cx="60" cy="52" r="12" fill="#f97316" />
                <path d="M26 72h68l-7 23H33z" fill="#f97316" stroke="#c2410c" stroke-width="3" stroke-linejoin="round" />
                <path d="M47 83h26" stroke="#fff7ed" stroke-width="4" stroke-linecap="round" />
                @break
            @case('perawatan & kebersihan')
                <path d="M44 33h30v12H44z" fill="#fb923c" />
                <path d="M59 33V23h22" fill="none" stroke="#9a3412" stroke-width="5" stroke-linecap="round" />
                <rect x="36" y="43" width="46" height="54" rx="12" fill="#fed7aa" stroke="#c2410c" stroke-width="3" />
                <path d="M59 57c-4 7-10 12-10 18a10 10 0 0 0 20 0c0-6-6-11-10-18" fill="#f97316" />
                <circle cx="89" cy="38" r="7" fill="#ffedd5" stroke="#fb923c" stroke-width="2" />
                <circle cx="96" cy="57" r="4" fill="#fed7aa" />
                @break
            @case('aksesori & mainan')
                <circle cx="76" cy="77" r="23" fill="#fb923c" stroke="#c2410c" stroke-width="3" />
                <path d="M55 68c17 0 25 10 27 30M67 56c-2 18 8 28 30 30" fill="none" stroke="#ffedd5" stroke-width="4" />
                <path d="M29 32c-10-7-19 7-10 14-5 10 8 17 15 8l22 10c-2 11 14 15 18 5 12 0 13-16 2-18L47 38c4-11-11-17-18-6z" fill="#fed7aa" stroke="#c2410c" stroke-width="3" stroke-linejoin="round" />
                @break
            @default
                <ellipse cx="35" cy="43" rx="9" ry="12" fill="#fb923c" transform="rotate(-25 35 43)" />
                <ellipse cx="53" cy="31" rx="9" ry="12" fill="#fb923c" />
                <ellipse cx="74" cy="34" rx="9" ry="12" fill="#fb923c" transform="rotate(15 74 34)" />
                <ellipse cx="89" cy="51" rx="9" ry="12" fill="#fb923c" transform="rotate(30 89 51)" />
                <path d="M60 52c-10 0-14 10-23 20-13 16-1 29 13 22 7-4 13-4 20 0 14 7 26-6 13-22-9-10-13-20-23-20z" fill="#ea580c" />
        @endswitch
    </svg>
@else
    <span class="flex flex-col items-center justify-center gap-1 text-center text-pm-muted" aria-label="{{ $category->name }}: perlu gambar">
        <svg class="h-8 w-8 shrink-0" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="3" />
            <circle cx="8" cy="8" r="1.5" />
            <path d="m3 17 5-5 4 4 3-3 6 6" />
        </svg>
        <span class="text-[10px] font-medium">Perlu gambar</span>
    </span>
@endif
