<?php

require_once __DIR__ . '/../Controller.php';

// Submission 1: intentional function shells. Database methods will use prepared statements.
class AuthController extends Controller
{
    public function loginForm()
    {
        $this->render('auth/login');
    }

    public function login()
    {
        $identifier = $_POST['identifier'] ?? '';
        $password = $_POST['password'] ?? '';
        // var_dump(password_hash($password, PASSWORD_DEFAULT));
        // exit;
        $identifier = is_string($identifier) ? trim($identifier) : '';

        if ($identifier === '' || !is_string($password) || $password === '') {
            $this->render('auth/login', [
                'errors' => ['Enter your login identifier and password.'],
                'identifier' => $identifier,
            ]);
            return;
        }

        require_once __DIR__ . '/../models/User.php';
        $user = (new User())->findByUsername($identifier);

        if (!$user || !password_verify($password, $user['Password'])) {
            $this->render('auth/login', [
                'errors' => ['Invalid login identifier or password.'],
                'identifier' => $identifier,
            ]);
            return;
        }

        if (!session_regenerate_id(true)) {
            throw new RuntimeException('Unable to regenerate the session ID.');
        }
        
        unset($user['Password']);
        $this->session->set('user', $user);
        switch ((int) $user['RoleID']) {
            case 1: // Admin
            case 2: // Manager
                header('Location: ?admin');
                break;

            default: // User
                header('Location: ?bugs');
                break;
        }
        exit;
    }

    public function logout()
    {
        $this->session->destroy();

        header('Location: ?login');
        exit;
    }
}

