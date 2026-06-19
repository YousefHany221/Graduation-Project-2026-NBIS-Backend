<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileChildController;
use App\Http\Controllers\NurseDashboardController;
use App\Http\Controllers\PoliceDashboardController;
use App\Http\Controllers\PoliceController;

/*
|--------------------------------------------------------------------------
| API عام — React + Mobile (نفس قاعدة البيانات)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/user/profile', [AuthController::class, 'updateProfile']);
    Route::get('/settings', [AuthController::class, 'settings']);
    Route::put('/settings', [AuthController::class, 'updateSettings']);
});

/*
|--------------------------------------------------------------------------
| ولي الأمر (user) — الويب
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::get('/my-children', [ParentController::class, 'index']);
    Route::get('/parent/child-details/{child}', [ParentController::class, 'show']); // مسار التفاصيل المتوافق مع الفرونت
    Route::post('/report-missing', [ParentController::class, 'reportMissing']); // مسار الإبلاغ المتوافق مع الفرونت
    Route::get('/parent/reports', [ParentController::class, 'getReports']); // 🆕 جلب تقارير ولي الأمر لصفحة ParentVerification
    Route::post('/children/register-by-parent', [ChildController::class, 'storeByParent']);
});

/*
|--------------------------------------------------------------------------
| ممرضة / أدمن — تسجيل طفل وقائمة الأطفال (جدول children)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:nurse,admin'])->group(function () {
    Route::post('/children/register', [ChildController::class, 'store']);
    Route::get('/children', [ChildController::class, 'index']);
    Route::get('/children/{child}', [ChildController::class, 'show']);

    // الـ Route المربوط بالـ React Dashboard ليرجع الإحصائيات لايف للممرضة
    Route::get('/nurse/dashboard', [NurseDashboardController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| شرطة / أدمن — لوحة التحكم وبحث وسجلات التحقق (ربط الـ AI)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:police,admin'])->group(function () {
    // 🆕 لوحة تحكم الشرطة والإحصائيات والبحث الخاص بالشرطة المتوافق مع PoliceDashboard.jsx
    Route::get('/police/dashboard', [PoliceDashboardController::class, 'index']);
    Route::get('/police/search', [PoliceController::class, 'search']);

    Route::post('/children/text-search', [ChildController::class, 'textSearch']);
    Route::post('/children/search-by-footprint', [ChildController::class, 'searchByFootprint']);
    Route::post('/children/validate-footprint', [ChildController::class, 'validateFootprint']);

    // 🆕 تم ضبط هذا المسار ليتوافق تماماً مع fetch بقائمة السجلات في VerificationLogs.jsx
    Route::get('/logs', [AdminController::class, 'verificationLogs']);
    Route::get('/verification-logs', [AdminController::class, 'verificationLogs']);

    // 🎯 الـ Route المربوط بـ المودال لاستقبال البيانات والملفات حية من الـ Hub بالفرونت إند
    Route::post('/children/register-found', [ChildController::class, 'registerFound']);
});

/*
|--------------------------------------------------------------------------
| أدمن — إدارة النظام والـ Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Dashboard stats
    Route::get('/admin/dashboard/stats', [AdminController::class, 'dashboardStats']);
    Route::get('/admin/dashboard/children', [AdminController::class, 'childrenOverview']);

    // Users management
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::post('/admin/users', [AdminController::class, 'createUser']);
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser']);
    Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser']);

    // Children management
    Route::get('/admin/children', [AdminController::class, 'children']);
    Route::delete('/admin/children/{child}', [AdminController::class, 'deleteChild']);

    // Settings
    Route::get('/admin/settings', [AdminController::class, 'settings']);
    Route::put('/admin/settings', [AdminController::class, 'updateSettings']);

    // Notifications
    Route::get('/admin/notifications', [AdminController::class, 'notifications']);
    Route::get('/admin/notifications/unread-count', [AdminController::class, 'notificationsUnreadCount']);
    Route::patch('/admin/notifications/{notification}/read', [AdminController::class, 'markNotificationRead']);
    Route::patch('/admin/notifications/read-all', [AdminController::class, 'markAllNotificationsRead']);
});

/*
|--------------------------------------------------------------------------
| روابط الموبايل المخصصة (Mobile Application APIs)
|--------------------------------------------------------------------------
*/

// 1. روابط الحسابات المفتوحة للموبايل (بدون Token)
Route::post('/mobile/login', [MobileAuthController::class, 'login']);
Route::post('/mobile/register', [MobileAuthController::class, 'register']);

// 2. روابط الموبايل المحمية (تتطلب Token من Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/mobile/logout', [MobileAuthController::class, 'logout']);

    // روابط الأطفال والبحث من الموبايل
    Route::post('/mobile/children/register', [MobileChildController::class, 'registerChild']);
    Route::post('/mobile/children/search', [MobileChildController::class, 'searchMissing']);
});