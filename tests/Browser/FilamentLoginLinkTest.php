<?php

namespace Tests\Browser;

use App\Models\User;

it('displays the filament login page', function () {
    $page = visit('/admin/login');

    $page->assertSee('Sign in to your account')
        ->assertSee('Email')
        ->assertSee('Password');
});

it('shows login link component on the login form', function () {
    $page = visit('/admin/login');

    $page->assertPresent('[data-testid="login-link"]');
});

it('allows user to login with valid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $page = visit('/admin/login');

    $page->type('input[name="email"]', 'test@example.com')
        ->type('input[name="password"]', 'password123')
        ->press('Sign in')
        ->assertPathIs('/admin');
});

it('prevents login with invalid credentials', function () {
    $page = visit('/admin/login');

    $page->type('input[name="email"]', 'invalid@example.com')
        ->type('input[name="password"]', 'wrongpassword')
        ->press('Sign in')
        ->assertSee('These credentials do not match');
});

it('redirects authenticated users away from login page', function () {
    $user = User::factory()->create();

    $page = visit('/admin/login')->actingAs($user);

    $page->assertPathIs('/admin');
});

it('allows authenticated user to access admin dashboard', function () {
    $user = User::factory()->create();

    $page = visit('/admin')->actingAs($user);

    $page->assertPathIs('/admin');
});

it('validates required fields on login form', function () {
    $page = visit('/admin/login');

    $page->press('Sign in')
        ->assertSee('required');
});

it('displays email input field', function () {
    $page = visit('/admin/login');

    $page->assertPresent('input[name="email"][type="email"]');
});

it('displays password field as hidden input', function () {
    $page = visit('/admin/login');

    $page->assertPresent('input[name="password"][type="password"]');
});

it('can use login link to authenticate without password', function () {
    $user = User::factory()->create([
        'email' => 'linktest@example.com',
    ]);

    $page = visit('/admin/login');

    $page->assertSee('Email')
        ->type('input[name="email"]', 'linktest@example.com')
        ->click('[data-testid="login-link"]')
        ->assertPathIs('/admin');
});
