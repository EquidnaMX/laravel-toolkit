<?php

namespace Equidna\Toolkit\Services\Pagination;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DefaultPaginationStrategy extends AbstractPaginationStrategy
{
    public function buildPaginator(
        array|Collection|LengthAwarePaginator|EloquentBuilder|QueryBuilder $data,
        ?int $page = null,
        ?int $itemsPerPage = null,
        bool $setFullUrl = false,
    ): LengthAwarePaginator {
        if ($data instanceof LengthAwarePaginator) {
            if ($setFullUrl) {
                $this->setFullURL($data);
            }

            return $data;
        }

        if ($data instanceof QueryBuilder || $data instanceof EloquentBuilder) {
            return $this->paginateLengthAware($data, $page, 'page', $itemsPerPage, $setFullUrl);
        }

        $collection = is_array($data) ? collect($data) : $data;
        $paginationLength = $this->resolveItemsPerPage($itemsPerPage);
        $currentPage = $page ?: 1;

        $paginator = new LengthAwarePaginator(
            $collection->forPage(
                (int) $currentPage,
                $paginationLength,
            ),
            $collection->count(),
            $paginationLength,
            (int) $currentPage,
        );

        if ($setFullUrl) {
            $this->setFullURL($paginator);
        }

        return $paginator;
    }

    public function paginateLengthAware(
        EloquentBuilder|QueryBuilder $query,
        ?int $page = null,
        string $pageName = 'page',
        ?int $itemsPerPage = null,
        bool $setFullUrl = false,
        ?callable $transformation = null,
    ): LengthAwarePaginator {
        $paginationLength = $this->resolveItemsPerPage($itemsPerPage);
        $paginator = $query->paginate(
            $paginationLength,
            ['*'],
            $pageName,
            $page ?: 1,
        );

        if (!is_null($transformation)) {
            $paginator->through($transformation);
        }

        if ($setFullUrl) {
            $this->setFullURL($paginator);
        }

        return $paginator;
    }

    public function paginateCursor(
        EloquentBuilder|QueryBuilder $query,
        ?int $itemsPerPage = null,
        string $cursorName = 'cursor',
        bool $setFullUrl = false,
        ?callable $transformation = null,
    ): CursorPaginator {
        $paginationLength = $this->resolveItemsPerPage($itemsPerPage);
        $paginator = $query->cursorPaginate(
            $paginationLength,
            ['*'],
            $cursorName,
        );

        if (!is_null($transformation)) {
            $paginator->through($transformation);
        }

        if ($setFullUrl) {
            $this->setFullURL($paginator);
        }

        return $paginator;
    }
}

