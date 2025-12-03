<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Helpers;

use Equidna\Toolkit\Helpers\PaginatorHelper;
use Equidna\Toolkit\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginatorHelperTest extends TestCase
{
    public function test_build_paginator_from_array_sets_full_url(): void
    {
        $items = range(1, 30);

        $paginator = PaginatorHelper::buildPaginator($items, page: 2, items_per_page: 5, set_full_url: true);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertSame(5, $paginator->perPage());
        $this->assertSame('http://localhost/current', $paginator->path());
        $this->assertSame(range(6, 10), $paginator->items());
    }

    public function test_length_aware_query_pagination_passes_arguments(): void
    {
        $builder = new class {
            public array $calls = [];

            public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
            {
                $this->calls[] = compact('perPage', 'columns', 'pageName', 'page');

                return new LengthAwarePaginator([], 0, (int) $perPage, (int) $page);
            }
        };

        $paginator = PaginatorHelper::paginateLengthAware($builder, page: 3, pageName: 'page', items_per_page: 4, set_full_url: true);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertSame([
            ['perPage' => 4, 'columns' => ['*'], 'pageName' => 'page', 'page' => 3],
        ], $builder->calls);
        $this->assertSame('http://localhost/current', $paginator->path());
    }

    public function test_cursor_pagination_tracks_url_and_transforms(): void
    {
        $builder = new class {
            public array $calls = [];

            public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor')
            {
                $this->calls[] = compact('perPage', 'columns', 'cursorName');

                return new CursorPaginator(collect([['id' => 1]]), $perPage, $cursorName);
            }
        };

        $paginator = PaginatorHelper::paginateCursor($builder, items_per_page: 2, cursorName: 'pointer', set_full_url: true, transformation: fn(array $row) => $row['id']);

        $this->assertInstanceOf(CursorPaginator::class, $paginator);
        $this->assertSame([
            ['perPage' => 2, 'columns' => ['*'], 'cursorName' => 'pointer'],
        ], $builder->calls);
        $this->assertSame('http://localhost/current', $paginator->path());
        $this->assertSame([1], $paginator->items());
    }

    public function test_append_cleaned_request_removes_excluded_fields(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15);
        $request = Request::create('/items', 'GET', [
            '_token' => 'abc',
            'page' => 2,
            'search' => 'laravel',
        ]);

        PaginatorHelper::appendCleanedRequest($paginator, $request);

        $this->assertSame(['search' => 'laravel'], $paginator->getOptions()['query'] ?? []);
    }
}
