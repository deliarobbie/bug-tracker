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
        return is_array($user) && isset($user['RoleID']) && in_array((int) $user['RoleID'], $roles, true);
    }

    public static function canAccessBugs($user)
    {
        return self::hasRole($user, [1, 2, 3]);
    }

    public static function requireLogin()
    {
        $user = Session::getInstance()->get('user');
        if (!self::isLoggedIn($user) || !self::canAccessBugs($user)) {
            echo 'Access denied.';
            return false;
        }
        return $user;
    }

    public static function canManageUsers($user)
    {
        return self::hasRole($user, [1]);
    }

    public static function canManageProjects($user)
    {
        return self::hasRole($user, [1, 2]);
    }

    public static function canAssignBugs($user)
    {
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
        if (!self::canViewBug($user, $bug)) {
            return false;
        }
        if (self::canAssignBugs($user)) {
            return true;
        }
        return self::isLoggedIn($user) && isset($bug['assignedToId'])
            && (string) $bug['assignedToId'] === (string) $user['Id'];
    }

    public static function validCredentials($user, $password)
    {
        return is_array($user) && isset($user['Password']) && is_string($password) && password_verify($password, $user['Password']);
    }
}
