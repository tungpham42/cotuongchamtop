@if ($paginator->hasPages())
    <nav aria-label="{{ __('Phân trang') }}">
        <ul class="gm-pagination-list">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="gm-page-link gm-disabled"><i class="fa-solid fa-angle-left"></i></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="gm-page-link">
                        <i class="fa-solid fa-angle-left"></i>
                    </a>
                @endif
            </li>

            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                <li>
                    @if ($page == $paginator->currentPage())
                        <span class="gm-page-link gm-active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="gm-page-link">{{ $page }}</a>
                    @endif
                </li>
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="gm-page-link">
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                @else
                    <span class="gm-page-link gm-disabled"><i class="fa-solid fa-angle-right"></i></span>
                @endif
            </li>
        </ul>
    </nav>

    <style>
        .gm-pagination-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            list-style: none;
            gap: 6px;
            padding: 0;
            margin: 0;
        }

        .gm-page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--royal-gold-light, #fff2cc);
            background: var(--glass-bg-dark, rgba(11, 12, 16, .85));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border, rgba(255, 215, 0, .55));
            box-shadow:
                var(--liquid-shadow, 0 8px 32px rgba(0, 0, 0, .8)),
                inset 0 2px 8px rgba(255, 255, 255, .15);
            transition: all .3s cubic-bezier(.175, .885, .32, 1.275);
        }

        .gm-page-link:hover {
            color: var(--royal-gold, #ffd700);
            border-color: var(--royal-gold, #ffd700);
            box-shadow:
                0 10px 20px rgba(0, 0, 0, .8),
                0 0 15px rgba(212, 175, 55, .35),
                inset 0 2px 8px rgba(255, 255, 255, .2);
            transform: translateY(-2px);
        }

        .gm-active {
            background: linear-gradient(135deg, var(--royal-gold, #ffd700), #b89020) !important;
            color: var(--royal-bg, #0b0c10) !important;
            border-color: var(--royal-gold, #ffd700) !important;
            text-shadow: none;
            box-shadow: 0 0 15px rgba(255, 215, 0, .6);
        }

        .gm-disabled {
            opacity: .35;
            pointer-events: none;
        }
    </style>
@endif
