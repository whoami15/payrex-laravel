<?php

use LegionHQ\LaravelPayrex\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

function loadFixture(string $path): array
{
    return json_decode(file_get_contents(__DIR__.'/fixtures/'.$path), true, 512, JSON_THROW_ON_ERROR);
}
