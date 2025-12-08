<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Helpers;

use Equidna\Toolkit\Exceptions\ConfigurationException;
use Equidna\Toolkit\Helpers\PaginatorHelper;
use Equidna\Toolkit\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
        $this->assertSame(range(6, 10), array_values($paginator->items()));
    }

    public function test_length_aware_query_pagination_passes_arguments(): void
    {
        $builder = $this->getMockBuilder(EloquentBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['paginate'])
            ->getMock();

        $builder->expects($this->once())
            ->method('paginate')
            ->with(4, ['*'], 'page', 3)
            ->willReturn(new LengthAwarePaginator([], 0, 4, 3));

        $paginator = PaginatorHelper::paginateLengthAware($builder, page: 3, pageName: 'page', items_per_page: 4, set_full_url: true);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertSame('http://localhost/current', $paginator->path());
    }

    public function test_cursor_pagination_tracks_url_and_transforms(): void
    {
        $builder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['cursorPaginate'])
            ->getMock();

        $cursor = \Illuminate\Pagination\Cursor::fromEncoded('cursor');
        $builder->expects($this->once())
            ->method('cursorPaginate')
            ->with(2, ['*'], 'pointer')
            ->willReturn(new CursorPaginator(collect([['id' => 1]]), 2, $cursor, [], 'pointer'));

        $paginator = PaginatorHelper::paginateCursor($builder, items_per_page: 2, cursorName: 'pointer', set_full_url: true, transformation: fn(array $row) => $row['id']);

        $this->assertInstanceOf(CursorPaginator::class, $paginator);
        $this->assertSame('http://localhost/current', $paginator->path());
        $this->assertSame([1], $paginator->items());
    }

    public function test_append_cleaned_request_removes_excluded_fields(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15);
        $request = Request::create('/items?search=laravel&_token=abc&page=2', 'GET');

        PaginatorHelper::appendCleanedRequest($paginator, $request);

        $query = $paginator->getOptions()['query'] ?? [];

        $this->assertArrayNotHasKey('_token', $query);
        $this->assertArrayNotHasKey('page', $query);
    }

    public function test_invalid_per_page_configuration_throws_exception(): void
    {
        $this->config->set('equidna.paginator.page_items', 0);

        $this->expectException(ConfigurationException::class);

        PaginatorHelper::buildPaginator(range(1, 5));
    }
}
