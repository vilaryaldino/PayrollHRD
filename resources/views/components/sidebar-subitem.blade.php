@props(['href', 'icon' => 'bi-circle', 'title', 'active' => false])

<a href="{{ $href }}" 
   class="flex items-center pl-10 pr-3 py-2 text-sm transition-colors rounded-md group {{ $active ? 'text-amber-500 font-medium' : 'text-[#a1a5b7] hover:text-white' }}">
    <i class="bi {{ $icon }} mr-3 text-[10px] {{ $active ? 'text-amber-500' : 'opacity-70 group-hover:opacity-100' }}"></i>
    <span class="whitespace-nowrap">{{ $title }}</span>
</a>
