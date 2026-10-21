<?php

require_once __DIR__ . '/../Controller.php';

// Submission 1: intentional function shells. Database methods will use prepared statements.
class AuthController extends Controller
{
    public function loginForm()
    {
        // TODO: Display the login view.
    }

    public function login()
    {
        // TODO: Validate credentials, verify the password hash, regenerate the session ID, and store identity.
    }

    public function logout()
    {
        // TODO: Destroy the session and redirect to login.
    }

}

