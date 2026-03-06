<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\PayrexCollection;
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

it('auto paginates across multiple pages with correct cursor', function () {
    $receivedCursor = null;

    $page1 = new PayrexCollection(
        [
            'resource' => 'list',
            'has_more' => true,
            'data' => [
                ['id' => 'obj_1', 'resource' => 'test'],
                ['id' => 'obj_2', 'resource' => 'test'],
            ],
        ],
        PayrexObject::class,
        function (array $pagination) use (&$receivedCursor) {
            $receivedCursor = $pagination;

            return new PayrexCollection(
                [
                    'resource' => 'list',
                    'has_more' => false,
                    'data' => [
                        ['id' => 'obj_3', 'resource' => 'test'],
                    ],
                ],
                PayrexObject::class,
            );
        },
    );

    $allIds = $page1->autoPaginate()->map(fn ($item) => $item->id)->all();

    expect($allIds)->toBe(['obj_1', 'obj_2', 'obj_3'])
        ->and($receivedCursor)->toBe(['after' => 'obj_2']);
});

it('auto paginates across three pages with correct cursors', function () {
    $receivedCursors = [];

    $page1 = new PayrexCollection(
        [
            'resource' => 'list',
            'has_more' => true,
            'data' => [
                ['id' => 'obj_1', 'resource' => 'test'],
                ['id' => 'obj_2', 'resource' => 'test'],
            ],
        ],
        PayrexObject::class,
        function (array $pagination) use (&$receivedCursors) {
            $receivedCursors[] = $pagination;

            if (count($receivedCursors) === 1) {
                return new PayrexCollection(
                    [
                        'resource' => 'list',
                        'has_more' => true,
                        'data' => [
                            ['id' => 'obj_3', 'resource' => 'test'],
                            ['id' => 'obj_4', 'resource' => 'test'],
                        ],
                    ],
                    PayrexObject::class,
                    function (array $pagination) use (&$receivedCursors) {
                        $receivedCursors[] = $pagination;

                        return new PayrexCollection(
                            [
                                'resource' => 'list',
                                'has_more' => false,
                                'data' => [
                                    ['id' => 'obj_5', 'resource' => 'test'],
                                ],
                            ],
                            PayrexObject::class,
                        );
                    },
                );
            }

            return new PayrexCollection(
                ['resource' => 'list', 'has_more' => false, 'data' => []],
                PayrexObject::class,
            );
        },
    );

    $allIds = $page1->autoPaginate()->map(fn ($item) => $item->id)->all();

    expect($allIds)->toBe(['obj_1', 'obj_2', 'obj_3', 'obj_4', 'obj_5'])
        ->and($receivedCursors)->toBe([
            ['after' => 'obj_2'],
            ['after' => 'obj_4'],
        ]);
});

it('auto paginate works for single page', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => [['id' => 'obj_1', 'resource' => 'test']]],
        PayrexObject::class,
    );

    $ids = $collection->autoPaginate()->map(fn ($item) => $item->id)->all();

    expect($ids)->toBe(['obj_1']);
});

it('handles empty collection', function () {
    $collection = new PayrexCollection(
        ['resource' => 'list', 'has_more' => false, 'data' => []],
        PayrexObject::class,
    );

    expect(count($collection))->toBe(0)
        ->and($collection->autoPaginate()->all())->toBe([]);
});

it('serializes to JSON', function () {
    $attributes = ['resource' => 'list', 'has_more' => false, 'data' => [['id' => 'obj_1', 'resource' => 'test']]];
    $collection = new PayrexCollection($attributes, PayrexObject::class);

    expect(json_encode($collection))->toBe(json_encode($attributes));
});

it('auto paginate respects maxPages safety valve', function () {
    $pagesFetched = 0;

    $makePage = function (string $id, bool $hasMore) use (&$makePage, &$pagesFetched): PayrexCollection {
        return new PayrexCollection(
            [
                'resource' => 'list',
                'has_more' => $hasMore,
                'data' => [
                    ['id' => $id, 'resource' => 'test'],
                ],
            ],
            PayrexObject::class,
            function (array $pagination) use (&$makePage, &$pagesFetched): PayrexCollection {
                $pagesFetched++;
                $nextId = 'obj_'.($pagesFetched + 1);

                return $makePage($nextId, true);
            },
        );
    };

    $page1 = $makePage('obj_1', true);

    $allIds = $page1->autoPaginate(maxPages: 3)->map(fn ($item) => $item->id)->all();

    expect($allIds)->toBe(['obj_1', 'obj_2', 'obj_3'])
        ->and($pagesFetched)->toBe(2);
});

it('gracefully stops auto pagination when paginator is null', function () {
    $collection = new PayrexCollection(
        [
            'resource' => 'list',
            'has_more' => true,
            'data' => [
                ['id' => 'obj_1', 'resource' => 'test'],
            ],
        ],
        PayrexObject::class,
    );

    $ids = $collection->autoPaginate()->map(fn ($item) => $item->id)->all();

    expect($ids)->toBe(['obj_1']);
});
