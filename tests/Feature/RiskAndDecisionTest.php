<?php

use App\Enums\RfcStatus;
use App\Models\Authority;
use App\Models\ChangeRequest;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('change manager dapat mengisi analisis risiko dan mengubah status menjadi assessed', function () {
    $cm = User::factory()->create();
    $cm->assignRole('Change Manager');

    $rfc = ChangeRequest::factory()->create(['status' => RfcStatus::Complete->value]);

    $this->actingAs($cm);

    Livewire::test(\App\Livewire\ChangeRequests\AssessRisk::class, ['rfc' => $rfc])
        ->set('systemCriticalityScore', 4)
        ->set('impactScopeScore', 3)
        ->set('rollbackReadinessScore', 2)
        ->set('riskLevel', 'medium')
        ->set('mitigationPlan', 'Rencana mitigasi penggantian server cadangan.')
        ->set('rollbackPlan', 'Rencana rollback mengembalikan snapshot VM.')
        ->call('saveRiskAssessment')
        ->assertHasNoErrors();

    expect($rfc->fresh()->status)->toBe(RfcStatus::Assessed->value);
    expect($rfc->fresh()->riskAssessment)->not->toBeNull();
});

test('keputusan otorisasi menghasilkan dokumen dengan checksum sha256', function () {
    Storage::fake('public');

    $cm = User::factory()->create();
    $cm->assignRole('Change Manager');

    $pemohon = User::factory()->create();
    $authority = Authority::factory()->create();

    $rfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $pemohon->id,
        'status' => RfcStatus::Assessed->value,
    ]);

    $rfc->riskAssessment()->create([
        'assessed_by_id' => $cm->id,
        'system_criticality_score' => 3,
        'impact_scope_score' => 3,
        'rollback_readiness_score' => 2,
        'total_score' => 8,
        'risk_level' => 'medium',
        'mitigation_plan' => 'Mitigasi contoh',
        'rollback_plan' => 'Rollback contoh',
    ]);

    $isi = '%PDF-1.4 isi dokumen otorisasi contoh';
    $file = UploadedFile::fake()->createWithContent('sk_otorisasi.pdf', $isi);
    expect(file_get_contents($file->getRealPath()))->toBe($isi);

    $this->actingAs($cm);

    Livewire::test(\App\Livewire\ChangeRequests\RecordDecision::class, ['rfc' => $rfc])
        ->set('authorityId', $authority->id)
        ->set('decisionStatus', 'approved')
        ->set('notes', 'Disetujui berdasarkan hasil evaluasi teknis.')
        ->set('documentNumber', '500/SK-CAB/2026')
        ->set('documentFile', $file)
        ->call('saveDecision')
        ->assertHasNoErrors();

    expect($rfc->fresh()->status)->toBe(RfcStatus::Approved->value);

    $doc = $rfc->fresh()->authorizationDocuments()->first();
    expect($doc)->not->toBeNull();
    expect($doc->checksum_sha256)->toBe(hash('sha256', $isi));

    $decision = $rfc->fresh()->decisions()->first();
    expect($decision->snapshot_authority_name)->toBe($authority->name);
    expect($decision->recorded_by_id)->toBe($cm->id);
    expect($doc->decision_id)->toBe($decision->id);
});
