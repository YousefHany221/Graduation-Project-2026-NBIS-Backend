<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Child;
use App\Models\VerificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Exception;

class ChildController extends Controller
{
    // 🎯 الدالة الأصلية: لتسجيل الأطفال العاديين
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'place_found' => 'required|string',
            'child_photo' => 'nullable|image',
            'footprint' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        $footprintPath = $request->file('footprint')->store('footprints', 'public');

        $child = Child::create([
            'name_en' => $validated['name_en'],
            'name_ar' => $validated['name_ar'],
            'dob' => $validated['dob'],
            'gender' => $validated['gender'],
            'place_found' => $validated['place_found'],
            'footprint_path' => $footprintPath,
        ]);

        return $this->sendToAiServer($child, $request->file('footprint'));
    }

    // ── 🆕 🎯 الدالة الجديدة: استقبال بيانات الـ Operational Hub
    public function registerFound(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'child_name'     => 'required|string|max:255',
            'estimated_age'  => 'nullable|string|max:100',
            'gender'         => 'required|in:male,female',
            'found_location' => 'required|string|max:255',
            'date_found'     => 'required|date',
            'case_id'        => 'nullable|string|max:100',
            'report_type'    => 'required|string',
            'notes'          => 'nullable|string',
            'child_photo'    => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'footprint'      => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $photoPath = null;
        $footprintPath = null;

        if ($request->hasFile('child_photo')) {
            $photoPath = $request->file('child_photo')->store('child_photos', 'public');
        }

        if ($request->hasFile('footprint')) {
            $footprintPath = $request->file('footprint')->store('footprints', 'public');
        }

        $child = Child::create([
            'name_en'          => $validated['child_name'],
            'name_ar'          => $validated['child_name'],
            'dob'              => $validated['date_found'],
            'gender'           => $validated['gender'],
            'place_found'      => $validated['found_location'],
            'child_photo_path' => $photoPath,
            'footprint_path'   => $footprintPath,
            'status'           => 'pending',
        ]);

        $log = VerificationLog::create([
            'child_id'    => $child->id,
            'user_id'     => auth()->check() ? auth()->id() : null,
            'type'        => $validated['report_type'],
            'status'      => 'pending',
            'child_name'  => $validated['child_name'],
            'verified_by' => auth()->check() ? (auth()->user()->name ?? 'Officer') : 'System Hub',
            'date'        => date('Y-m-d'),
        ]);

        if ($request->hasFile('footprint')) {
            return $this->sendToAiServer($child, $request->file('footprint'), $log);
        }

        return response()->json([
            'success' => true,
            'message' => 'Child registered & Log recorded successfully! 🎉',
            'child'   => $child
        ], 201);
    }

    // ── 🛠️ PRIVATE HELPER FUNCTION: لإرسال البصمة للـ AI
    private function sendToAiServer(Child $child, UploadedFile $footprintFile, $log = null): JsonResponse
    {
        $aiUrl = env('FOOTPRINT_AI_URL', 'https://default-ai-model-url.ngrok-free.dev/v1/match');

        try {
            $response = Http::attach(
                'file',
                file_get_contents($footprintFile->getRealPath()),
                'child_footprint.png'
            )->post($aiUrl, [
                'child_id' => $child->id
            ]);

            if ($response->successful()) {
                $aiData = $response->json();

                if (isset($aiData['matched']) && $aiData['matched'] === true) {
                    $child->update(['status' => 'verified']);
                    if ($log) {
                        $log->update(['status' => 'verified']);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Child registered and Footprint processed successfully via AI! 👣🎉',
                    'child' => $child,
                    'ai_response' => $aiData
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'Child saved, but AI Model failed to process footprint.',
                'error' => $response->body()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Child saved, but could not connect to Footprint AI Server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        return response()->json(Child::all(), 200);
    }

    public function show(Child $child)
    {
        return response()->json($child, 200);
    }
}