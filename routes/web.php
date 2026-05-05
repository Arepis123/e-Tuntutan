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
use App\Models\Claim;
use App\Models\ClaimDocument;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

    // Claim document download
    Route::get('/tuntutan/{claim}/documents/{document}/download', function (Claim $claim, ClaimDocument $document) {
        $user = auth()->user();
        abort_unless(
            $user->hasAnyRole(['admin', 'pic']) || $user->id === $claim->user_id,
            403
        );
        abort_if(! $document->path, 404);

        $disk     = $document->disk ?? 'local';
        $filename = $document->original_filename ?? basename($document->path);

        if (request()->boolean('inline')) {
            return Storage::disk($disk)->response($document->path, $filename);
        }

        return Storage::disk($disk)->download($document->path, $filename);
    })->name('claims.documents.download');

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
