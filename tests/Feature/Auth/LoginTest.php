<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    RateLimiter::clear(Str::transliterate(Str::lower('user@paraf.local') . '|127.0.0.1'));
});

test('tamu dialihkan ke halaman login saat mengakses route terproteksi', function () {
    $this->get('/change-requests')
        ->assertRedirect('/login');
});

test('pengguna dapat berhasil login dengan kredensial valid', function () {
    $user = User::factory()->create([
        'email' => 'user@paraf.local',
        'password' => bcrypt('secret123'),
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->set('email', 'user@paraf.local')
        ->set('password', 'secret123')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect('/requester/dashboard');

    $this->assertAuthenticatedAs($user);
});

test('admin diarahkan ke daftar RFC setelah login', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'email' => 'admin@paraf.local',
        'password' => bcrypt('secret123'),
        'is_active' => true,
    ]);
    $admin->assignRole('Admin');

    Livewire::test(Login::class)
        ->set('email', 'admin@paraf.local')
        ->set('password', 'secret123')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect('/change-requests');

    $this->assertAuthenticatedAs($admin);
});

test('gagal login dengan kata sandi salah', function () {
    User::factory()->create([
        'email' => 'user@paraf.local',
        'password' => bcrypt('secret123'),
        'is_active' => true,
    ]);

    Livewire::test(Login::class)
        ->set('email', 'user@paraf.local')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

test('penolakan login untuk akun tidak aktif', function () {
    User::factory()->create([
        'email' => 'user@paraf.local',
        'password' => bcrypt('secret123'),
        'is_active' => false,
    ]);

    Livewire::test(Login::class)
        ->set('email', 'user@paraf.local')
        ->set('password', 'secret123')
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

test('rate limiting aktif setelah percobaan login berulang', function () {
    User::factory()->create([
        'email' => 'user@paraf.local',
        'password' => bcrypt('secret123'),
    ]);

    $component = Livewire::test(Login::class)
        ->set('email', 'user@paraf.local')
        ->set('password', 'wrong-password');

    for ($i = 0; $i < 5; $i++) {
        $component->call('login');
    }

    $component->call('login')
        ->assertHasErrors(['email']);
});

test('pengguna terotentikasi dapat melakukan logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});
