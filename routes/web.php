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

// NBIS React demo (no auth) — Admin & Police dashboards
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
        Route::get('/run-migrate', function () {
            Artisan::call('migrate --force');
            return "Database migrated successfully!";
    });

});

// Web dashboard routes disabled - using React frontend
// Uncomment these if you need Blade-based dashboards
/*
Route::middleware(['auth', 'verified'])->group(function () {

    // المسار العام بعد تسجيل الدخول
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        return match ($role) {
            'admin', 'nurse', 'police', 'user' => redirect()->route("{$role}.dashboard"),
            default => abort(403, 'Unknown role.'),
        };
    })->name('dashboard');
});

// المسارات المشتركة (تعديل الحساب)
Route::middleware('auth', 'verified')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- 🏥 منطقة الممرضة (Nurse Page) ---
// الصفحة نفسها للممرضة فقط — الأدمن عنده داشبورد منفصلة
Route::middleware(['auth', 'verified', 'role:nurse'])->group(function () {
    Route::get('/nurse/dashboard', [NurseDashboardController::class, 'index'])->name('nurse.dashboard');
});

// --- 👮 منطقة الشرطة (Police Page) ---
Route::middleware(['auth', 'verified', 'role:police'])->group(function () {
    Route::get('/police/dashboard', [PoliceDashboardController::class, 'index'])->name('police.dashboard');
});

// --- 👨‍👩‍👧 منطقة المستخدم/الأهل (User/Parent Pages) ---
Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [ParentDashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/user/children', [ParentDashboardController::class, 'children'])->name('user.children');
    Route::post('/user/report-missing', [ParentDashboardController::class, 'reportMissing'])->name('user.reportMissing');
});

// --- 🛡️ منطقة الأدمن (Admin Page) ---
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

// --- ⚡ الأكشنز المشتركة (Shared Actions) ---
Route::middleware(['auth', 'verified', 'role:nurse,admin'])->group(function () {
    Route::get('/children/create', [BabyController::class, 'create'])->name('children.create');
    Route::post('/children/store', [BabyController::class, 'store'])->name('children.store');
});

Route::middleware(['auth', 'verified', 'role:police,admin'])->group(function () {
    Route::post('/children/search', [PoliceController::class, 'search'])->name('children.search');
});
*/

// require __DIR__ . '/auth.php';