@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center">
        <span class="relative z-0 inline-flex shadow-sm rounded-md rtl:space-x-reverse">
            {{-- Previous Page Arrow --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true">
                    <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endif

            {{-- Slider Container (> 3 Pages) --}}
            @php
                $isSlider = $paginator->lastPage() > 3;
            @endphp

            @if ($isSlider)
                <div class="flex overflow-x-auto no-scrollbar scroll-smooth w-48 sm:w-64 md:w-80 cursor-grab pagination-slider-container -ml-px pl-px" style="scrollbar-width: none; -ms-overflow-style: none;">
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true">
                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">{{ $element }}</span>
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 bg-gray-100">{{ $page }}</span>
                            </span>
                        @else
                            <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-750 transition ease-in-out duration-150">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($isSlider)
                </div>
            @endif

            {{-- Next Page Arrow --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1.414 1.414 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true">
                    <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5" aria-hidden="true">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1.414 1.414 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </span>
            @endif
        </span>
    </nav>

    @if ($paginator->lastPage() > 3)
        @once
            <style>
                .no-scrollbar::-webkit-scrollbar {
                    display: none;
                }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const sliders = document.querySelectorAll('.pagination-slider-container');

                    sliders.forEach(slider => {
                        const activePage = slider.querySelector('[aria-current="page"]');
                        if (activePage) {
                            slider.scrollLeft = activePage.offsetLeft - (slider.offsetWidth / 2) + (activePage.offsetWidth / 2);
                        }

                        let isDown = false;
                        let isDragging = false;
                        let startX;
                        let scrollLeft;

                        slider.addEventListener('mousedown', (e) => {
                            isDown = true;
                            isDragging = false;
                            slider.classList.add('cursor-grabbing');
                            slider.classList.remove('cursor-grab');
                            startX = e.pageX - slider.offsetLeft;
                            scrollLeft = slider.scrollLeft;
                        });

                        slider.addEventListener('mouseleave', () => {
                            isDown = false;
                            slider.classList.remove('cursor-grabbing');
                            slider.classList.add('cursor-grab');
                        });

                        slider.addEventListener('mouseup', () => {
                            isDown = false;
                            slider.classList.remove('cursor-grabbing');
                            slider.classList.add('cursor-grab');
                        });

                        slider.addEventListener('mousemove', (e) => {
                            if (!isDown) return;
                            e.preventDefault();
                            isDragging = true;
                            const x = e.pageX - slider.offsetLeft;
                            const walk = (x - startX) * 2;
                            slider.scrollLeft = scrollLeft - walk;
                        });

                        slider.addEventListener('click', (e) => {
                            if (isDragging) {
                                e.preventDefault();
                                e.stopPropagation();
                                isDragging = false;
                            }
                        });

                        slider.addEventListener('wheel', (e) => {
                            if (e.deltaY !== 0) {
                                e.preventDefault();
                                slider.scrollLeft += e.deltaY;
                            }
                        }, { passive: false });
                    });
                });
            </script>
        @endonce
    @endif
@endif
