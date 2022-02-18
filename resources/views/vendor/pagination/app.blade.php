@if ($paginator->hasPages())
    <ol id="pageNation">
        {{-- 最初のページ --}}
        <li>
            <a href="{{ $paginator->onFirstPage() ? '#' : $paginator->url(1) }}"><span class="material-icons">first_page</span></a>
        </li>
            
        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li><span class="stay">{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- 最後のページ --}}
        <li>
            <a href="{{ $paginator->currentPage() == $paginator->lastPage() ? "#" : $paginator->url($paginator->lastPage()) }}"><span class="material-icons">last_page</span></a>
        </li>
    </ol>
@endif
