<?php

/**
 * Provides collection-driven pagination helpers for arrays and collections.
 * PHP 8.0+
 * @package   Equidna\Toolkit\Helpers
 * @author    Gabriel Ruelas <gruelasjr@gmail.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://laravel.com/docs/12.x/pagination#manually-creating-a-paginator Documentation
 */

namespace Equidna\Toolkit\Helpers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Builds paginators from in-memory datasets and syncs query parameters.
 */
class PaginatorHelper
{
    public const EXCLUDE_FROM_REQUEST = [
        '_token',
        'page',
        'client_user',
        'client_token',
        'client_token_type',
    ];

    /**
     * Builds a paginator instance backed by an array or collection.
     *
     * The paginator mirrors Laravel's manual paginator creation guidance
     * so the consuming application can attach it to Blade links or APIs.
     *
     * @param  array<int|string, mixed>|Collection $data           Dataset to paginate.
     * @param  int|null                            $page           Page number (defaults to 1).
     * @param  int|null                            $items_per_page Items per page (defaults to config value).
     * @param  bool                                $set_full_url   When true, sets the paginator path to the current URL.
     * @return LengthAwarePaginator                                 Paginator ready for rendering or JSON serialization.
     */
    public static function buildPaginator(
        array|Collection $data,
        ?int $page = null,
        ?int $items_per_page = null,
        bool $set_full_url = false,
    ): LengthAwarePaginator {
        $data = is_array($data) ? collect($data) : $data;

        $paginationLength = $items_per_page ?: config('equidna.paginator.page_items');

        $currentPage = $page ?: 1;

        $paginator = new LengthAwarePaginator(
            $data->forPage(
                (int) $currentPage,
                $paginationLength,
            ),
            $data->count(),
            $paginationLength,
            (int) $currentPage,
        );

        if ($set_full_url) {
            static::setFullURL($paginator);
        }

        return $paginator;
    }

    /**
     * Append cleaned request parameters to the paginator.
     *
     * @param  LengthAwarePaginator $paginator Paginator receiving filtered query parameters.
     * @param  Request              $request   Current HTTP request used for query data.
     * @return void
     */
    public static function appendCleanedRequest(LengthAwarePaginator $paginator, Request $request): void
    {
        $paginator->appends($request->except(static::EXCLUDE_FROM_REQUEST));
    }

    /**
     * Set the paginator path to the current URL.
     *
     * @param  LengthAwarePaginator $paginator Paginator whose base path should mirror the current URL.
     * @return void
     */
    public static function setFullURL(LengthAwarePaginator $paginator): void
    {
        $paginator->setPath(url()->current());
    }
}
