@props(['title', 'value', 'subtext' => null, 'icon', 'color' => 'blue', 'href' => '#', 'linkText' => 'Lihat Detail'])

@php
    $colorClasses = [
        'blue' => ['border' => 'border-blue-500', 'text' => 'text-blue-600', 'icon' => 'text-blue-500', 'bg' => 'bg-blue-50', 'hover' => 'hover:text-blue-700'],
        'emerald' => ['border' => 'border-emerald-500', 'text' => 'text-emerald-600', 'icon' => 'text-emerald-500', 'bg' => 'bg-emerald-50', 'hover' => 'hover:text-emerald-700'],
        'amber' => ['border' => 'border-amber-500', 'text' => 'text-amber-600', 'icon' => 'text-amber-500', 'bg' => 'bg-amber-50', 'hover' => 'hover:text-amber-700'],
        'rose' => ['border' => 'border-rose-500', 'text' => 'text-rose-600', 'icon' => 'text-rose-500', 'bg' => 'bg-rose-50', 'hover' => 'hover:text-rose-700'],
        'indigo' => ['border' => 'border-indigo-500', 'text' => 'text-indigo-600', 'icon' => 'text-indigo-500', 'bg' => 'bg-indigo-50', 'hover' => 'hover:text-indigo-700'],
    ];
    $c = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="bg-white rounded-xl shadow-sm border-l-4 {{ $c['border'] }} p-5 flex flex-col justify-between h-full hover:shadow-md transition-shadow">
    <div class="flex justify-between items-start">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider {{ $c['text'] }} mb-1">{{ $title }}</p>
            <h3 class="text-3xl font-bold text-slate-800">{{ $value }}</h3>
            @if($subtext)
                <div class="mt-1 text-sm text-slate-500">{!! $subtext !!}</div>
            @endif
        </div>
        <div class="p-3 {{ $c['bg'] }} rounded-lg">
            <i class="bi {{ $icon }} {{ $c['icon'] }} text-2xl"></i>
        </div>
    </div>
    <div class="mt-4 pt-3 border-t border-slate-100">
        <a href="{{ $href }}" class="text-sm font-medium {{ $c['text'] }} {{ $c['hover'] }} flex items-center gap-1 transition-colors group">
            {{ $linkText }} <i class="bi bi-arrow-right group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>
</div>
