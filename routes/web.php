<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\BabyController;
use App\Http\Controllers\PoliceController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\NurseDashboardController;
use App\Http\Controllers\PoliceDashboardController;
use App\Http\Controllers\ParentDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'NBIS Backend API is running successfully!',
        'version' => '1.0.0'
    ]);
});

// NBIS React demo (no auth)
Route::get('/demo/{any?}', function () {
    return view('demo-react');
})->where('any', '.*')->name('demo');

Route::get('/run-migrate', function () {
    try {
        Artisan::call('migrate --force');
        return response()->json([
            'status' => 'success',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::get('/clear-everything-nbis', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    return response()->json([
        'status' => 'success',
        'message' => 'All caches cleared successfully, Yousef!'
    ]);
});