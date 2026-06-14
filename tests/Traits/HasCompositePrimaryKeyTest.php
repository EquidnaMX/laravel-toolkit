<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Traits;

use Equidna\Toolkit\Tests\TestCase;
use Equidna\Toolkit\Traits\Database\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Builder;

class HasCompositePrimaryKeyTest extends TestCase
{
    public function test_set_keys_for_save_query_uses_original_values(): void
    {
        $model = new class () {
            use HasCompositePrimaryKey;

            public array $original = [];

            public int $first;

            public int $second;

            public function getKeyName(): array
            {
                return ['first', 'second'];
            }

            public function getAttribute($key)
            {
                return $this->{$key} ?? null;
            }

            public function callSetKeysForSaveQuery($query)
            {
                return $this->setKeysForSaveQuery($query);
            }
        };

        $model->original = ['first' => 10, 'second' => 20];

        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();

        $whereCalls = [];

        $builder->expects($this->exactly(2))
            ->method('where')
            ->willReturnCallback(function (...$arguments) use (&$whereCalls, $builder) {
                $whereCalls[] = $arguments;

                return $builder;
            });

        $result = $model->callSetKeysForSaveQuery($builder);

        $this->assertSame($builder, $result);
        $this->assertSame(
            [
                ['first', '=', 10, 'and'],
                ['second', '=', 20, 'and'],
            ],
            $whereCalls,
        );
    }

    public function test_set_keys_for_save_query_falls_back_to_attributes(): void
    {
        $model = new class () {
            use HasCompositePrimaryKey;

            public array $original = [];

            public int $first;

            public int $second;

            public function getKeyName(): array
            {
                return ['first', 'second'];
            }

            public function getAttribute($key)
            {
                return $this->{$key} ?? null;
            }

            public function callSetKeysForSaveQuery($query)
            {
                return $this->setKeysForSaveQuery($query);
            }
        };

        $model->first = 5;
        $model->second = 6;

        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();

        $whereCalls = [];

        $builder->expects($this->exactly(2))
            ->method('where')
            ->willReturnCallback(function (...$arguments) use (&$whereCalls, $builder) {
                $whereCalls[] = $arguments;

                return $builder;
            });

        $result = $model->callSetKeysForSaveQuery($builder);

        $this->assertSame($builder, $result);
        $this->assertSame(
            [
                ['first', '=', 5, 'and'],
                ['second', '=', 6, 'and'],
            ],
            $whereCalls,
        );
    }
}
