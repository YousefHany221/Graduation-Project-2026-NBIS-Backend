<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChildRegistrationService;
use App\Services\FootprintAiService;
use App\Services\FootprintValidationService;
use App\Models\Child;
use App\Models\VerificationLog;
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
     * تسجيل طفل جديد عبر الممرضة (الويب)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'mother_name'        => ['nullable', 'string', 'max:255'],
            'father_name'        => ['nullable', 'string', 'max:255'],
            'father_phone'       => ['nullable', 'string', 'max:15'],
            'father_national_id' => ['nullable', 'string', 'size:14'],
            'gender'             => ['nullable', 'in:male,female'],
            'birth_date'         => ['nullable', 'date'],
            'estimated_age'      => ['nullable', 'string', 'max:50'],
            'found_location'     => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string'],
            'child_photo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'footprint'          => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        try {
            $nurseId = Auth::id();

            $child = $this->childRegistration->register(
                $validated,
                $nurseId,
                $request->file('footprint'),
                $request->file('child_photo')
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Child registered successfully inside the system.',
                'data' => $child
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration service failed to complete processing.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تسجيل البيانات المفقودة أو المعثور عليها حياً من الـ Operational Hub (الشرطة)
     */
    public function registerFound(Request $request): JsonResponse
    {
        if ($request->has('child_name') && !$request->has('name')) {
            $request->merge(['name' => $request->input('child_name')]);
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'estimated_age'  => ['nullable', 'string', 'max:100'],
            'gender'         => ['required', 'in:male,female'],
            'found_location' => ['required', 'string', 'max:255'],
            'notes'          => ['nullable', 'string'],
            'report_type'    => ['nullable', 'string'],
            'child_photo'    => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'footprint'      => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        try {
            $photoPath = $request->hasFile('child_photo') ? $request->file('child_photo')->store('child_photos', 'public') : null;
            $footprintPath = $request->hasFile('footprint') ? $request->file('footprint')->store('footprints', 'public') : null;

            $child = Child::create([
                'name'               => $validated['name'],
                'estimated_age'      => $validated['estimated_age'],
                'gender'             => $validated['gender'],
                'found_location'     => $validated['found_location'],
                'notes'              => $validated['notes'],
                'child_photo_path'   => $photoPath,
                'footprint_path'     => $footprintPath,
                'status'             => 'pending',
            ]);

            VerificationLog::create([
                'child_id'    => $child->id,
                'user_id'     => Auth::id(),
                'type'        => $validated['report_type'] ?? 'found',
                'status'      => 'pending',
                'child_name'  => $validated['name'],
                'verified_by' => Auth::user()->name ?? 'Officer',
                'date'        => date('Y-m-d'),
            ]);

            if ($request->hasFile('footprint')) {
                try {
                    $aiService = $this->footprintAi;

                    $aiService->match($child, $request->file('footprint'));
                } catch (\Exception $aiException) {
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Child registered & log recorded successfully.',
                'data'   => $child
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register hub record.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من البصمة ومطابقتها بالذكاء الاصطناعي
     */
    public function validateFootprint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fingerprint_image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'child_id'          => ['nullable', 'integer', 'exists:children,id'],
        ]);

        try {
            $childId = $request->input('child_id');

            $result = $this->footprintValidation->validate(
                $request->file('fingerprint_image'),
                $childId
            );

            return response()->json([
                'status' => 'success',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'AI Footprint validation pipeline encountered an error.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        return response()->json(Child::all(), 200);
    }

    public function show(Child $child): JsonResponse
    {
        return response()->json($child, 200);
    }
}