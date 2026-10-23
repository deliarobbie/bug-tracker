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

        $this->renderAdmin();
    }

    public function createUser()
    {
        $user = $this->session->get('user');
        if (!is_array($user) || (int) ($user['RoleID'] ?? 0) !== 1) //admin 
        {
            http_response_code(403);
            echo 'Access denied.';
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $name = is_string($name) ? trim($name) : '';
        $username = is_string($username) ? trim($username) : '';
        $roleId = filter_var($_POST['role_id'] ?? '', FILTER_VALIDATE_INT);
        $projectInput = $_POST['project_id'] ?? '';
        $projectId = $projectInput === '' ? null : filter_var(
            $projectInput,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $errors = [];

        if ($name === '' || $username === '') {
            $errors[] = 'Enter a name and username.';
        }
        if (!is_string($password) || $password === ''
            || strlen($password) > 72 || strpos($password, "\0") !== false) {
            $errors[] = 'Enter a password of 1 to 72 bytes without null characters.';
        }
        if (!in_array($roleId, [1, 2, 3], true)) {
            $errors[] = 'Select a valid role.';
        }
        if ($projectId === false) {
            $errors[] = 'Select a valid project.';
        } elseif ($projectId !== null && $roleId !== 3) {
            $errors[] = 'Only Users may have a project assignment.';
        }

        if (!$errors) {
            require_once __DIR__ . '/../models/User.php';
            try {
                $userModel = new User();
                if ($userModel->findByUsername($username)) {
                    $errors[] = 'That username is already in use.';
                } elseif ($userModel->create([
                    'name' => $name,
                    'username' => $username,
                    'password' => $password,
                    'role_id' => $roleId,
                    'project_id' => $projectId,
                ])) {
                    header('Location: ?admin');
                    return;
                } else {
                    $errors[] = 'Unable to create user.';
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to create user. Check the username and project assignment.';
            }
        }

        http_response_code(422);
        $this->renderAdmin($errors);
    }

    private function renderAdmin($errors = [])
    {
        require_once __DIR__ . '/../models/User.php';
        $this->render('admin/index', [
            'currentUser' => $this->session->get('user'),
            'roles' => (new User())->getRoles(),
            'allUsers' => (new User())->findAll(),
            'errors' => $errors,
        ]);
    }

    public function deleteUser()
    {
        $user = $this->session->get('user');
        if (!is_array($user) || (int) ($user['RoleID'] ?? 0) !== 1) {
            http_response_code(403);
            echo 'Access denied.';
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $id = filter_var(
            $_POST['id'] ?? '',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $errors = [];

        if ($id === false) {
            $errors[] = 'Select a valid user.';
        } else {
            require_once __DIR__ . '/../models/User.php';
            try {
                if ((new User())->deleteAndClearAssignments($id)) {
                    header('Location: ?admin'); // refresh
                    return;
                }
                $errors[] = 'Unable to delete user.';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to delete user.';
            }
        }

        http_response_code(422);
        $this->renderAdmin($errors);
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

