<?php
namespace App;

/**
 * Authorization - Role-Based Access Control
 * 
 * Manages user roles, permissions, and access control
 * Implements hierarchical role system with permission inheritance
 */
class Authorization
{
    private $database;
    private $config;
    private $currentUserId;
    private $currentUserRole;
    private $permissionCache = [];

    public function __construct(Database $database, $userId = null, $userRole = null)
    {
        $this->database = $database;
        $this->config = include __DIR__ . '/../config/security.php';
        $this->currentUserId = $userId;
        $this->currentUserRole = $userRole;
    }

    /**
     * Set current user context
     * 
     * @param int $userId User ID
     * @param string|null $role User role (will be fetched if not provided)
     * @return $this
     */
    public function setUser($userId, $role = null)
    {
        $this->currentUserId = $userId;
        
        if ($role === null) {
            try {
                $user = $this->database->findOne('users', ['id' => $userId]);
                $this->currentUserRole = $user['role'] ?? 'member';
            } catch (Exception $e) {
                $this->currentUserRole = 'guest';
            }
        } else {
            $this->currentUserRole = $role;
        }

        return $this;
    }

    /**
     * Check if user has permission
     * 
     * @param string $permission Permission to check
     * @return bool True if user has permission
     */
    public function can($permission)
    {
        if (!$this->currentUserRole) {
            return false;
        }

        // Check cache first
        $cacheKey = $this->currentUserRole . '_' . $permission;
        if (isset($this->permissionCache[$cacheKey])) {
            return $this->permissionCache[$cacheKey];
        }

        try {
            // Get role permissions
            $rolePermissions = $this->getRolePermissions($this->currentUserRole);
            $hasPermission = in_array($permission, $rolePermissions);

            // Check user-specific permissions
            if (!$hasPermission && $this->currentUserId) {
                $userPermissions = $this->getUserPermissions($this->currentUserId);
                $hasPermission = in_array($permission, $userPermissions);
            }

            // Cache result
            $this->permissionCache[$cacheKey] = $hasPermission;

            return $hasPermission;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if user cannot perform action
     * 
     * @param string $permission Permission to check
     * @return bool True if user cannot perform action
     */
    public function cannot($permission)
    {
        return !$this->can($permission);
    }

    /**
     * Check multiple permissions (OR logic)
     * 
     * @param array $permissions Permissions to check
     * @return bool True if user has any permission
     */
    public function canAny($permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check multiple permissions (AND logic)
     * 
     * @param array $permissions Permissions to check
     * @return bool True if user has all permissions
     */
    public function canAll($permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has role
     * 
     * @param string $role Role to check
     * @return bool True if user has role
     */
    public function hasRole($role)
    {
        if (!$this->currentUserRole) {
            return false;
        }

        return $this->currentUserRole === $role;
    }

    /**
     * Check if user has any of the roles
     * 
     * @param array $roles Roles to check
     * @return bool True if user has any role
     */
    public function hasAnyRole($roles)
    {
        return in_array($this->currentUserRole, $roles);
    }

    /**
     * Get current user role
     * 
     * @return string|null Current user role
     */
    public function getRole()
    {
        return $this->currentUserRole;
    }

    /**
     * Get role permissions
     * 
     * @param string $role Role name
     * @return array Array of permission names
     */
    public function getRolePermissions($role)
    {
        try {
            $roleRecord = $this->database->findOne('roles', ['name' => $role]);
            
            if (!$roleRecord) {
                return [];
            }

            $permissions = $this->database->findMany(
                'role_permissions',
                ['role_id' => $roleRecord['id']]
            );

            $permissionNames = [];
            foreach ($permissions as $permission) {
                $permRecord = $this->database->findOne('permissions', ['id' => $permission['permission_id']]);
                if ($permRecord) {
                    $permissionNames[] = $permRecord['name'];
                }
            }

            return $permissionNames;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get user-specific permissions
     * 
     * Permissions granted directly to user, outside of their role
     * 
     * @param int $userId User ID
     * @return array Array of permission names
     */
    public function getUserPermissions($userId)
    {
        try {
            $permissions = $this->database->findMany(
                'user_permissions',
                ['user_id' => $userId]
            );

            $permissionNames = [];
            foreach ($permissions as $permission) {
                $permRecord = $this->database->findOne('permissions', ['id' => $permission['permission_id']]);
                if ($permRecord) {
                    $permissionNames[] = $permRecord['name'];
                }
            }

            return $permissionNames;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Grant permission to role
     * 
     * @param string $role Role name
     * @param string $permission Permission name
     * @return bool True on success
     */
    public function grantPermissionToRole($role, $permission)
    {
        try {
            $roleRecord = $this->database->findOne('roles', ['name' => $role]);
            $permRecord = $this->database->findOne('permissions', ['name' => $permission]);

            if (!$roleRecord || !$permRecord) {
                return false;
            }

            // Check if already granted
            $exists = $this->database->findOne('role_permissions', [
                'role_id' => $roleRecord['id'],
                'permission_id' => $permRecord['id']
            ]);

            if ($exists) {
                return true;
            }

            $this->database->insert('role_permissions', [
                'role_id' => $roleRecord['id'],
                'permission_id' => $permRecord['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Clear cache
            $this->permissionCache = [];

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Revoke permission from role
     * 
     * @param string $role Role name
     * @param string $permission Permission name
     * @return bool True on success
     */
    public function revokePermissionFromRole($role, $permission)
    {
        try {
            $roleRecord = $this->database->findOne('roles', ['name' => $role]);
            $permRecord = $this->database->findOne('permissions', ['name' => $permission]);

            if (!$roleRecord || !$permRecord) {
                return false;
            }

            $this->database->delete('role_permissions', [
                'role_id' => $roleRecord['id'],
                'permission_id' => $permRecord['id']
            ]);

            // Clear cache
            $this->permissionCache = [];

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Grant permission directly to user
     * 
     * @param int $userId User ID
     * @param string $permission Permission name
     * @return bool True on success
     */
    public function grantPermissionToUser($userId, $permission)
    {
        try {
            $permRecord = $this->database->findOne('permissions', ['name' => $permission]);

            if (!$permRecord) {
                return false;
            }

            // Check if already granted
            $exists = $this->database->findOne('user_permissions', [
                'user_id' => $userId,
                'permission_id' => $permRecord['id']
            ]);

            if ($exists) {
                return true;
            }

            $this->database->insert('user_permissions', [
                'user_id' => $userId,
                'permission_id' => $permRecord['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Clear cache
            $this->permissionCache = [];

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Revoke permission from user
     * 
     * @param int $userId User ID
     * @param string $permission Permission name
     * @return bool True on success
     */
    public function revokePermissionFromUser($userId, $permission)
    {
        try {
            $permRecord = $this->database->findOne('permissions', ['name' => $permission]);

            if (!$permRecord) {
                return false;
            }

            $this->database->delete('user_permissions', [
                'user_id' => $userId,
                'permission_id' => $permRecord['id']
            ]);

            // Clear cache
            $this->permissionCache = [];

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Assign role to user
     * 
     * @param int $userId User ID
     * @param string $role Role name
     * @return bool True on success
     */
    public function assignRoleToUser($userId, $role)
    {
        try {
            $roleRecord = $this->database->findOne('roles', ['name' => $role]);

            if (!$roleRecord) {
                return false;
            }

            $this->database->update('users',
                ['role' => $role],
                ['id' => $userId]
            );

            // Clear cache
            $this->permissionCache = [];

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create new role
     * 
     * @param string $name Role name
     * @param string $description Role description
     * @return bool True on success
     */
    public function createRole($name, $description = '')
    {
        try {
            // Check if role exists
            $exists = $this->database->findOne('roles', ['name' => $name]);
            if ($exists) {
                return false;
            }

            $this->database->insert('roles', [
                'name' => $name,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create new permission
     * 
     * @param string $name Permission name
     * @param string $description Permission description
     * @param string $category Permission category/module
     * @return bool True on success
     */
    public function createPermission($name, $description = '', $category = '')
    {
        try {
            // Check if permission exists
            $exists = $this->database->findOne('permissions', ['name' => $name]);
            if ($exists) {
                return false;
            }

            $this->database->insert('permissions', [
                'name' => $name,
                'description' => $description,
                'category' => $category,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all roles
     * 
     * @return array Array of role records
     */
    public function getAllRoles()
    {
        try {
            return $this->database->findMany('roles', []);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get all permissions
     * 
     * @param string|null $category Filter by category
     * @return array Array of permission records
     */
    public function getAllPermissions($category = null)
    {
        try {
            if ($category) {
                return $this->database->findMany('permissions', ['category' => $category]);
            }

            return $this->database->findMany('permissions', []);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get permissions grouped by category
     * 
     * @return array Array of [category => [permissions]]
     */
    public function getPermissionsByCategory()
    {
        try {
            $permissions = $this->getAllPermissions();
            $grouped = [];

            foreach ($permissions as $permission) {
                $category = $permission['category'] ?? 'General';
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                $grouped[$category][] = $permission;
            }

            return $grouped;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Check access to resource
     * 
     * @param string $resource Resource name/type
     * @param string $action Action to perform (view, edit, delete)
     * @param int|null $resourceOwnerId Owner ID of resource
     * @return bool True if access granted
     */
    public function canAccessResource($resource, $action = 'view', $resourceOwnerId = null)
    {
        // Build permission name: resource.action (e.g., 'booking.view', 'user.edit')
        $permission = $resource . '.' . $action;

        // Check if user has general permission
        if ($this->can($permission)) {
            return true;
        }

        // Check if user is resource owner and has owner permission
        if ($resourceOwnerId && $resourceOwnerId == $this->currentUserId) {
            $ownerPermission = $resource . '.view_own';
            if ($this->can($ownerPermission)) {
                return true;
            }

            $ownerPermission = $resource . '.' . $action . '_own';
            if ($this->can($ownerPermission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get authorization report
     * 
     * @return array Authorization details
     */
    public function getReport()
    {
        return [
            'user_id' => $this->currentUserId,
            'role' => $this->currentUserRole,
            'role_permissions' => $this->currentUserRole ? 
                $this->getRolePermissions($this->currentUserRole) : [],
            'user_permissions' => $this->currentUserId ? 
                $this->getUserPermissions($this->currentUserId) : [],
            'total_permissions' => ($this->currentUserRole ? 
                count($this->getRolePermissions($this->currentUserRole)) : 0) + 
                ($this->currentUserId ? 
                count($this->getUserPermissions($this->currentUserId)) : 0),
        ];
    }

    /**
     * Clear permission cache
     * 
     * @return $this
     */
    public function clearCache()
    {
        $this->permissionCache = [];
        return $this;
    }

    /**
     * Delete role
     * 
     * @param string $role Role name
     * @return bool True on success
     */
    public function deleteRole($role)
    {
        try {
            $roleRecord = $this->database->findOne('roles', ['name' => $role]);

            if (!$roleRecord) {
                return false;
            }

            // Remove role permissions
            $this->database->delete('role_permissions', ['role_id' => $roleRecord['id']]);

            // Delete role
            $this->database->delete('roles', ['id' => $roleRecord['id']]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete permission
     * 
     * @param string $permission Permission name
     * @return bool True on success
     */
    public function deletePermission($permission)
    {
        try {
            $permRecord = $this->database->findOne('permissions', ['name' => $permission]);

            if (!$permRecord) {
                return false;
            }

            // Remove role permissions
            $this->database->delete('role_permissions', ['permission_id' => $permRecord['id']]);

            // Remove user permissions
            $this->database->delete('user_permissions', ['permission_id' => $permRecord['id']]);

            // Delete permission
            $this->database->delete('permissions', ['id' => $permRecord['id']]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
