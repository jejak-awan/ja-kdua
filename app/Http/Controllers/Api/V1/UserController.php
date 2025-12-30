<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'permissions']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $perPage = $request->input('per_page', 20);
        $users = $query->latest()->paginate($perPage);

        // Ensure roles and permissions are always arrays (not null)
        $users->getCollection()->transform(function ($user) {
            // Ensure roles is always a collection (will be serialized as array in JSON)
            if (! $user->relationLoaded('roles') || $user->roles === null) {
                $user->setRelation('roles', collect([]));
            }
            // Ensure permissions is always a collection (will be serialized as array in JSON)
            if (! $user->relationLoaded('permissions') || $user->permissions === null) {
                $user->setRelation('permissions', collect([]));
            }

            return $user;
        });

        return $this->paginated($users, 'Users retrieved successfully');
    }

    public function show(User $user)
    {
        return $this->success($user->load(['roles', 'permissions']), 'User retrieved successfully');
    }

    public function profile(Request $request)
    {
        return $this->success($request->user()->load(['roles', 'permissions']), 'Profile retrieved successfully');
    }

    public function loginHistory(Request $request)
    {
        $user = $request->user();
        
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);
        
        $history = \App\Models\LoginHistory::where('user_id', $user->id)
            ->orderBy('login_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->success($history, 'Login history retrieved successfully');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'avatar' => 'nullable|string',
        ]);

        $user->update($validated);

        return $this->success($user->load(['roles', 'permissions']), 'Profile updated successfully');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048', // 2MB max
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return $this->success([
            'avatar' => Storage::disk('public')->url($path),
            'user' => $user->load(['roles', 'permissions']),
        ], 'Avatar uploaded successfully');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', 'min:8', new StrongPassword()],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return $this->validationError(['current_password' => ['Current password is incorrect']], 'Current password is incorrect');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->success(null, 'Password updated successfully');
    }

    /**
     * Get user preferences.
     */
    public function getPreferences(Request $request)
    {
        $user = $request->user();
        
        return $this->success([
            'dark_mode' => $user->getPreference('dark_mode', 'system'),
            'locale' => $user->getPreference('locale', 'en'),
        ], 'Preferences retrieved successfully');
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'dark_mode' => 'sometimes|string|in:light,dark,system',
            'locale' => 'sometimes|string|max:10',
        ]);

        $user = $request->user();
        
        foreach ($validated as $key => $value) {
            $user->setPreference($key, $value);
        }
        
        $user->save();

        return $this->success([
            'dark_mode' => $user->getPreference('dark_mode', 'system'),
            'locale' => $user->getPreference('locale', 'en'),
        ], 'Preferences updated successfully');
    }


    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->update($validated);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return $this->success($user->load(['roles', 'permissions']), 'User updated successfully');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return $this->validationError(['user' => ['You cannot delete your own account']], 'You cannot delete your own account');
        }

        // Delete avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }

    /**
     * Force logout a user from all devices by revoking all their tokens.
     * Admin-only action for security management.
     */
    public function forceLogout(User $user)
    {
        // Prevent force logging out yourself
        if ($user->id === auth()->id()) {
            return $this->validationError(
                ['user' => ['You cannot force logout your own account']],
                'You cannot force logout your own account'
            );
        }

        // Count and revoke all tokens
        $tokenCount = $user->tokens()->count();
        $user->tokens()->delete();

        // Log this security action
        \App\Models\SecurityLog::log(
            'force_logout',
            $user,
            request()->ip(),
            "Admin force logged out user from {$tokenCount} device(s)",
            [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
                'revoked_sessions' => $tokenCount,
            ]
        );

        return $this->success([
            'revoked_sessions' => $tokenCount,
        ], "User logged out from {$tokenCount} device(s) successfully");
    }
}
