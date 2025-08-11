@php
    $thumbnailUrl = $getThumbnailUrl();
    $embedUrl = $getEmbedUrl();
@endphp

<div>
    @if($thumbnailUrl)
        <div x-data="{ open: false }" class="relative">
            <button 
                @click="open = true" 
                class="relative group overflow-hidden rounded-lg hover:opacity-75 transition-opacity"
            >
                <img 
                    src="{{ $thumbnailUrl }}" 
                    alt="Video thumbnail" 
                    class="w-24 h-10 object-cover rounded-lg overflow-hidden"
                />
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-8 h-8 text-white opacity-75 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                    </svg>
                </div>
            </button>

            <div
                x-show="open"
                x-transition
                x-on:keydown.escape.window="open = false"
                class="fixed inset-0 z-50 overflow-hidden"
                style="display: none;"
            >
                <div class="absolute inset-0 bg-black/75"></div>
                
                <div class="fixed inset-0 flex items-center justify-center p-4">
                    <div 
                        class="relative bg-black rounded-lg overflow-hidden w-full max-w-6xl"
                        @click.away="open = false"
                    >
                        <div class="relative">
                            <iframe
                                src="{{ $embedUrl }}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                                allowfullscreen
                                class="w-full aspect-video"
                                style="height: 80vh;"
                            ></iframe>

                            <button
                                @click="open = false"
                                class="absolute top-4 right-10 p-2 text-white bg-black/50 hover:bg-black/70 rounded-full transition-colors"
                                style="margin-right: 10px;"
                            >
                                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-gray-400">لا يوجد فيديو</div>
    @endif
</div>