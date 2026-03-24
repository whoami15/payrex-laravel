<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Data\PayrexCursorPaginator;
use LegionHQ\LaravelPayrex\Data\PayrexObject;

it('maps items to typed DTO instances', function () {
    $collection = new PayrexCollection(
        [
            'resource' => 'list',
            'has_more' => false,
            'data' => [
                ['id' => 'obj_1', 'resource' => 'test'],
                ['id' => 'obj_2', 'resource' => 'test'],
            ],
        ],
        PayrexObject::class,
    );

    expect($collection->resource)->toBe('list')
        ->and($collection->hasMore)->toBeFalse()
        ->and($collection->data)->toHaveCount(2)
        ->and($collection->data[0])->toBeInstanceOf(PayrexObject::class)
        ->and($collection->data[0]->id)->toBe('obj_1')
        ->and($collection->data[1]->id)->toBe('obj_2');
});

it('is countable', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => [['id' => 'obj_1', 'resource' => 'test'], ['id' => 'obj_2', 'resource' => 'test']]],
        PayrexObject::class,
    );

    expect(count($collection))->toBe(2);
});

it('is iterable', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => [['id' => 'obj_1', 'resource' => 'test'], ['id' => 'obj_2', 'resource' => 'test']]],
        PayrexObject::class,
    );

    $ids = [];
    foreach ($collection as $item) {
        $ids[] = $item->id;
    }

    expect($ids)->toBe(['obj_1', 'obj_2']);
});

it('supports ArrayAccess for backwards compatibility', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => [['id' => 'obj_1', 'resource' => 'test']]],
        PayrexObject::class,
    );

    expect(isset($collection['resource']))->toBeTrue()
        ->and(isset($collection['nonexistent']))->toBeFalse()
        ->and($collection['resource'])->toBe('list')
        ->and($collection['has_more'])->toBeFalse()
        ->and($collection['data'])->toHaveCount(1)
        ->and($collection['data'][0])->toBeInstanceOf(PayrexObject::class)
        ->and($collection['data'][0]->id)->toBe('obj_1');
});

it('throws LogicException on ArrayAccess mutation', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => []],
        PayrexObject::class,
    );

    $collection['resource'] = 'changed';
})->throws(LogicException::class, 'PayrexCollection is immutable.');

it('throws LogicException on ArrayAccess unset', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => []],
        PayrexObject::class,
    );

    unset($collection['resource']);
})->throws(LogicException::class, 'PayrexCollection is immutable.');

it('handles empty collection', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => []],
        PayrexObject::class,
    );

    expect(count($collection))->toBe(0)
        ->and($collection->data)->toBe([]);
});

it('serializes to JSON', function () {
    $attributes = ['resource' => 'list', 'has_more' => false, 'data' => [['id' => 'obj_1', 'resource' => 'test']]];
    $collection = new PayrexCollection($attributes, PayrexObject::class);

    expect(json_encode($collection))->toBe(json_encode($attributes));
});

it('toCursorPaginator returns a PayrexCursorPaginator', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => true, 'data' => [
            ['id' => 'obj_1', 'resource' => 'test'],
            ['id' => 'obj_2', 'resource' => 'test'],
        ]],
        PayrexObject::class,
    );

    $paginator = $collection->toCursorPaginator(perPage: 10);

    expect($paginator)->toBeInstanceOf(PayrexCursorPaginator::class)
        ->and($paginator->items())->toHaveCount(2)
        ->and($paginator->perPage())->toBe(10)
        ->and($paginator->hasMorePages())->toBeTrue();
});

it('toCursorPaginator defaults perPage to current page size', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => [
            ['id' => 'obj_1', 'resource' => 'test'],
            ['id' => 'obj_2', 'resource' => 'test'],
            ['id' => 'obj_3', 'resource' => 'test'],
        ]],
        PayrexObject::class,
    );

    $paginator = $collection->toCursorPaginator();

    expect($paginator->perPage())->toBe(3)
        ->and($paginator->hasMorePages())->toBeFalse();
});

it('toCursorPaginator forwards hasMore false correctly', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => [
            ['id' => 'obj_1', 'resource' => 'test'],
        ]],
        PayrexObject::class,
    );

    $paginator = $collection->toCursorPaginator();

    expect($paginator->hasMorePages())->toBeFalse()
        ->and($paginator->nextCursor())->toBeNull();
});

it('toCursorPaginator works with empty collection', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => []],
        PayrexObject::class,
    );

    $paginator = $collection->toCursorPaginator(perPage: 10);

    expect($paginator->items())->toHaveCount(0)
        ->and($paginator->hasMorePages())->toBeFalse();
});

it('toCursorPaginator defaults perPage to 1 on empty collection', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => []],
        PayrexObject::class,
    );

    $paginator = $collection->toCursorPaginator();

    expect($paginator->perPage())->toBe(1)
        ->and($paginator->items())->toHaveCount(0);
});
