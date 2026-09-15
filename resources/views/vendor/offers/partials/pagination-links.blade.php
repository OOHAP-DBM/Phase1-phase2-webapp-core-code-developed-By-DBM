@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 border border-gray-200 rounded text-gray-300 bg-gray-50 cursor-not-allowed">
                ‹
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="px-3 py-1.5 border border-gray-300 rounded text-gray-600 bg-white hover:bg-gray-50">
                ‹
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)

            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-2 py-1.5 text-gray-400">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1.5 rounded bg-[#2E5B42] text-white font-semibold">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="px-3 py-1.5 border border-gray-300 rounded text-gray-600 bg-white hover:bg-gray-50">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif

        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="px-3 py-1.5 border border-gray-300 rounded text-gray-600 bg-white hover:bg-gray-50">
                ›
            </a>
        @else
            <span class="px-3 py-1.5 border border-gray-200 rounded text-gray-300 bg-gray-50 cursor-not-allowed">
                ›
            </span>
        @endif

    </nav>
@endif
