<?php

use App\Enums\RfcStatus;
use App\Exceptions\InvalidRfcStatusTransitionException;
use App\Models\ChangeRequest;
use App\Models\User;
use App\StateMachines\RfcStatusTransition;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('pemilik rfc dapat mengajukan draft', function () {
    $owner = userWithRole('Pemohon');
    $rfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $owner->id,
        'status' => RfcStatus::DRAFT->value,
    ]);

    RfcStatusTransition::transitionTo($rfc, RfcStatus::SUBMITTED, $owner);

    expect($rfc->fresh()->status)->toBe(RfcStatus::SUBMITTED->value);
});

test('admin dapat meneruskan submitted ke ticket_created', function () {
    $admin = userWithRole('Admin');
    $rfc = ChangeRequest::factory()->create([
        'status' => RfcStatus::SUBMITTED->value,
    ]);

    RfcStatusTransition::transitionTo($rfc, RfcStatus::TICKET_CREATED, $admin);

    expect($rfc->fresh()->status)->toBe(RfcStatus::TICKET_CREATED->value);
});

test('change manager dapat menjalankan approved sampai implemented lalu closed', function () {
    $cm = userWithRole('Change Manager');
    $rfc = ChangeRequest::factory()->create([
        'status' => RfcStatus::APPROVED->value,
    ]);

    RfcStatusTransition::transitionTo($rfc, RfcStatus::IN_PROGRESS, $cm);
    RfcStatusTransition::transitionTo($rfc, RfcStatus::IMPLEMENTED, $cm);
    RfcStatusTransition::transitionTo($rfc, RfcStatus::CLOSED, $cm);

    expect($rfc->fresh()->status)->toBe(RfcStatus::CLOSED->value);
});

test('transisi yang tidak ada di matriks ditolak', function () {
    $owner = userWithRole('Pemohon');
    $rfc = ChangeRequest::factory()->create([
        'submitted_by_id' => $owner->id,
        'status' => RfcStatus::DRAFT->value,
    ]);

    expect(fn () => RfcStatusTransition::transitionTo($rfc, RfcStatus::APPROVED, $owner))
        ->toThrow(InvalidRfcStatusTransitionException::class);
});

test('transisi dengan role yang salah ditolak', function () {
    $cm = userWithRole('Change Manager');
    $rfc = ChangeRequest::factory()->create([
        'status' => RfcStatus::SUBMITTED->value,
    ]);

    expect(fn () => RfcStatusTransition::transitionTo($rfc, RfcStatus::TICKET_CREATED, $cm))
        ->toThrow(InvalidRfcStatusTransitionException::class);
});

test('failed hanya dapat menuju closed', function () {
    $cm = userWithRole('Change Manager');
    $rfc = ChangeRequest::factory()->create([
        'status' => RfcStatus::FAILED->value,
    ]);

    RfcStatusTransition::transitionTo($rfc, RfcStatus::CLOSED, $cm);

    expect($rfc->fresh()->status)->toBe(RfcStatus::CLOSED->value);
});
