<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Child;
use Illuminate\Support\Facades\Auth;

class MobileChildController extends Controller
{
    public function registerChild(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female',
        ]);

        $child = Child::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'status' => 'normal',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Child registered successfully',
            'child' => $child
        ], 201);
    }

    public function searchMissing(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $results = Child::where('status', 'missing')
            ->where('name', 'like', '%' . $request->name . '%')
            ->get();

        return response()->json([
            'status' => true,
            'results' => $results
        ], 200);
    }
}