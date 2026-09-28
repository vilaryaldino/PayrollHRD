@props(['type' => 'button', 'color' => 'primary', 'icon' => null])

@php
$colors = [
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 border-transparent',
    'secondary' => 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300 focus:ring-blue-500',
    'danger' => 'bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500 border-transparent',
    'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 border-transparent',
];
$colorClass = $colors[$color] ?? $colors['primary'];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-lg border px-4 py-2 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors $colorClass"]) }}>
    @if($icon)
        <i class="bi {{ $icon }} mr-2"></i>
    @endif
    {{ $slot }}
</button>
