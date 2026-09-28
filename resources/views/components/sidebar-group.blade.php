@props(['icon', 'title', 'active' => false])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }" class="my-1">
    <button @click="open = !open" 
            class="w-full flex items-center justify-between px-3 py-2.5 text-sm transition-colors rounded-lg group {{ $active ? 'text-white' : 'text-[#a1a5b7] hover:bg-white/5 hover:text-white' }}">
        <div class="flex items-center">
            <i class="bi {{ $icon }} text-lg w-6 text-center mr-3 transition-colors {{ $active ? 'text-amber-500' : 'group-hover:text-white' }}"></i>
            <span x-show="sidebarOpen" class="whitespace-nowrap font-medium">{{ $title }}</span>
        </div>
        <i x-show="sidebarOpen" class="bi bi-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
    </button>
    <div x-show="open && sidebarOpen" 
         x-collapse 
         class="mt-1 space-y-1 bg-[#1a1a27] rounded-lg py-2 mx-2">
        {{ $slot }}
    </div>
</div>
