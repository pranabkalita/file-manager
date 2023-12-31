<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
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
    Route::get('/my-files/{folder?}', [FileController::class, 'myFiles'])
        ->where('folder', '(.*)')->name('myFiles');

    Route::post('/folder/create', [FileController::class, 'createFolder'])->name('folder.create');
    Route::post('/file', [FileController::class, 'store'])->name('file.store');
    Route::delete('/file', [FileController::class, 'destroy'])->name('files.delete');
    Route::delete('/file/delete-forever', [FileController::class, 'deleteForever'])->name('files.deleteForever');
    Route::get('/file/download', [FileController::class, 'download'])->name('files.download');
    Route::get('/trash', [FileController::class, 'trash'])->name('trash');
    Route::post('/restore', [FileController::class, 'restore'])->name('restore');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
