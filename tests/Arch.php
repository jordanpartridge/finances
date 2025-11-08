<?php

declare(strict_types=1);

arch('api controllers extend nothing')
    ->expect('App\Http\Controllers\Api')
    ->toExtendNothing();

arch('api controllers are invokable')
    ->expect('App\Http\Controllers\Api')
    ->toHaveMethod('__invoke');

arch('api controllers have no suffix')
    ->expect('App\Http\Controllers\Api')
    ->not->toHaveSuffix('Controller');

arch('models extend eloquent model')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\User'); // User extends Authenticatable

arch('strict types in controllers')
    ->expect('App\Http\Controllers')
    ->toUseStrictTypes();

arch('strict types in models')
    ->expect('App\Models')
    ->toUseStrictTypes()
    ->ignoring('App\Models\User');
