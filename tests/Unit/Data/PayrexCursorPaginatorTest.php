<?php

declare(strict_types=1);

use Illuminate\Pagination\Cursor;
use LegionHQ\LaravelPayrex\Data\PayrexCursorPaginator;
use LegionHQ\LaravelPayrex\Data\PayrexObject;

it('uses the API has_more flag instead of item count', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
        PayrexObject::from(['id' => 'obj_2']),
    ];

    $paginator = new PayrexCursorPaginator($items, 2, apiHasMore: true);

    expect($paginator->hasMorePages())->toBeTrue()
        ->and($paginator->items())->toHaveCount(2);
});

it('reports no more pages when API says has_more is false', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
    ];

    $paginator = new PayrexCursorPaginator($items, 10, apiHasMore: false);

    expect($paginator->hasMorePages())->toBeFalse();
});

it('generates next cursor from the last item id', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
        PayrexObject::from(['id' => 'obj_2']),
        PayrexObject::from(['id' => 'obj_3']),
    ];

    $paginator = new PayrexCursorPaginator($items, 3, apiHasMore: true);

    $nextCursor = $paginator->nextCursor();

    expect($nextCursor)->not->toBeNull()
        ->and($nextCursor->parameter('id'))->toBe('obj_3')
        ->and($nextCursor->pointsToNextItems())->toBeTrue();
});

it('returns null next cursor when no more pages', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
    ];

    $paginator = new PayrexCursorPaginator($items, 10, apiHasMore: false);

    expect($paginator->nextCursor())->toBeNull();
});

it('generates previous cursor from the first item id when navigating forward', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_4']),
        PayrexObject::from(['id' => 'obj_5']),
    ];

    $cursor = new Cursor(['id' => 'obj_3'], pointsToNextItems: true);
    $paginator = new PayrexCursorPaginator($items, 2, apiHasMore: true, cursor: $cursor);

    $prevCursor = $paginator->previousCursor();

    expect($prevCursor)->not->toBeNull()
        ->and($prevCursor->parameter('id'))->toBe('obj_4')
        ->and($prevCursor->pointsToPreviousItems())->toBeTrue();
});

it('returns null previous cursor on the first page', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
    ];

    $paginator = new PayrexCursorPaginator($items, 10, apiHasMore: false);

    expect($paginator->previousCursor())->toBeNull();
});

it('serializes to the expected array structure', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
        PayrexObject::from(['id' => 'obj_2']),
    ];

    $paginator = new PayrexCursorPaginator($items, 2, apiHasMore: true);
    $array = $paginator->toArray();

    expect($array)->toHaveKeys(['data', 'path', 'per_page', 'next_cursor', 'next_page_url', 'prev_cursor', 'prev_page_url'])
        ->and($array['per_page'])->toBe(2)
        ->and($array['next_cursor'])->not->toBeNull()
        ->and($array['prev_cursor'])->toBeNull();
});

it('handles empty items', function () {
    $paginator = new PayrexCursorPaginator([], 10, apiHasMore: false);

    expect($paginator->items())->toHaveCount(0)
        ->and($paginator->hasMorePages())->toBeFalse()
        ->and($paginator->nextCursor())->toBeNull();
});

it('does not slice items like the base CursorPaginator', function () {
    // Base CursorPaginator fetches perPage+1 items and slices. Ours should keep all items.
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
        PayrexObject::from(['id' => 'obj_2']),
        PayrexObject::from(['id' => 'obj_3']),
    ];

    $paginator = new PayrexCursorPaginator($items, 3, apiHasMore: true);

    expect($paginator->items())->toHaveCount(3);
});

it('appends query parameters to pagination URLs', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
    ];

    $paginator = new PayrexCursorPaginator($items, 1, apiHasMore: true, options: ['path' => '/customers']);
    $paginator->appends(['limit' => 5, 'name' => 'Juan']);

    $nextUrl = $paginator->nextPageUrl();

    expect($nextUrl)->toContain('/customers')
        ->and($nextUrl)->toContain('limit=5')
        ->and($nextUrl)->toContain('name=Juan')
        ->and($nextUrl)->toContain('cursor=');
});

it('defaults path to root when no path option provided', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
    ];

    $paginator = new PayrexCursorPaginator($items, 1, apiHasMore: true);

    expect($paginator->path())->toBe('/');
});

it('accepts a custom path via options', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_1']),
    ];

    $paginator = new PayrexCursorPaginator($items, 1, apiHasMore: true, options: ['path' => '/api/customers']);

    expect($paginator->path())->toBe('/api/customers');
});

it('reverses items when cursor points to previous items', function () {
    $items = [
        PayrexObject::from(['id' => 'obj_3']),
        PayrexObject::from(['id' => 'obj_2']),
        PayrexObject::from(['id' => 'obj_1']),
    ];

    $cursor = new Cursor(['id' => 'obj_4'], pointsToNextItems: false);
    $paginator = new PayrexCursorPaginator($items, 3, apiHasMore: false, cursor: $cursor);

    $ids = collect($paginator->items())->map(fn ($item) => $item->id)->all();

    expect($ids)->toBe(['obj_1', 'obj_2', 'obj_3']);
});
