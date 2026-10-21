<?php

require_once __DIR__ . '/../Controller.php';

class AdminController extends Controller
{
    public function index()
    {
        // TODO: Require Admin/Manager access; load projects and Admin-only user management data.
    }

    public function createUser()
    {
        // TODO: Require Admin; validate, hash password, and create user. Only Users may have one project assignment.
    }

    public function deleteUser()
    {
        // TODO: Require Admin; clear assignments and delete user while preserving bugs/projects.
    }

    public function createProject()
    {
        // TODO: Require Admin/Manager; validate and create project.
    }

    public function updateProject()
    {
        // TODO: Require Admin/Manager; validate and update project.
    }

}

