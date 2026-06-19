<?php

namespace App\Http\Controllers;

use App\Services\ChildSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * معالجة طلبات البحث النصي المباشر القادمة من لوحة تحكم React للشرطة.
 */
class PoliceController extends Controller
{
    public function search(Request $request, ChildSearchService $childSearch): JsonResponse
    {
        $validated = $request->validate([
            'search_query' => ['nullable', 'string', 'max:255'],
        ]);

        // تنفيذ البحث عبر الخدمة المخصصة للـ Text Matching
        $results = $childSearch->searchByText($validated['search_query'] ?? null);

        // إرجاع النتيجة دائماً كـ JSON لتسهيل المعالجة في الـ Axios/Fetch بالفرونت
        return response()->json([
            'success' => true,
            'data'    => $childSearch->toSearchResultRows($results),
        ], 200);
    }
}