<?php

use App\Livewire\Profile\Appearance;
use App\Models\InstanceSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Once;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    InstanceSettings::query()->forceCreate(['id' => 0]);
    Once::flush();
});

test('the base layout always renders the dark theme', function () {
    User::factory()->create();

    $this->get('/login')
        ->assertSuccessful()
        ->assertSee('<html class="dark" data-theme="dark"', false)
        ->assertSee('<meta name="color-scheme" content="dark" />', false)
        ->assertSee('<meta name="theme-color" content="#0a0a0a"', false)
        ->assertDontSee('hexToOklch', false)
        ->assertDontSee('prefers-color-scheme', false);
});

test('the login page uses the monochrome auth shell', function () {
    User::factory()->create();

    $this->get('/login')
        ->assertSuccessful()
        ->assertSee('class="auth-panel"', false)
        ->assertSee('/coolify-logo-monochrome.svg', false);
});

test('the appearance page only offers layout preferences', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Appearance::class)
        ->assertSuccessful()
        ->assertSee('Page width')
        ->assertSee('Full width')
        ->assertSee('Centered')
        ->assertDontSee('Color theme')
        ->assertDontSee('Custom')
        ->assertDontSee('System');
});

test('the profile menu theme controls no longer switch color schemes', function () {
    $html = Blade::render('<x-theme-controls variant="menu" />');

    expect($html)
        ->toContain("setWidth('full')")
        ->toContain("setWidth('centered')")
        ->not->toContain('setTheme(')
        ->not->toContain('chooseCustom(');
});

test('status badges expose their semantic state and readable label', function (string $type) {
    $html = Blade::render('<x-status-badge :type="$type" label="Proxy" status="Running" />', ['type' => $type]);

    expect($html)
        ->toContain('data-status-type="'.$type.'"')
        ->toContain('Proxy Running');
})->with([
    'success',
    'error',
    'warning',
    'neutral',
]);

test('running resources expose health warnings without a healthy state badge', function (string $status, string $label) {
    $html = Blade::render('<x-status.running :status="$status" />', ['status' => $status]);

    expect($html)
        ->toContain('data-status-type="warning"')
        ->toContain($label)
        ->not->toContain('data-status-type="success"');
})->with([
    ['running:unhealthy', 'Unhealthy'],
    ['running:unknown', 'No healthcheck'],
]);

test('healthy running resources expose a healthy state badge', function () {
    $html = Blade::render('<x-status.running status="running:healthy" />');

    expect($html)
        ->toContain('data-status-type="success"')
        ->toContain('Running (healthy)')
        ->not->toContain('data-status-type="warning"');
});
