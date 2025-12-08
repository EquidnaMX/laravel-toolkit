<?php

namespace Equidna\Toolkit\Contracts;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PaginationStrategyInterface
{
    /**
     * @param  array<int|string, mixed>|Collection|LengthAwarePaginator|EloquentBuilder|QueryBuilder $data
     */
    public function buildPaginator(
        array|Collection|LengthAwarePaginator|EloquentBuilder|QueryBuilder $data,
        ?int $page = null,
        ?int $itemsPerPage = null,
        bool $setFullUrl = false,
    ): LengthAwarePaginator;

    public function paginateLengthAware(
        EloquentBuilder|QueryBuilder $query,
        ?int $page = null,
        string $pageName = 'page',
        ?int $itemsPerPage = null,
        bool $setFullUrl = false,
        ?callable $transformation = null,
    ): LengthAwarePaginator;

    public function paginateCursor(
        EloquentBuilder|QueryBuilder $query,
        ?int $itemsPerPage = null,
        string $cursorName = 'cursor',
        bool $setFullUrl = false,
        ?callable $transformation = null,
    ): CursorPaginator;

    public function appendCleanedRequest(CursorPaginator|LengthAwarePaginator $paginator, Request $request): void;

    public function setFullURL(CursorPaginator|LengthAwarePaginator $paginator): void;

    /**
     * @return array<int, string>
     */
    public function excludedRequestParameters(): array;
}
