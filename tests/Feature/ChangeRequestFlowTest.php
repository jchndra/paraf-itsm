<?php

use App\Models\Application;
use App\Models\ChangeRequest;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('pemohon hanya dapat melihat rfc miliknya sendiri di daftar rfc', function () {
    $pemohonA = User::factory()->create();
    $pemohonA->assignRole('Pemohon');

    $pemohonB = User::factory()->create();
    $pemohonB->assignRole('Pemohon');

    $app = Application::factory()->create();

    $rfcA = ChangeRequest::factory()->create([
        'submitted_by_id' => $pemohonA->id,
        'application_id' => $app->id,
    ]);

    $rfcB = ChangeRequest::factory()->create([
        'submitted_by_id' => $pemohonB->id,
        'application_id' => $app->id,
    ]);

    $this->actingAs($pemohonA);

    Livewire::test(\App\Livewire\ChangeRequests\ListChangeRequests::class)
        ->assertSee($rfcA->rfc_number)
        ->assertDontSee($rfcB->rfc_number);
});

test('nomor rfc otomatis dibuat berurutan dengan format rfc-tahun-nnnn', function () {
    $number1 = \App\Services\RfcNumberGenerator::generate();
    expect($number1)->toBe('RFC-' . date('Y') . '-0001');

    ChangeRequest::factory()->create(['rfc_number' => $number1]);

    $number2 = \App\Services\RfcNumberGenerator::generate();
    expect($number2)->toBe('RFC-' . date('Y') . '-0002');
});
