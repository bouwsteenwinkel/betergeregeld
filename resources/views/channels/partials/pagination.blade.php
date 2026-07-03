@if ($paginator->hasPages())
	<nav class="pager" aria-label="Paginering">
		@if ($paginator->onFirstPage())
			<span class="pager-btn is-disabled" aria-disabled="true">&larr; Vorige</span>
		@else
			<a class="pager-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">&larr; Vorige</a>
		@endif

		<span class="pager-info">Pagina {{ $paginator->currentPage() }} van {{ $paginator->lastPage() }}</span>

		@if ($paginator->hasMorePages())
			<a class="pager-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Volgende &rarr;</a>
		@else
			<span class="pager-btn is-disabled" aria-disabled="true">Volgende &rarr;</span>
		@endif
	</nav>
@endif
