<?php

use App\Enums\DecisionStatus;
use App\Enums\RfcStatus;
use App\Livewire\ChangeRequests\AssessRisk;
use App\Livewire\ChangeRequests\RecordDecision;
use App\Models\Authority;
use App\Models\ChangeRequest;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('change manager dapat mengisi analisis risiko tanpa mengubah status ticket created', function () {
    $cm = User::factory()->create();
    $cm->assignRole('Change Manager');

    $pemohon = User::factory()->create();

    $rfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $pemohon->id,
        'status' => RfcStatus::TICKET_CREATED->value,
    ]);

    $this->actingAs($cm);

    Livewire::test(AssessRisk::class, ['rfc' => $rfc])
        ->set('risk_level', 'Sedang')
        ->set('impact_level', 'Sedang')
        ->set('urgency_level', 'Normal')
        ->set('classification', 'Normal Change')
        ->set('analysis_notes', 'Rencana mitigasi penggantian server cadangan dan pengujian pada staging.')
        ->call('submitAssessment')
        ->assertHasNoErrors();

    expect($rfc->fresh()->status)->toBe(RfcStatus::TICKET_CREATED->value);
    expect($rfc->fresh()->riskAssessment)->not->toBeNull();
});

test('keputusan otorisasi ditolak jika analisis risiko belum ada', function () {
    $cm = User::factory()->create();
    $cm->assignRole('Change Manager');

    $authority = Authority::factory()->create();

    $rfc = ChangeRequest::factory()->create([
        'status' => RfcStatus::TICKET_CREATED->value,
    ]);

    $this->actingAs($cm);

    Livewire::test(RecordDecision::class, ['rfc' => $rfc])
        ->set('authorityId', $authority->id)
        ->set('decisionStatus', DecisionStatus::APPROVED->value)
        ->set('notes', 'Disetujui berdasarkan hasil evaluasi teknis.')
        ->call('saveDecision')
        ->assertHasErrors(['decisionStatus']);

    expect($rfc->fresh()->status)->toBe(RfcStatus::TICKET_CREATED->value);
});

test('keputusan otorisasi tercatat beserta snapshot pejabat', function () {
    $cm = User::factory()->create();
    $cm->assignRole('Change Manager');

    $pemohon = User::factory()->create();
    $authority = Authority::factory()->create();

    $rfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $pemohon->id,
        'status' => RfcStatus::TICKET_CREATED->value,
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

    $this->actingAs($cm);

    Livewire::test(RecordDecision::class, ['rfc' => $rfc])
        ->set('authorityId', $authority->id)
        ->set('decisionStatus', DecisionStatus::APPROVED->value)
        ->set('notes', 'Disetujui berdasarkan hasil evaluasi teknis.')
        ->call('saveDecision')
        ->assertHasNoErrors();

    expect($rfc->fresh()->status)->toBe(RfcStatus::APPROVED->value);

    $decision = $rfc->fresh()->decisions()->first();
    expect($decision)->not->toBeNull();
    expect($decision->snapshot_authority_name)->toBe($authority->name);
    expect($decision->recorded_by_id)->toBe($cm->id);
    expect($decision->decision_status)->toBe(DecisionStatus::APPROVED->value);
});

test('change manager dapat mengembalikan rfc ke admin', function () {
    $cm = User::factory()->create();
    $cm->assignRole('Change Manager');

    $authority = Authority::factory()->create();

    $rfc = ChangeRequest::factory()->create([
        'status' => RfcStatus::TICKET_CREATED->value,
    ]);

    $rfc->riskAssessment()->create([
        'assessed_by_id' => $cm->id,
        'system_criticality_score' => 2,
        'impact_scope_score' => 2,
        'rollback_readiness_score' => 2,
        'total_score' => 6,
        'risk_level' => 'medium',
        'mitigation_plan' => 'Mitigasi contoh',
        'rollback_plan' => 'Rollback contoh',
    ]);

    $this->actingAs($cm);

    Livewire::test(RecordDecision::class, ['rfc' => $rfc])
        ->set('authorityId', $authority->id)
        ->set('decisionStatus', DecisionStatus::RETURNED_TO_ADMIN->value)
        ->set('notes', 'Perlu pengecekan ulang oleh Admin.')
        ->call('saveDecision')
        ->assertHasNoErrors();

    expect($rfc->fresh()->status)->toBe(RfcStatus::RETURNED_TO_ADMIN->value);
    expect($rfc->fresh()->decisions()->first()->decision_status)->toBe(DecisionStatus::RETURNED_TO_ADMIN->value);
});
