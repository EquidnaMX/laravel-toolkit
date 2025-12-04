<?php

declare(strict_types=1);

namespace Equidna\Toolkit\Tests\Traits;

use Equidna\Toolkit\Tests\TestCase;
use Equidna\Toolkit\Traits\Database\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HasCompositePrimaryKeyTest extends TestCase
{
    public function test_set_keys_for_save_query_uses_original_values(): void
    {
        $model = new class () extends Model {
            use HasCompositePrimaryKey;

            protected $table = 'example';

            public $timestamps = false;

            protected array $primaryKey = ['first', 'second'];

            public function getKeyName(): array
            {
                return $this->primaryKey;
            }
        };

        $model->original = ['first' => 10, 'second' => 20];

        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();

        $builder->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(
                ['first', '=', 10],
                ['second', '=', 20],
            )
            ->willReturnSelf();

        $result = $model->setKeysForSaveQuery($builder);

        $this->assertSame($builder, $result);
    }

    public function test_set_keys_for_save_query_falls_back_to_attributes(): void
    {
        $model = new class () extends Model {
            use HasCompositePrimaryKey;

            protected $table = 'example';

            public $timestamps = false;

            protected array $primaryKey = ['first', 'second'];

            public function getKeyName(): array
            {
                return $this->primaryKey;
            }
        };

        $model->first = 5;
        $model->second = 6;

        $builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();

        $builder->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(
                ['first', '=', 5],
                ['second', '=', 6],
            )
            ->willReturnSelf();

        $result = $model->setKeysForSaveQuery($builder);

        $this->assertSame($builder, $result);
    }
}
