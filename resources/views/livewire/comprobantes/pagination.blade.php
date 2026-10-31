@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm leading-5 text-muted">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium text-ink">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium text-ink">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-medium text-ink">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>

        <div class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="inline-flex min-h-10 cursor-not-allowed items-center justify-center rounded-control border border-line bg-canvas px-3 text-sm font-medium text-muted opacity-60">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" aria-label="{{ __('pagination.previous') }}" class="inline-flex min-h-10 items-center justify-center rounded-control border border-line bg-white px-3 text-sm font-medium text-ink transition hover:bg-canvas focus:z-10 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 focus:ring-offset-canvas disabled:cursor-not-allowed disabled:opacity-50">
                    {!! __('pagination.previous') !!}
                </button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="inline-flex min-h-10 items-center justify-center px-2 text-sm font-medium text-muted">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-control border border-aequitas-900 bg-aequitas-900 px-3 text-sm font-semibold text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-control border border-line bg-white px-3 text-sm font-medium text-ink transition hover:bg-canvas focus:z-10 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 focus:ring-offset-canvas disabled:cursor-not-allowed disabled:opacity-50">
                                    {{ $page }}
                                </button>
                            @endif
                        </span>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" aria-label="{{ __('pagination.next') }}" class="inline-flex min-h-10 items-center justify-center rounded-control border border-line bg-white px-3 text-sm font-medium text-ink transition hover:bg-canvas focus:z-10 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 focus:ring-offset-canvas disabled:cursor-not-allowed disabled:opacity-50">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="inline-flex min-h-10 cursor-not-allowed items-center justify-center rounded-control border border-line bg-canvas px-3 text-sm font-medium text-muted opacity-60">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>
    </nav>
@endif
