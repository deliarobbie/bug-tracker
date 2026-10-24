<?php

require_once __DIR__ . '/Session.php';

class Authorization
{
    public static function isLoggedIn($user)
    {
        // 'user' is a $_session variable that gets created when user gets logged in (Session.php)
        return is_array($user) && !empty($user['Id']);
    }

    public static function hasRole($user, $roles)
    {
        // check if user roleid in roles e.g. [1,2]
        return is_array($user) && isset($user['RoleID']) && in_array((int) $user['RoleID'], $roles);
    }

    public static function canAccessBugs($user)
    {
        // all user types
        return self::hasRole($user, [1, 2, 3]);
    }

    public static function requireLogin()
    {
        // get user session var (confirms login)
        $user = Session::getInstance()->get('user');
        if (!self::isLoggedIn($user) || !self::canAccessBugs($user)) {
            echo 'Access denied.';
            return false;
        }
        return $user;
    }

    public static function canManageUsers($user)
    {
        // is admin
        return self::hasRole($user, [1]);
    }

    public static function canManageProjects($user)
    {
        // is admin or manager
        return self::hasRole($user, [1, 2]);
    }

    public static function canAssignBugs($user)
    {
        // is admin or user
        return self::canManageProjects($user);
    }

    public static function canViewBug($user, $bug)
    {
        if (!self::canAccessBugs($user) || !is_array($bug)) {
            return false;
        }
        if (self::canManageProjects($user)) {
            return true;
        }
        return isset($user['ProjectId'], $bug['projectId'])
            && (string) $user['ProjectId'] === (string) $bug['projectId'];
    }

    public static function canUpdateBug($user, $bug)
    {
        // user must be allowed to view the bug before updating it
        if (!self::canViewBug($user, $bug)) {
            return false;
        }
        // admins and managers can update any bug they can view
        if (self::canAssignBugs($user)) {
            return true;
        }
        // users must be logged in and assigned to this bug
        return self::isLoggedIn($user) && isset($bug['assignedToId'])
            && (string) $bug['assignedToId'] === (string) $user['Id'];
    }

    public static function validCredentials($user, $password)
    {
        // validates password is set, is a string, and matches hash
        return is_array($user) && isset($user['Password']) && is_string($password) && password_verify($password, $user['Password']);
    }
}
