<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * تحديد إذا كان المستخدم مسموح له بعمل هذا الـ Request.
     */
    public function authorize(): bool
    {
        return true; // 🎯 تأكد إنها true عشان ميرفضش الـ Request بـ Unauthorized
    }

    /**
     * شروط التحقق من البيانات (Validation Rules).
     */
    public function rules(): array
    {
        // جلب الـ ID الخاص بالمستخدم الحالي المسجل بالـ Token بشكل آمن
        $userId = auth()->user()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                // يتأكد إن الإيميل مش متكرر، مع تجاهل إيميل المستخدم الحالي عشان ميرفضش التعديل
                Rule::unique(User::class)->ignore($userId)
            ],
        ];
    }
}