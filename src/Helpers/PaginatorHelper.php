<?php

/**
 * Provides pagination helpers for in-memory datasets and database queries.
 * PHP 8.0+
 * @package   Equidna\Toolkit\Helpers
 * @author    Gabriel Ruelas <gruelasjr@gmail.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://laravel.com/docs/12.x/pagination#manually-creating-a-paginator Documentation
 */

namespace Equidna\Toolkit\Helpers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
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
     * @param  array<int|string, mixed>|Collection|LengthAwarePaginator|EloquentBuilder|QueryBuilder $data           Dataset or builder to paginate.
     * @param  int|null                                                             $page           Page number (defaults to 1).
     * @param  int|null                                                             $items_per_page Items per page (defaults to config value).
     * @param  bool                                                                 $set_full_url   When true, sets the paginator path to the current URL.
     * @return LengthAwarePaginator                                                                Paginator ready for rendering or JSON serialization.
     */
    public static function buildPaginator(
        array|Collection|LengthAwarePaginator|EloquentBuilder|QueryBuilder $data,
        ?int $page = null,
        ?int $items_per_page = null,
        bool $set_full_url = false,
    ): LengthAwarePaginator {
        if ($data instanceof LengthAwarePaginator) {
            if ($set_full_url) {
                static::setFullURL($data);
            }

            return $data;
        }

        if ($data instanceof QueryBuilder || $data instanceof EloquentBuilder) {
            return static::paginateLengthAware($data, $page, 'page', $items_per_page, $set_full_url);
        }

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
     * Paginate a database-backed query with length-aware metadata.
     *
     * @param  EloquentBuilder|QueryBuilder $query          Eloquent or base query builder.
     * @param  int|null     $page           Page number (defaults to 1).
     * @param  string       $pageName       Pagination parameter name.
     * @param  int|null     $items_per_page Items per page (defaults to config value).
     * @param  bool         $set_full_url   When true, sets the paginator path to the current URL.
     * @param  callable|null $transformation Optional transformation callback applied via through().
     * @return LengthAwarePaginator                         Paginator ready for rendering or JSON serialization.
     */
    public static function paginateLengthAware(
        EloquentBuilder|QueryBuilder $query,
        ?int $page = null,
        string $pageName = 'page',
        ?int $items_per_page = null,
        bool $set_full_url = false,
        ?callable $transformation = null,
    ): LengthAwarePaginator {
        $paginationLength = $items_per_page ?: config('equidna.paginator.page_items');
        $paginator = $query->paginate(
            $paginationLength,
            ['*'],
            $pageName,
            $page ?: 1,
        );

        if (!is_null($transformation)) {
            $paginator->through($transformation);
        }

        if ($set_full_url) {
            static::setFullURL($paginator);
        }

        return $paginator;
    }

    /**
     * Paginate a database-backed query using cursor pagination.
     *
     * Cursor pagination is more efficient for large datasets where offset-based
     * pagination becomes expensive.
     *
     * @param  EloquentBuilder|QueryBuilder  $query           Eloquent or base query builder.
     * @param  int|null      $items_per_page  Items per page (defaults to config value).
     * @param  string        $cursorName      Cursor query string key.
     * @param  bool          $set_full_url    When true, sets the paginator path to the current URL.
     * @param  callable|null $transformation  Optional transformation callback applied via through().
     * @return CursorPaginator                                  Paginator ready for rendering or JSON serialization.
     */
    public static function paginateCursor(
        EloquentBuilder|QueryBuilder $query,
        ?int $items_per_page = null,
        string $cursorName = 'cursor',
        bool $set_full_url = false,
        ?callable $transformation = null,
    ): CursorPaginator {
        $paginationLength = $items_per_page ?: config('equidna.paginator.page_items');
        $paginator = $query->cursorPaginate(
            $paginationLength,
            ['*'],
            $cursorName,
        );

        if (!is_null($transformation)) {
            $paginator->through($transformation);
        }

        if ($set_full_url) {
            static::setFullURL($paginator);
        }

        return $paginator;
    }

    /**
     * Append cleaned request parameters to the paginator.
     *
     * @param  CursorPaginator|LengthAwarePaginator $paginator Paginator receiving filtered query parameters.
     * @param  Request              $request   Current HTTP request used for query data.
     * @return void
     */
    public static function appendCleanedRequest(CursorPaginator|LengthAwarePaginator $paginator, Request $request): void
    {
        $paginator->appends($request->except(static::EXCLUDE_FROM_REQUEST));
    }

    /**
     * Set the paginator path to the current URL.
     *
     * @param  CursorPaginator|LengthAwarePaginator $paginator Paginator whose base path should mirror the current URL.
     * @return void
     */
    public static function setFullURL(CursorPaginator|LengthAwarePaginator $paginator): void
    {
        $paginator->setPath(url()->current());
    }
}
