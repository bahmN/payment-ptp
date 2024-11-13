@if ($paginator->hasPages())
    <nav class="paginator">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="paginator_previous">
                < </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="paginator_previous">
                        < </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="paginator_next">
                >
            </a>
        @else
            <span class="paginator_next">
                >
            </span>
        @endif
    </nav>
@endif
