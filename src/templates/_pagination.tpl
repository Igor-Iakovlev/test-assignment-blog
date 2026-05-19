{if $paginator->totalPages > 1}
    <nav class="pagination">
        {if $paginator->hasPrev()}
            <a class="pagination__prev" href="{$paginator->urlFor($paginator->currentPage - 1)}">&laquo; Prev</a>
        {/if}

        <span class="pagination__current">Page {$paginator->currentPage} of {$paginator->totalPages}</span>

        {if $paginator->hasNext()}
            <a class="pagination__next" href="{$paginator->urlFor($paginator->currentPage + 1)}">Next &raquo;</a>
        {/if}
    </nav>
{/if}
