<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserService
{
    /**
     * Create a new user with role assignment
     */
    public function createUser(array $userData, int $roleId): User
    {
        return DB::transaction(function () use ($userData, $roleId) {
            $user = User::create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);

            $user->roles()->attach($roleId);
            
            $this->syncRoleSidebars($user->id, $roleId);

            $this->logActivity('user_created', $user, [
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $roleId
            ]);

            return $user;
        });
    }

    /**
     * Update user information
     */
    public function updateUser(User $user, array $userData): User
    {
        return DB::transaction(function () use ($user, $userData) {
            $oldData = $user->only(['name', 'email']);
            
            $user->update([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
            ]);

            // Update password if provided
            if (!empty($userData['password'])) {
                $user->update([
                    'password' => Hash::make($userData['password'])
                ]);
                $oldData['password_changed'] = true;
            }

            // Log activity
            $this->logActivity('user_updated', $user, [
                'old_data' => $oldData,
                'new_data' => $userData
            ]);

            return $user;
        });
    }

    /**
     * Delete user with safety checks
     */
    public function deleteUser(User $user): bool
    {
        // Safety checks
        if (Auth::id() == $user->id) {
            throw new \Exception('Cannot delete your own account');
        }

        if ($user->hasRole('root')) {
            throw new \Exception('Cannot delete root user');
        }

        return DB::transaction(function () use ($user) {
            $userData = $user->only(['name', 'email']);
            
            // Detach all roles before deletion
            $user->roles()->detach();
            
            // Log activity before deletion
            $this->logActivity('user_deleted', $user, $userData);
            
            return $user->delete();
        });
    }

    /**
     * Assign role to user
     */
    public function assignRole(User $user, int $roleId): void
    {
        DB::transaction(function () use ($user, $roleId) {
            // Check if user already has this role
            if ($user->roles()->where('role_id', $roleId)->exists()) {
                throw new \Exception('User already has this role');
            }

            $user->roles()->attach($roleId);
            
            // Auto-sync sidebars from role template
            $this->syncRoleSidebars($user->id, $roleId);

            $role = Role::findOrFail($roleId);
            // Log activity
            $this->logActivity('role_assigned', $user, [
                'role_name' => $role->name,
                'role_id' => $roleId
            ]);
        });
    }

    /**
     * Remove role from user
     */
    public function removeRole(int $roleUserId): void
    {
        DB::transaction(function () use ($roleUserId) {
            $roleUser = \App\Models\RoleUser::findOrFail($roleUserId);
            
            // Cannot remove root role
            if ($roleUser->role->name === 'root') {
                throw new \Exception('Cannot remove root role');
            }

            $userData = $roleUser->user->only(['name', 'email']);
            $roleData = $roleUser->role->only(['name', 'display_name']);

            // Log activity before removal
            $this->logActivity('role_removed', $roleUser->user, [
                'user_data' => $userData,
                'role_data' => $roleData
            ]);

            $roleUser->delete();
        });
    }

    /**
     * Get users with search and pagination
     */
    /**
     * Get users-role assignments with search and pagination for privilege management
     */
    public function getUsersWithFilters(?string $search = null, ?int $roleId = null, int $perPage = 10)
    {
        $query = \App\Models\RoleUser::with(['user', 'role', 'sidebars.sidebar']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('role', function ($rq) use ($search) {
                    $rq->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get raw users with search and pagination for identity management
     * Optimized to prevent timeout on large datasets
     */
    public function getUsersForRoot(?string $search = null, ?int $roleId = null, int $perPage = 10)
    {
        $query = User::select(['users.id', 'users.name', 'users.username', 'users.email', 'users.status', 'users.created_at', 'users.updated_at'])
            ->with(['roles' => function($query) {
                $query->select('roles.id', 'roles.name', 'roles.display_name');
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.username', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        if ($roleId) {
            // Use join instead of whereHas for better performance
            $query->join('role_user', 'users.id', '=', 'role_user.user_id')
                  ->where('role_user.role_id', $roleId)
                  ->distinct();
        }

        return $query->orderBy('users.created_at', 'desc')
                     ->paginate($perPage)
                     ->withQueryString();
    }

    /**
     * Get system-wide user statistics
     */
    public function getUserStats(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'new' => User::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }

    /**
     * Log user activities
     */
    private function logActivity(string $action, $model, array $details = []): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'details' => $details,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user, string $newPassword): void
    {
        DB::transaction(function () use ($user, $newPassword) {
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            $this->logActivity('password_reset', $user, [
                'email' => $user->email,
                'reset_by' => Auth::user()->name
            ]);
        });
    }

    /**
     * Check if username exists and return the current max ID in the system.
     */
    public function checkUsername(string $username): array
    {
        $exists = User::where('username', $username)->exists();
        $maxId = User::max('id') ?? 0;

        return [
            'exists' => $exists,
            'max_id' => $maxId
        ];
    }

    /**
     * Synchronize sidebars from a role to a specific user-role assignment
     */
    public function syncRoleSidebars(int $userId, int $roleId): void
    {
        // No-op: permissions are now resolved directly from roles in real-time
        return;
    }

    /**
     * Incrementally sync sidebars for ALL users belonging to a specific role.
     * This ONLY ADDS missing sidebars that are defined in the role template,
     * without deleting existing custom sidebars assigned to the user.
     * Uses chunking to prevent timeout on large datasets.
     */
    public function syncAllUsersToRoleSidebars(int $roleId): void
    {
        $role = Role::with('sidebars')->findOrFail($roleId);
        $roleSidebarIds = $role->sidebars->pluck('id')->toArray();

        if (empty($roleSidebarIds)) return;

        // Process in chunks to avoid memory/timeout issues
        \App\Models\RoleUser::where('role_id', $roleId)
            ->with(['sidebars' => function($query) {
                $query->select('sidebar_id', 'role_user_id');
            }])
            ->chunk(100, function ($roleUsers) use ($roleSidebarIds) {
                foreach ($roleUsers as $roleUser) {
                    $existingSidebarIds = $roleUser->sidebars->pluck('sidebar_id')->toArray();
                    $missingIds = array_diff($roleSidebarIds, $existingSidebarIds);

                    if (!empty($missingIds)) {
                        // Batch insert instead of individual creates
                        $inserts = collect($missingIds)->map(function ($sidebarId) use ($roleUser) {
                            return [
                                'role_user_id' => $roleUser->id,
                                'sidebar_id' => $sidebarId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        })->toArray();

                        \App\Models\UserRoleSidebar::insert($inserts);
                    }
                }
            });
    }
}
