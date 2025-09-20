@if ($paginator->hasPages())
    <nav aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between w-100">
        <div class="flex justify-between flex-1 sm:hidden mt-2 ml-3 mb-5">
            @if ($paginator->onFirstPage())
                <span class="btn-lg relative inline-flex items-center px-4 py-2 text-lg font-medium text-gray-300 bg-dark text-secondary border-gray-300 cursor-default leading-5 rounded-md">
                    <i class="fas fa-chevron-left"></i> Trước
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}#co-the" class="btn-dark btn-lg relative inline-flex items-center px-4 py-2 text-lg font-medium text-gray-700 bg-dark text-light border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 text-decoration-none">
                    <i class="fas fa-chevron-left"></i> Trước
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}#co-the" class="btn-dark btn-lg relative inline-flex items-center px-4 py-2 ml-3 text-lg font-medium text-gray-700 bg-dark text-light border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 text-decoration-none">
                    Sau <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="btn-lg relative inline-flex items-center px-4 py-2 ml-3 text-lg font-medium text-gray-300 bg-dark text-secondary border-gray-300 cursor-default leading-5 rounded-md">
                    Sau <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
