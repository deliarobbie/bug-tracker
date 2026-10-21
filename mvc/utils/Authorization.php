<?php

// Submission 1: intentional function shells. Database methods will use prepared statements.
class Authorization
{
    public static function requireLogin()
    {
        // TODO: Reject unauthenticated access.
    }

    public static function canManageUsers($user)
    {
        // TODO: Check Admin role.
    }

    public static function canManageProjects($user)
    {
        // TODO: Check Admin or Manager role.
    }

    public static function canViewBug($user, $bug)
    {
        // TODO: Admins/Managers see all; Users only their project.
    }

    public static function canUpdateBug($user, $bug)
    {
        // TODO: Admins/Managers update all; Users only assigned bugs.
    }

    public static function canAssignBugs($user)
    {
        // TODO: Check role assignment permission.
    }

}

