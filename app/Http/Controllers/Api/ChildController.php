<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChildRegistrationService;
use App\Services\FootprintAiService;
use App\Services\FootprintValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChildController extends Controller
{
    public function __construct(
        private ChildRegistrationService $childRegistration,
        private FootprintAiService $footprintAi,
        private FootprintValidationService $footprintValidation,
    ) {}

    /**
     * تسجيل طفل جديد (السطر 55)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:15'],
            'father_national_id' => ['nullable', 'string', 'size:14'],
            'gender' => ['nullable', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'estimated_age' => ['nullable', 'string', 'max:50'],
            'found_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'child_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'footprint' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        // 🎯 التصحيح هنا: دالة register تتطلب (array, int, UploadedFile, UploadedFile)
        // Nurse ID مأخوذ من المستخدم المسجل
        $nurseId = Auth::id();

        $child = $this->childRegistration->register(
            $validated,
            $nurseId,
            $request->file('footprint'),
            $request->file('child_photo')
        );

        return response()->json(['status' => 'success', 'data' => $child], 201);
    }

    /**
     * التحقق من البصمة (السطر 161)
     */
    public function validateFootprint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fingerprint_image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'child_id' => ['nullable', 'integer', 'exists:children,id'],
        ]);

        // 🎯 التصحيح هنا: الدالة تتطلب (UploadedFile, ?int)
        $result = $this->footprintValidation->validate(
            $request->file('fingerprint_image'),
            $validated['child_id'] ?? null
        );

        return response()->json($result, 200);
    }
}