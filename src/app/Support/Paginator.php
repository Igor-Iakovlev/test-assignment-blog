<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Support;

readonly class Paginator
{
    public int $currentPage;
    public int $perPage;
    public int $total;
    public int $totalPages;

    /**
     * @param array<string, scalar> $query
     */
    public function __construct(
        int $currentPage,
        int $perPage,
        int $total,
        public string $baseUrl,
        public array $query = [],
    ) {
        $this->perPage = max(1, $perPage);
        $this->total = max(0, $total);
        $this->totalPages = (int) max(1, ceil($this->total / $this->perPage));
        $this->currentPage = min(max(1, $currentPage), $this->totalPages);
    }

    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function urlFor(int $page): string
    {
        $query = $this->query;
        $query['page'] = $page;

        return $this->baseUrl . '?' . http_build_query($query);
    }
}
