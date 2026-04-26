<?php

use App\Livewire\Dashboard;
use App\Livewire\Claims\ClaimList;
use App\Livewire\Claims\ClaimCreate;
use App\Livewire\Claims\ClaimDetail;
use App\Livewire\Claims\ClaimAppeal;
use App\Livewire\Payments\PaymentList;
use App\Livewire\Settings\UserSettings;
use App\Livewire\Users\UserList;
use App\Livewire\Roles\RoleList;
use App\Http\Controllers\FclFormController;
use App\Livewire\Configuration\DocumentConfigPage;
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
    Route::get('/tuntutan/{claim}/rayuan', ClaimAppeal::class)->name('claims.appeal');

    // Payments
    Route::get('/pembayaran', PaymentList::class)->name('payments.index')->middleware('can:payments.view');

    // Users (Admin)
    Route::get('/pengguna', UserList::class)->name('users.index')->middleware('can:users.manage');

    // FCL Form PDF download
    Route::post('/tuntutan/fcl-form/download', [FclFormController::class, 'download'])->name('claims.fcl.download');

    // FCL Form preview with pre-filled test data (styling only)
    Route::get('/tuntutan/fcl-form/preview', [FclFormController::class, 'preview'])->name('claims.fcl.preview');

    // Configuration (Admin only)
    Route::get('/konfigurasi', DocumentConfigPage::class)->name('configuration.index')->middleware('role:admin');

    // Roles & Permissions (Admin only)
    Route::get('/peranan', RoleList::class)->name('roles.index')->middleware('role:admin');

    // Settings
    Route::get('/settings', UserSettings::class)->name('settings');
});

require __DIR__.'/auth.php';
