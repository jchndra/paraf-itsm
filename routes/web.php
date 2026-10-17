<?php

use App\Livewire\Auth\Login;
use App\Livewire\ChangeRequests\CreateChangeRequest;
use App\Livewire\ChangeRequests\ListChangeRequests;
use App\Livewire\ChangeRequests\RecordDecision;
use App\Livewire\ChangeRequests\ReviewCompleteness;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('change-requests.index');
});

// Route khusus tamu (belum terotentikasi)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Group route yang membutuhkan otentikasi login
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::get('/change-requests', ListChangeRequests::class)->name('change-requests.index');
    Route::get('/change-requests/create', CreateChangeRequest::class)->name('change-requests.create');
    Route::get('/change-requests/{rfc}', ReviewCompleteness::class)->name('change-requests.show');
    Route::get('/change-requests/{rfc}/decision', RecordDecision::class)->name('change-requests.decision');
});
