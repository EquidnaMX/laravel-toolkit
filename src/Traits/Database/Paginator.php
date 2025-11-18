<?php

/**
 * @author Erick Escobar
 * @license MIT
 * @version 1.0.0
 *
 */

namespace Equidna\Toolkit\Traits\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Equidna\Toolkit\Helpers\RouteHelper;
use Equidna\Toolkit\Helpers\PaginatorHelper;

trait Paginator
{
    /**
     * Scope a query for pagination with optional transformation.
     *
     * @param Builder $query The query builder instance.
     * @param string $pageName The name of the pagination parameter. Default is 'page'.
     * @param int $page The current page number. Default is 1.
     * @param int|null $page_items The number of items per page. Default is null, which uses the configuration value.
     * @param callable|null $transformation An optional transformation callback to apply to the paginator items.
     *
     * @return LengthAwarePaginator|array The paginated result, either as a LengthAwarePaginator instance or an array if the request is an API call.
     */
    /**
     * Scope a query for pagination with optional transformation.
     *
     * @param Builder $query The query builder instance.
     * @param int|null $page The current page number. Default is 1.
     * @param string $pageName The name of the pagination parameter. Default is 'page'.
     * @param int|null $items_per_page The number of items per page. Default is null, which uses the configuration value.
     * @param bool $set_full_url Whether to set the paginator path to the current URL.
     * @param callable|null $transformation Optional transformation callback for paginator items.
     * @return LengthAwarePaginator|array The paginated result, either as a LengthAwarePaginator or an array for API calls.
     */
    public function scopePaginator(
        Builder $query,
        ?int $page = null,
        string $pageName = 'page',
        ?int $items_per_page = null,
        bool $set_full_url = false,
        null|callable $transformation = null
    ): LengthAwarePaginator|array {

        $paginationLength = $items_per_page ?: config('equidna.paginator.page_items');
        $paginator = $query->paginate(
            $paginationLength,
            ['*'],
            $pageName,
            $page ?: 1
        );

        if (!is_null($transformation)) {
            # used tap to apply the transformation to the paginator items
            # preventing a linter error when using the paginator directly

            tap($paginator, fn(LengthAwarePaginator $paginator) => $paginator->through($transformation));
        }

        if ($set_full_url) {
            PaginatorHelper::setFullURL($paginator);
        }

        if (RouteHelper::wantsJson()) {
            return [
                'data'         => $paginator->items(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ];
        }

        return $paginator;
    }
}
