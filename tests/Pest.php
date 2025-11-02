<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class,
)->in('Feature');

uses(
    Tests\TestCase::class,
)->in('Unit');

uses(
    Tests\TestCase::class,
    RefreshDatabase::class,
)->in('Browser');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
