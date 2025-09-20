@if ($paginator->hasPages())
    <nav class="w-100 ml-3">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link btn-md bg-dark text-secondary" aria-hidden="true"><i class="fas fa-chevron-left"></i> 이전의</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link btn-md bg-dark text-light" href="{{ $paginator->previousPageUrl() }}#peojeul" rel="prev"><i class="fas fa-chevron-left"></i> 이전의</a>
                </li>
            @endif
            
            <!-- Pagination Elements -->
            @foreach ($elements as $element)
                <!-- Array Of Links -->
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <!--  Use three dots when current page is greater than 4.  -->
                        @if ($paginator->currentPage() > 3 && $page === 2)
                            <li class="page-item disabled bg-dark text-light"><span class="page-link btn-md bg-dark text-light">...</span></li>
                        @endif

                        <!--  Show active page else show the first and last two pages from current page.  -->
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active"><span class="page-link btn-md bg-dark text-light">{{ $page }}</span></li>
                        @elseif ($page === $paginator->currentPage() + 1 || $page === $paginator->currentPage() - 1 || $page === $paginator->lastPage() || $page === 1)
                            <li class="page-item"><a class="page-link btn-md bg-dark text-light" href="{{ $url }}#peojeul">{{ $page }}</a></li>
                        @endif

                        <!--  Use three dots when current page is away from end.  -->
                        @if ($paginator->currentPage() < $paginator->lastPage() - 2 && $page === $paginator->lastPage() - 1)
                            <li class="page-item disabled"><span class="page-link btn-md bg-dark text-light">...</span></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link btn-md bg-dark text-light" href="{{ $paginator->nextPageUrl() }}#peojeul" rel="next">다음 분 <i class="fas fa-chevron-right"></i></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link btn-md bg-dark text-secondary" aria-hidden="true">다음 분 <i class="fas fa-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
