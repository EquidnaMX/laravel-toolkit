<?php

namespace Equidna\Toolkit\Services\Pagination;

use Equidna\Toolkit\Contracts\PaginationStrategyInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class AbstractPaginationStrategy implements PaginationStrategyInterface
{
    public const EXCLUDE_FROM_REQUEST = [
        '_token',
        'page',
        'client_user',
        'client_token',
        'client_token_type',
    ];

    public function appendCleanedRequest(CursorPaginator|LengthAwarePaginator $paginator, Request $request): void
    {
        $paginator->appends($request->except($this->excludedRequestParameters()));
    }

    public function setFullURL(CursorPaginator|LengthAwarePaginator $paginator): void
    {
        $paginator->setPath(url()->current());
    }

    public function excludedRequestParameters(): array
    {
        return self::EXCLUDE_FROM_REQUEST;
    }

    protected function resolveItemsPerPage(?int $itemsPerPage = null): int
    {
        return $itemsPerPage ?: config('equidna.paginator.page_items');
    }
}

