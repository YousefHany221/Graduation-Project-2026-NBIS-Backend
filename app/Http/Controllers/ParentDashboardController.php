<?php

namespace App\Http\Controllers;

use App\Services\ParentChildService;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * لوحة ولي الأمر (ويب Breeze + Blade) — اختيارية أثناء الانتقال إلى React.
 * نفس منطق الـ API عبر ParentChildService؛ التطبيق الرئيسي: Api\ParentController + Sanctum.
 */
class ParentDashboardController extends Controller
{
    public function __construct(
        private ParentChildService $parentChild,
    ) {}

    /**
     * عرض الصفحة الرئيسية للوحة التحكم
     */
    public function index(): View
    {
        return view('user.dashboard');
    }

    /** * عرض أطفال ولي الأمر الحالي 
     */
    public function children(Request $request): View
    {
        $children = $this->parentChild->childrenForParent($request->user());

        return view('user.children', compact('children'));
    }

    /**
     * عرض تفاصيل طفل معين (تم حل الـ Error وإضافة int وتحديد نوع المرجوع هنا 🎯)
     */
    public function show(Request $request, int $child): View|\Illuminate\Http\JsonResponse
    {
        $childRecord = Child::find($child);

        if (!$childRecord) {
            abort(404, 'Child not found');
        }

        return view('user.child-details', compact('childRecord'));
    }

    /** * استقبال بلاغ طفل مفقود 
     */
    public function reportMissing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'child_id' => ['required', 'integer', 'exists:children,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = $this->parentChild->reportMissing(
            $request->user(),
            (int) $validated['child_id'],
            $validated['notes'] ?? null
        );

        if (isset($result['status']) && $result['status'] === 'already_missing') {
            return redirect()->route('user.children')
                ->with('warning', __('This child is already reported as missing.'));
        }

        return redirect()->route('user.children')
            ->with('success', __('Missing child report submitted successfully.'));
    }
}