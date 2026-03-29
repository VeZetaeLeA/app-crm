<?php
namespace Core;

/**
 * Authentication Helper Class
 */
class Auth
{
    // Roles Constants
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_STAFF = 'staff';
    const ROLE_CLIENT = 'client';

    /**
     * Hashing centralizado soportando Argon2id
     */
    public static function hashPassword(string $password): string
    {
        $algoName = Config::get('security.hash_algo', 'argon2id');
        $algo = ($algoName === 'argon2id' && defined('PASSWORD_ARGON2ID')) ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;

        $options = [];
        if ($algo === \PASSWORD_ARGON2ID) {
            $options = ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 3];
        }

        return password_hash($password, $algo, $options);
    }

    public static function user()
    {
        return Session::get('user');
    }

    public static function check()
    {
        return Session::has('user');
    }

    public static function role()
    {
        $user = self::user();
        return $user ? $user['role'] : null;
    }

    /**
     * Check if user has a specific permission
     */
    public static function can(string $permission): bool
    {
        $role = self::role();
        if (!$role)
            return false;

        // Super Admin has all permissions
        if ($role === self::ROLE_SUPER_ADMIN)
            return true;

        $rbacMode = Config::get('intelligence.rbac_mode', 'classic');
        if ($rbacMode === 'classic') {
            return self::checkClassicPermission($role, $permission);
        }

        return self::checkGranularPermission($role, $permission);
    }

    private static function checkClassicPermission(string $role, string $permission): bool
    {
        // Simple mapping for classic mode
        $map = [
            self::ROLE_ADMIN => ['manage_all', 'view_reports', 'manage_users'],
            self::ROLE_STAFF => ['manage_tickets', 'view_projects'],
            self::ROLE_CLIENT => ['view_own_tickets', 'view_own_invoices']
        ];

        $permissions = $map[$role] ?? [];
        return in_array($permission, $permissions) || in_array('manage_all', $permissions);
    }

    private static function checkGranularPermission(string $role, string $permission): bool
    {
        // Advanced mapping for Evolution 2.0
        $map = [
            self::ROLE_ADMIN => [
                'manage_leads',
                'manage_projects',
                'manage_finance',
                'manage_services',
                'manage_cms',
                'view_reports',
                'manage_tickets',
                'manage_users',
                'view_logs'
            ],
            self::ROLE_STAFF => [
                'manage_tickets',
                'view_projects',
                'manage_chat'
            ],
            self::ROLE_CLIENT => [
                'view_own_projects',
                'view_own_invoices',
                'view_own_tickets',
                'create_ticket'
            ]
        ];

        $permissions = $map[$role] ?? [];
        return in_array($permission, $permissions);
    }

    public static function isSuperAdmin()
    {
        return self::role() === self::ROLE_SUPER_ADMIN;
    }

    public static function isAdmin()
    {
        return in_array(self::role(), [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public static function isStaff()
    {
        return self::role() === self::ROLE_STAFF;
    }

    public static function isClient()
    {
        return self::role() === self::ROLE_CLIENT;
    }
}
