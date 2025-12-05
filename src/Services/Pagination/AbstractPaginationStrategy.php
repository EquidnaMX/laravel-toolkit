<?php

namespace Equidna\Toolkit\Services\Pagination;

use Equidna\Toolkit\Contracts\PaginationStrategyInterface;
use Equidna\Toolkit\Exceptions\ConfigurationException;
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
        $params = $request->except($this->excludedRequestParameters());

        if (empty($params)) {
            $queryParams = $request->query();
            if (is_object($queryParams) && method_exists($queryParams, 'all')) {
                $queryParams = $queryParams->all();
            }

            $params = array_diff_key(
                (array) $queryParams,
                array_flip($this->excludedRequestParameters()),
            );
        }

        if (empty($params)) {
            $params = array_diff_key(
                (array) $request->all(),
                array_flip($this->excludedRequestParameters()),
            );
        }

        $paginator->appends($params);
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
        $resolved = $itemsPerPage ?? config('equidna.paginator.page_items');
        $resolved = (int) $resolved;

        if ($resolved <= 0) {
            throw new ConfigurationException('Pagination per-page value must be a positive integer.');
        }

        return $resolved;
    }
}
