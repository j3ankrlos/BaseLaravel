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

// Autenticación (Invitados)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Rutas protegidas (Usuario Logueado)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
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

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
