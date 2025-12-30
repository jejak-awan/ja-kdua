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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'min:8', new StrongPassword()],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'avatar' => 'nullable|string',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();
        
        $user = User::create($validated);

        if ($request->has('roles')) {
            $maxRequestedRank = 0;
            $roles = \Spatie\Permission\Models\Role::whereIn('id', $request->roles)->get();
            
            $roleRanks = [
                'super-admin' => 100,
                'admin'       => 80,
                'editor'      => 60,
                'author'      => 40,
                'member'      => 20,
            ];

            foreach ($roles as $role) {
                $rank = $roleRanks[$role->name] ?? 0;
                if ($rank > $maxRequestedRank) {
                    $maxRequestedRank = $rank;
                }
            }

            if ($maxRequestedRank > auth()->user()->getRoleRank()) {
                return $this->forbidden('You cannot assign a role higher than your own rank');
            }

            $user->syncRoles($request->roles);
        }

        return $this->success($user->load(['roles', 'permissions']), 'User created successfully', 201);
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
            'password' => ['sometimes', 'nullable', 'min:8', new StrongPassword()],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'avatar' => 'nullable|string',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'is_verified' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (isset($validated['is_verified'])) {
            $user->email_verified_at = $validated['is_verified'] ? now() : null;
            unset($validated['is_verified']);
        }

        $user->update($validated);

        // Guard: Hierarchy check
        if (!auth()->user()->isHigherThan($user) && auth()->id() !== $user->id) {
            return $this->forbidden('You can only manage users with a lower rank than yours');
        }

        if ($request->has('roles')) {
            $maxRequestedRank = 0;
            $roles = \Spatie\Permission\Models\Role::whereIn('id', $request->roles)->get();
            
            $roleRanks = [
                'super-admin' => 100,
                'admin'       => 80,
                'editor'      => 60,
                'author'      => 40,
                'member'      => 20,
            ];

            foreach ($roles as $role) {
                $rank = $roleRanks[$role->name] ?? 0;
                if ($rank > $maxRequestedRank) {
                    $maxRequestedRank = $rank;
                }
            }

            // Cannot assign role higher than own
            if ($maxRequestedRank > auth()->user()->getRoleRank()) {
                return $this->forbidden('You cannot assign a role higher than your own rank');
            }

            // Guard: cannot remove super-admin role from the last super-admin
            $isCurrentlySuperAdmin = $user->hasRole('super-admin');
            $requestedSuperAdmin = $roles->contains('name', 'super-admin');

            if ($isCurrentlySuperAdmin && !$requestedSuperAdmin) {
                $superAdminCount = User::role('super-admin')->count();
                if ($superAdminCount <= 1) {
                    return $this->validationError(['roles' => ['Cannot remove the last super-admin role']], 'Cannot remove the last super-admin role');
                }
            }

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

        // Prevent deleting users with higher or equal rank
        if (!auth()->user()->isHigherThan($user)) {
            return $this->forbidden('You can only delete users with a lower rank than yours');
        }

        // Prevent deleting the last super-admin
        if ($user->hasRole('super-admin')) {
            $superAdminCount = User::role('super-admin')->count();
            if ($superAdminCount <= 1) {
                return $this->validationError(['user' => ['Cannot delete the last super-admin account']], 'Cannot delete the last super-admin account');
            }
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
        // Guard: Hierarchy check
        if (!auth()->user()->isHigherThan($user) && auth()->id() !== $user->id) {
            return $this->forbidden('You can only manage users with a lower rank than yours');
        }

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

    /**
     * Verify a user's email manually.
     */
    public function verify(User $user)
    {
        // Guard: Hierarchy check
        if (!auth()->user()->isHigherThan($user) && auth()->id() !== $user->id) {
            return $this->forbidden('You can only manage users with a lower rank than yours');
        }

        if ($user->email_verified_at) {
            return $this->error('User is already verified', 400);
        }

        $user->markEmailAsVerified();

        return $this->success($user->load(['roles', 'permissions']), 'User verified successfully');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'action' => 'required|in:delete,force_logout,verify',
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $count = 0;

        if ($action === 'delete') {
            // Filter out self-deletion and hierarchy protection
            $ids = array_filter($ids, function($id) {
                // Self deletion check
                if ($id == auth()->id()) return false;
                
                $target = User::find($id);
                if (!$target) return false;

                // Rank check
                if (!auth()->user()->isHigherThan($target)) return false;

                return true;
            });

            // Prevent deleting the last super-admin in bulk delete
            $superAdminsToDelete = User::whereIn('id', $ids)->role('super-admin')->count();
            if ($superAdminsToDelete > 0) {
                $totalSuperAdmins = User::role('super-admin')->count();
                if ($totalSuperAdmins - $superAdminsToDelete < 1) {
                    return $this->validationError(['ids' => ['Bulk action would leave the system without a super-admin']], 'Cannot delete the last super-admin');
                }
            }

            // Delete avatars
            $users = User::whereIn('id', $ids)->get();
            foreach ($users as $user) {
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
            }

            $count = User::whereIn('id', $ids)->delete();
            $message = "{$count} users deleted successfully";
        } elseif ($action === 'force_logout') {
            // Filter out self-logout and hierarchy protection
            $ids = array_filter($ids, function($id) {
                if ($id == auth()->id()) return false;
                
                $target = User::find($id);
                if (!$target) return false;
                if (!auth()->user()->isHigherThan($target)) return false;
                
                return true;
            });
            
            $users = User::whereIn('id', $ids)->get();
            foreach ($users as $user) {
                $user->tokens()->delete();
                $count++;
            }
            $message = "{$count} users force logged out successfully";
        } elseif ($action === 'verify') {
            // Filter hierarchy protection
            $ids = array_filter($ids, function($id) {
                $target = User::find($id);
                if (!$target) return false;
                if (auth()->id() !== $id && !auth()->user()->isHigherThan($target)) return false;
                return true;
            });

            $users = User::whereIn('id', $ids)->whereNull('email_verified_at')->get();
            foreach ($users as $user) {
                $user->markEmailAsVerified();
                $count++;
            }
            $message = "{$count} users verified successfully";
        }

        return $this->success(['count' => $count], $message);
    }
}
