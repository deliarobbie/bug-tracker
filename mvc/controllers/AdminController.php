<?php

require_once __DIR__ . '/../Controller.php';

class AdminController extends Controller
{
    public function index()
    {
        $user = $this->session->get('user');

        if (!is_array($user)
            || !in_array((int) ($user['RoleID'] ?? 0), [1, 2], true)) {
            http_response_code(403);
            echo 'Access denied.';
            return;
        }

        $this->render('admin/index');
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

