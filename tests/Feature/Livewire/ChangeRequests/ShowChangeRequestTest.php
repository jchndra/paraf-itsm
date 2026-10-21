<?php

use App\Enums\RfcStatus;
use App\Livewire\ChangeRequests\ShowChangeRequest;
use App\Models\ChangeRequest;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

it('allows requester to view their own RFC', function () {
    $requester = User::factory()->create();
    $requester->assignRole('Pemohon');

    $rfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $requester->id,
        'title' => 'Pembaruan Server B',
    ]);

    $this->actingAs($requester);

    Livewire::test(ShowChangeRequest::class, ['rfc' => $rfc])
        ->assertOk()
        ->assertSee('Pembaruan Server B')
        ->assertSee($rfc->rfc_number);
});

it('blocks requester from viewing another users RFC with 403', function () {
    $requester1 = User::factory()->create();
    $requester1->assignRole('Pemohon');

    $requester2 = User::factory()->create(); // Attacker
    $requester2->assignRole('Pemohon');

    $rfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $requester1->id,
    ]);

    $this->actingAs($requester2);

    Livewire::test(ShowChangeRequest::class, ['rfc' => $rfc])
        ->assertForbidden(); // Memastikan respons HTTP 403
});

it('hides the edit button because the edit feature is not available yet', function () {
    // Perilaku lama: tombol "Edit / Revisi RFC" tampil untuk status draft/needs_revision,
    // tetapi mengarah ke halaman create. Fitur edit belum ada, jadi tombol disembunyikan.
    $requester = User::factory()->create();
    $requester->assignRole('Pemohon');

    // Test for Draft
    $draftRfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $requester->id,
        'status' => RfcStatus::DRAFT->value,
    ]);

    // Test for Approved
    $approvedRfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $requester->id,
        'status' => RfcStatus::APPROVED->value,
    ]);

    $this->actingAs($requester);

    Livewire::test(ShowChangeRequest::class, ['rfc' => $draftRfc])
        ->assertDontSee('Edit / Revisi RFC');

    Livewire::test(ShowChangeRequest::class, ['rfc' => $approvedRfc])
        ->assertDontSee('Edit / Revisi RFC');
});

it('allows change manager to bypass ownership check', function () {
    $requester = User::factory()->create();
    $requester->assignRole('Pemohon');

    $manager = User::factory()->create();
    // Policy view() memberi akses lintas pemilik melalui rfc.view_all, bukan rfc.assess_risk.
    $manager->assignRole('Change Manager');

    $rfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $requester->id,
    ]);

    $this->actingAs($manager);

    Livewire::test(ShowChangeRequest::class, ['rfc' => $rfc])
        ->assertOk(); // Manager tidak terblokir 403
});
