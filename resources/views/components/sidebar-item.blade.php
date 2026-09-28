@props(['href', 'icon', 'title', 'active' => false])

<a href="{{ $href }}" 
   class="flex items-center px-3 py-2.5 my-1 text-sm transition-colors rounded-lg group {{ $active ? 'bg-[#2b2b40] text-white font-medium border-l-4 border-amber-500' : 'text-[#a1a5b7] hover:bg-white/5 hover:text-white' }}">
    <i class="bi {{ $icon }} text-lg {{ $active ? 'text-amber-500' : 'text-[#a1a5b7] group-hover:text-white' }} w-6 text-center mr-3 transition-colors"></i>
    <span x-show="sidebarOpen" class="whitespace-nowrap">{{ $title }}</span>
</a>
