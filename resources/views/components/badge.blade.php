@props(['color' => 'gray', 'icon' => null])

@php
$colors = [
    'emerald' => 'bg-emerald-100 text-emerald-700',
    'rose' => 'bg-rose-100 text-rose-700',
    'blue' => 'bg-blue-100 text-blue-700',
    'amber' => 'bg-amber-100 text-amber-700',
    'indigo' => 'bg-indigo-100 text-indigo-700',
    'slate' => 'bg-slate-100 text-slate-700',
    'gray' => 'bg-gray-100 text-gray-700',
];
$colorClass = $colors[$color] ?? $colors['gray'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {$colorClass}"]) }}>
    @if($icon)
        <i class="bi {{ $icon }}"></i>
    @endif
    {{ $slot }}
</span>
