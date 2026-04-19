<?php

use App\Livewire\Dashboard;
use App\Livewire\Claims\ClaimList;
use App\Livewire\Claims\ClaimCreate;
use App\Livewire\Claims\ClaimDetail;
use App\Livewire\Payments\PaymentList;
use App\Livewire\Settings\UserSettings;
use App\Livewire\Users\UserList;
use App\Livewire\Roles\RoleList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Claims
    Route::get('/tuntutan', ClaimList::class)->name('claims.index');
    Route::get('/tuntutan/baru', ClaimCreate::class)->name('claims.create')->middleware('can:claims.create');
    Route::get('/tuntutan/{claim}', ClaimDetail::class)->name('claims.show');

    // Payments
    Route::get('/pembayaran', PaymentList::class)->name('payments.index')->middleware('can:payments.view');

    // Users (Admin)
    Route::get('/pengguna', UserList::class)->name('users.index')->middleware('can:users.manage');

    // Roles & Permissions (Admin only)
    Route::get('/peranan', RoleList::class)->name('roles.index')->middleware('role:admin');

    // Settings
    Route::get('/settings', UserSettings::class)->name('settings');
});

require __DIR__.'/auth.php';
