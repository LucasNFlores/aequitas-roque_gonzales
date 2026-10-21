@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-control border-line text-ink shadow-sm placeholder:text-gray-400 focus:border-gold-600 focus:ring-gold-600 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500']) }}>
