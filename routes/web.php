<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\ReceipeHppController;
use App\Http\Controllers\DaftarBelanjaController;
use App\Http\Controllers\WasteLossController;
use App\Http\Controllers\LaporanPenjualanController;

// Public routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    // Temporary: auto-login as admin
    auth()->loginUsingId(1);
    return redirect('/');
})->name('login.post');

// Protected routes (require auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Ingredients routes
    Route::get('/ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
    Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
    Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show'])->name('ingredients.show');
    Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update');
    Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy'])->name('ingredients.destroy');
    
    // Production Batch routes
    Route::get('/batches', [ProductionBatchController::class, 'index'])->name('batches.index');
    Route::post('/batches', [ProductionBatchController::class, 'store'])->name('batches.store');
    Route::post('/batches/{batch}/record-sale', [ProductionBatchController::class, 'recordSale'])->name('batches.record-sale');
    Route::post('/batches/generate-report', [ProductionBatchController::class, 'generateReport'])->name('batches.generate-report');
    
    // Recipe & HPP routes
    Route::get('/recipes', [ReceipeHppController::class, 'index'])->name('recipes.index');
    Route::post('/recipes', [ReceipeHppController::class, 'store'])->name('recipes.store');
    Route::get('/recipes/{recipe}', [ReceipeHppController::class, 'show'])->name('recipes.show');
    Route::put('/recipes/{recipe}', [ReceipeHppController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [ReceipeHppController::class, 'delete']);
    
    // Shopping List routes
    Route::get('/shopping-lists', [DaftarBelanjaController::class, 'index'])->name('shopping-lists.index');
    Route::post('/shopping-lists', [DaftarBelanjaController::class, 'store'])->name('shopping-lists.store');
    
    // Waste & Loss routes
    Route::get('/waste-loss', [WasteLossController::class, 'index'])->name('waste-loss.index');
    Route::post('/waste-loss', [WasteLossController::class, 'store'])->name('waste-loss.store');
    
    // Sales Report routes
    Route::get('/laporan-penjualan', [LaporanPenjualanController::class, 'index'])->name('laporan-penjualan.index');
    
    // Placeholder routes (untuk nanti)
    Route::get('/analytics', function () {
        return redirect()->route('dashboard');
    })->name('analytics.index');
    
    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/login');
    })->name('logout');
});
