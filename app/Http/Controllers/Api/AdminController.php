<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Child;
use App\Models\User;
use App\Services\AdminNotificationService;
use App\Services\SystemSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct(
        private SystemSettingsService $settingsService,
        private AdminNotificationService $notificationService,
    ) {}

    /**
     * Get dashboard statistics
     */
    public function dashboardStats(): JsonResponse
    {
        $totalChildren = Child::count();
        $totalUsers = User::count();
        $verifiedChildren = Child::where('status', 'verified')->count();
        $pendingChildren = Child::where('status', 'pending')->count();
        $missingChildren = Child::where('status', 'missing')->count();
        $systemAlerts = $this->notificationService->unreadCount();

        return response()->json([
            'data' => [
                'total_children' => $totalChildren,
                'total_users' => $totalUsers,
                'verified_children' => $verifiedChildren,
                'pending_children' => $pendingChildren,
                'missing_children' => $missingChildren,
                'total_organizations' => User::query()->whereIn('role', ['nurse', 'police'])->count(),
                'system_alerts' => $systemAlerts,
            ]
        ]);
    }

    /**
     * Get list of users with search mechanism
     */
    public function getUsers(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Create a new authorized node user
     */
    public function createUser(Request $request): JsonResponse
    {
        if ($request->has('fullName') && !$request->has('name')) {
            $request->merge(['name' => $request->input('fullName')]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:admin,nurse,police,user'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'System node user provisioned successfully.',
            'data' => $user
        ], 210);
    }

    /**
     * Update existing node user credentials
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        if ($request->has('fullName') && !$request->has('name')) {
            $request->merge(['name' => $request->input('fullName')]);
        }

        $validated = $request->validate([
            'name' => ['p_filled', 'string', 'max:255'],
            'email' => ['p_filled', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['p_filled', 'string', 'in:admin,nurse,police,user'],
        ]);

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User credentials updated inside nodes.',
            'data' => $user
        ]);
    }

    /**
     * Destroy / Revoke node member account
     */
    public function deleteUser(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User access token revoked completely.'
        ]);
    }

    /**
     * Load recent children registrations
     */
    public function loadChildren(): JsonResponse
    {
        $children = Child::orderBy('created_at', 'desc')->take(10)->get();
        return response()->json([
            'status' => 'success',
            'data' => $children
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $items = AdminNotification::query()
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => collect($items->items())->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'is_read' => (bool) $notification->read_at,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function notificationsUnreadCount(): JsonResponse
    {
        return response()->json([
            'data' => [
                'unread_count' => $this->notificationService->unreadCount(),
            ],
        ]);
    }

    public function markNotificationRead(AdminNotification $notification): JsonResponse
    {
        $notification = $this->notificationService->markRead($notification);

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => [
                'id' => $notification->id,
                'is_read' => (bool) $notification->read_at,
                'read_at' => $notification->read_at?->toIso8601String(),
            ],
        ]);
    }

    public function markAllNotificationsRead(): JsonResponse
    {
        $updatedCount = $this->notificationService->markAllRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
            'data' => [
                'updated' => $updatedCount,
            ],
        ]);
    }
}