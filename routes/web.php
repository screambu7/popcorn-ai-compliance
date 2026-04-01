<?php

use App\Http\Controllers\AvisoController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\OperacionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScreeningController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('expedientes', ExpedienteController::class)->except(['edit', 'destroy']);

    // Operaciones
    Route::resource('operaciones', OperacionController::class)->except(['edit', 'destroy']);

    // Avisos SPPLD
    Route::resource('avisos', AvisoController::class)->only(['index', 'show']);
    Route::post('avisos/generate', [AvisoController::class, 'generate'])->name('avisos.generate');
    Route::post('avisos/{aviso}/approve', [AvisoController::class, 'approve'])->name('avisos.approve');
    Route::put('avisos/{aviso}/estado', [AvisoController::class, 'updateEstado'])->name('avisos.updateEstado');

    // Screening
    Route::get('screening', [ScreeningController::class, 'index'])->name('screening.index');
    Route::put('screening/{result}/review', [ScreeningController::class, 'review'])->name('screening.review');

    // Documentos
    Route::resource('documentos', DocumentoController::class)->only(['index', 'store']);
    Route::post('documentos/{documento}/verify', [DocumentoController::class, 'verify'])->name('documentos.verify');
    Route::get('documentos/{documento}/download', [DocumentoController::class, 'download'])->name('documentos.download');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
