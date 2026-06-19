<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NurseDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        // 1. حساب إحصائيات النظام بالكامل لتغذية كروت الـ React بدقة
        // تم تعديل الأسماء هنا لتطابق ما يطلبه ملف nurseDashboard.jsx تماماً
        $totalChildrenToday = Child::count(); // إجمالي الأطفال بالنظام

        $verifiedChildren = Child::whereIn('status', ['Verified', 'verified'])->count();
        $pendingChildren  = Child::whereIn('status', ['Pending', 'pending'])->count();
        $alertChildren    = Child::whereIn('status', ['Alerts', 'Alert', 'missing', 'missing_report'])->count();

        // 2. جلب آخر 10 أطفال تم تسجيلهم في النظام لعرضهم في جدول الـ Live-birth Stream
        $childrenList = Child::orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'name_en', 'mother_name', 'status', 'created_at'])
            ->map(function ($child) {
                return [
                    'id'           => $child->id,
                    'name_en'      => $child->name_en ?? 'Unknown Infant',
                    'mother_name'  => $child->mother_name ?? 'N/A',
                    'status'       => $child->status ?? 'Pending',
                    // إذا كان الحقل Carbon، يحسب الوقت تلقائياً، وإلا يضع الوقت الحالي
                    'created_at'   => $child->created_at ? $child->created_at->toIso8601String() : now()->toIso8601String(),
                    'last_check'   => $child->created_at ? $child->created_at->diffForHumans() : 'Just Now'
                ];
            });

        // 3. إرسال الرد كـ JSON متوافق 100% مع دالة fetchDashboardData بالفرونت
        return response()->json([
            'success' => true,
            'stats' => [
                'total_today'    => $totalChildrenToday, // مربوط بـ data.stats.total_today
                'verified_count' => $verifiedChildren,   // مربوط بـ data.stats.verified_count
                'pending_count'  => $pendingChildren,    // مربوط بـ data.stats.pending_count
                'issues_count'   => $alertChildren,      // مربوط بـ data.stats.issues_count
            ],
            'recent_children' => $childrenList,
            'children'        => $childrenList // Fallback تأكيدي للفرونت إند
        ], 200);
    }
}