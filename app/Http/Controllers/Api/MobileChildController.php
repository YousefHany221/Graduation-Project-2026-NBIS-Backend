<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Child; // تأكد أن الموديل عندك اسمه Child
use Illuminate\Support\Facades\Auth;

class MobileChildController extends Controller
{
    // 1. رابط لتسجيل طفل جديد من الموبايل
    public function registerChild(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female',
        ]);

        // إنشاء الطفل وربطه تلقائياً بالـ User اللي مسجل دخول حالياً
        $child = Child::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'status' => 'normal', // الحالة الافتراضية إنه سليم ومش مفقود
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الطفل بنجاح على قاعدة البيانات',
            'child' => $child
        ], 201);
    }

    // 2. رابط للبحث عن الأطفال المفقودين بالاسم
    public function searchMissing(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        // البحث في جدول الأطفال عن الاسم المشابه وتكون حالتهم مفقود (missing)
        $results = Child::where('status', 'missing')
            ->where('name', 'like', '%' . $request->name . '%')
            ->get();

        return response()->json([
            'status' => true,
            'results' => $results
        ], 200);
    }


    
}