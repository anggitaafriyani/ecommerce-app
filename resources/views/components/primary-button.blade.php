<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-2.5 bg-pm-orange border border-transparent rounded-full font-bold text-xs text-white uppercase tracking-wider hover:bg-pm-orange-dk focus:bg-pm-orange-dk active:bg-pm-orange-dk focus:outline-none focus:ring-2 focus:ring-pm-orange focus:ring-offset-2 shadow-xs transition duration-150']) }}>
    {{ $slot }}
</button>
