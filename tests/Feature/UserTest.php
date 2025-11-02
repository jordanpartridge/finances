<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('can be created', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class);
});

it('can be updated', function () {
    $user = User::factory()->create();

    $user->update(['name' => 'Updated User']);

    expect($user->name)->toBe('Updated User');
});

it('has fillable attributes', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($user->name)->toBe('John Doe');
    expect($user->email)->toBe('john@example.com');
});

it('hides password from serialization', function () {
    $user = User::factory()->create();

    $array = $user->toArray();

    expect($array)->not->toHaveKey('password');
});

it('hides remember token from serialization', function () {
    $user = User::factory()->create();

    $array = $user->toArray();

    expect($array)->not->toHaveKey('remember_token');
});

it('casts email verified at to datetime', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    expect($user->email_verified_at)->toBeInstanceOf(\Carbon\CarbonInterface::class);
});

it('hashes password when set', function () {
    $user = User::factory()->create([
        'password' => 'secret123',
    ]);

    expect(Hash::check('secret123', $user->password))->toBeTrue();
});

it('can be deleted', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    $user->delete();

    expect(User::find($userId))->toBeNull();
});

it('can retrieve by email', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $retrieved = User::where('email', 'test@example.com')->first();

    expect($retrieved->id)->toBe($user->id);
});
