@if ($paginator->hasPages())
    <nav class="w-100 ml-3">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link btn-md bg-dark text-secondary" aria-hidden="true"><i class="fas fa-chevron-left"></i> {{ __('Trước') }}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link btn-md bg-dark text-light" href="{{ $paginator->previousPageUrl() }}&loai={{ __('van-dau') }}#{{ __('van-dau') }}" rel="prev"><i class="fas fa-chevron-left"></i> {{ __('Trước') }}</a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($paginator->currentPage() > 3 && $page === 2)
                            <li class="page-item disabled bg-dark text-light"><span class="page-link btn-md bg-dark text-light">...</span></li>
                        @endif

                        @if ($page == $paginator->currentPage())
                            <li class="page-item active"><span class="page-link btn-md bg-dark text-light">{{ $page }}</span></li>
                        @elseif ($page === $paginator->currentPage() + 1 || $page === $paginator->currentPage() - 1 || $page === $paginator->lastPage() || $page === 1)
                            <li class="page-item"><a class="page-link btn-md bg-dark text-light" href="{{ $url }}&loai={{ __('van-dau') }}#{{ __('van-dau') }}">{{ $page }}</a></li>
                        @endif

                        @if ($paginator->currentPage() < $paginator->lastPage() - 2 && $page === $paginator->lastPage() - 1)
                            <li class="page-item disabled"><span class="page-link btn-md bg-dark text-light">...</span></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link btn-md bg-dark text-light" href="{{ $paginator->nextPageUrl() }}&loai={{ __('van-dau') }}#{{ __('van-dau') }}" rel="next">{{ __('Sau') }} <i class="fas fa-chevron-right"></i></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link btn-md bg-dark text-secondary" aria-hidden="true">{{ __('Sau') }} <i class="fas fa-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
