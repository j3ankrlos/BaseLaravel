<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\UserManagement;
use App\Livewire\Dashboard;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;

// Redirección inicial
Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/login');
});

// Ruta de Login (requerida por el middleware 'auth')
Route::get('/login', Login::class)->name('login');

use App\Livewire\Quarantine\QuarantineManagement;
use App\Livewire\Quarantine\QuarantineSegregation;

// Rutas protegidas (Usuario Logueado)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/quarantine', QuarantineManagement::class)->name('quarantine.index');
    Route::get('/quarantine/incorporation', \App\Livewire\Quarantine\QuarantineIncorporation::class)->name('quarantine.incorporation');
    Route::get('/quarantine/{batch}/segregation', QuarantineSegregation::class)->name('quarantine.segregation');
    Route::get('/users', UserManagement::class)->name('users');
    Route::get('/roles', \App\Livewire\RoleManagement::class)->name('roles');
    Route::get('/permissions', \App\Livewire\PermissionManagement::class)->name('permissions');
    
    Route::get('/employees', \App\Livewire\EmployeeManagement::class)->name('employees');
    Route::get('/attendance', \App\Livewire\AttendanceManagement::class)->name('attendance');
    Route::get('/incidents', \App\Livewire\IncidentManagement::class)->name('incidents');
    Route::get('/warehouse/a002', \App\Livewire\WarehouseA002Management::class)->name('warehouse.a002');
    Route::get('/warehouse/a006', \App\Livewire\WarehouseA006Management::class)->name('warehouse.a006');
    Route::get('/warehouse/requests', \App\Livewire\TransferRequestManagement::class)->name('warehouse.requests');
    Route::get('/certificates', \App\Livewire\CertificateManagement::class)->name('certificates.index');
    Route::get('/certificates/create', \App\Livewire\CertificateCreate::class)->name('certificates.create');
    Route::get('/certificates/causes', \App\Livewire\DeathCauseManagement::class)->name('certificates.causes');

    // Selección Genética
    Route::get('/genetic-selection/births', \App\Livewire\GeneticSelection\BirthRegistration::class)->name('genetics.births.create');
    Route::get('/genetic-selection/list', \App\Livewire\GeneticSelection\BirthManagement::class)->name('genetics.births.index');
    Route::get('/genetic-selection/pedigree/{id}', \App\Livewire\GeneticSelection\BirthPedigree::class)->name('genetics.births.pedigree');
    Route::get('/genetic-selection/pedigree-preview/{room}', \App\Livewire\GeneticSelection\PedigreePreview::class)->name('genetics.births.pedigree-preview');
    Route::get('/genetic-selection/edit/{id}', \App\Livewire\GeneticSelection\BirthEdit::class)->name('genetics.births.edit');

    // Recría
    Route::get('/rearing/entries', \App\Livewire\Rearing\RearingEntryManagement::class)->name('rearing.entries');

    // Operaciones Centralizadas / Movement Hub
    Route::get('/inventory/movements', \App\Livewire\Inventory\MovementOperations::class)->name('inventory.movements');
    Route::get('/inventory/list', \App\Livewire\Inventory\InventoryListView::class)->name('inventory.list');
    Route::get('/inventory/traceability', \App\Livewire\Inventory\TraceabilityViewer::class)->name('inventory.traceability');

    Route::get('/system/data-migration', \App\Livewire\System\DataMigration::class)->name('system.migration');
    Route::get('/system/feeds', \App\Livewire\System\FeedManagement::class)->name('system.feeds');

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
