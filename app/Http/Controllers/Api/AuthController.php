<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * تسجيل مستخدم جديد (Register)
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'nullable|string|in:user,nurse,police,admin'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            /** @var \App\Models\User $user */
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role ?? 'user',
            ]);

            // الآن سيتعرف الـ VS Code على الدالة فوراً بدون خطأ أحمر
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status'  => 'success',
                'message' => 'User registered successfully',
                'user'    => $user,
                'token'   => $token
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Registration failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تسجيل الدخول (Login)
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => 'error',
                'message' => 'بيانات الاعتماد غير صحيحة، تأكد من البريد الإلكتروني أو كلمة المرور'
            ], 401);
        }

        try {
            // 🎯 السحر هنا: نخبر الـ Editor أن الكائن الراجع هو من موديل User لتختفي المشكلة P1013
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status'  => 'success',
                'message' => 'Logged in successfully',
                'user'    => $user,
                'token'   => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Login process failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب بيانات المستخدم الحالي (Me)
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'user'   => $request->user()
        ]);
    }

    /**
     * تسجيل الخروج (Logout)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // حذف التوكنز الحالية للمستخدم
            $request->user()->currentAccessToken()->delete();

            // إنهاء الجلسة للـ Web
            Auth::guard('web')->logout();

            return response()->json([
                'status'  => 'success',
                'message' => 'Logged out successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Logout failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}