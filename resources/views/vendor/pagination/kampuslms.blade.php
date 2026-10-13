@if ($paginator->hasPages())
    <nav class="pagination-control" role="navigation" aria-label="Navigasi halaman">
        <p class="pagination-summary">
            Menampilkan {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>

        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="pagination-link is-disabled" aria-disabled="true">Sebelumnya</span>
            @else
                <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    Sebelumnya
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-link is-disabled" aria-disabled="true">{{ $element }}</span>
                @elseif (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="pagination-link is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-link" href="{{ $url }}" aria-label="Halaman {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Berikutnya
                </a>
            @else
                <span class="pagination-link is-disabled" aria-disabled="true">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
