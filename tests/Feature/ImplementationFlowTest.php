<?php

use App\Enums\RfcStatus;
use App\Livewire\ChangeRequests\CloseRfc;
use App\Livewire\ChangeRequests\ImplementChange;
use App\Models\ChangeRequest;
use App\Models\Evidence;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    Storage::fake('public');
});

function changeManager(): User
{
    $user = User::factory()->create();
    $user->assignRole('Change Manager');

    return $user;
}

test('aksi implementasi menjalankan approved ke in_progress lalu implemented dalam satu transaksi', function () {
    $cm = changeManager();

    $rfc = ChangeRequest::factory()->create([
        'status' => RfcStatus::APPROVED->value,
    ]);

    $this->actingAs($cm);

    Livewire::test(ImplementChange::class, ['rfc' => $rfc])
        ->set('execution_result', 'success')
        ->set('actual_start_time', now()->subHour()->format('Y-m-d\TH:i'))
        ->set('actual_end_time', now()->format('Y-m-d\TH:i'))
        ->set('implementation_notes', 'Perubahan diterapkan pada server produksi.')
        ->set('testing_notes', 'Pengujian fungsional dan regresi berhasil.')
        ->set('evidence_description', 'Bukti pengujian implementasi')
        ->set('evidence_file', UploadedFile::fake()->create('evidence.pdf', 100, 'application/pdf'))
        ->call('saveImplementation')
        ->assertHasNoErrors();

    expect($rfc->fresh()->status)->toBe(RfcStatus::IMPLEMENTED->value);
    expect(Evidence::where('change_request_id', $rfc->id)->count())->toBe(1);
});

test('aksi implementasi yang gagal mewajibkan catatan rollback dan berakhir failed', function () {
    $cm = changeManager();

    $rfc = ChangeRequest::factory()->create([
        'status' => RfcStatus::APPROVED->value,
    ]);

    $this->actingAs($cm);

    Livewire::test(ImplementChange::class, ['rfc' => $rfc])
        ->set('execution_result', 'failed')
        ->set('actual_start_time', now()->subHour()->format('Y-m-d\TH:i'))
        ->set('actual_end_time', now()->format('Y-m-d\TH:i'))
        ->set('implementation_notes', 'Perubahan diterapkan pada server produksi.')
        ->set('testing_notes', 'Pengujian menemukan kegagalan integrasi.')
        ->set('rollback_notes', 'Perubahan di-rollback ke versi sebelumnya.')
        ->set('evidence_description', 'Bukti kegagalan implementasi')
        ->set('evidence_file', UploadedFile::fake()->create('evidence.pdf', 100, 'application/pdf'))
        ->call('saveImplementation')
        ->assertHasNoErrors();

    expect($rfc->fresh()->status)->toBe(RfcStatus::FAILED->value);
});

test('change manager dapat menutup rfc berstatus failed', function () {
    $cm = changeManager();

    $rfc = ChangeRequest::factory()->create([
        'status' => RfcStatus::FAILED->value,
    ]);

    $this->actingAs($cm);

    Livewire::test(CloseRfc::class, ['rfc' => $rfc])
        ->set('closure_notes', 'Tiket ditutup setelah rollback diverifikasi.')
        ->call('closeTicket')
        ->assertHasNoErrors();

    expect($rfc->fresh()->status)->toBe(RfcStatus::CLOSED->value);
});
