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
        $errors = Utils::validateLogin($_POST, $data);
        $identifier = $data['identifier'];
        $password = $data['password'];

        if ($errors) {
            $this->render('auth/login', [
                'errors' => $errors,
                'identifier' => $identifier,
            ]);
            return;
        }

        require_once __DIR__ . '/../models/User.php';
        $user = (new User())->findByUsername($identifier);

        if (!Authorization::validCredentials($user, $password)) {
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
        if (Authorization::canManageProjects($user)) {
            header('Location: ?admin');
        } else {
            header('Location: ?bugs');
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

