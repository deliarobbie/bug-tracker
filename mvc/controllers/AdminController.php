<?php

require_once __DIR__ . '/../Controller.php';

class AdminController extends Controller
{
    public function index()
    {
        $user = $this->session->get('user');

        if (!Authorization::canManageProjects($user)) {
            echo 'Access denied.';
            return;
        }

        $this->renderAdmin();
    }

    public function createUser()
    {
        $user = $this->session->get('user');
        if (!Authorization::canManageUsers($user)) {
            echo 'Access denied.';
            return;
        }

        // if (!Utils::isPost()) {
        //     header('Allow: POST');
        //     return;
        // }

        $errors = Utils::validateUser($_POST, $data);

        if (!$errors) {
            require_once __DIR__ . '/../models/User.php';
            try {
                $userModel = new User();
                $errors = Utils::validateUserReferences($userModel, $data);
                if (!$errors && $userModel->create($data)) {
                    header('Location: ?admin');
                    return;
                } elseif (!$errors) {
                    $errors[] = 'Unable to create user.';
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to create user. Check the username and project assignment.';
            }
        }

        $this->renderAdmin($errors);
    }

    public function assignProject()
    {
        $user = $this->session->get('user');
        if (!Authorization::canManageUsers($user)) {
            echo 'Access denied.';
            return;
        }
        if (!Utils::isPost()) {
            header('Allow: POST');
            return;
        }

        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Project.php';
        try {
            $userModel = new User();
            $errors = Utils::validateProjectAssignment($_POST, $userModel, new Project(), $data);
            if (!$errors) {
                if ($userModel->assignProject($data['id'], $data['project_id'])) {
                    header('Location: ?admin');
                    return;
                }
                $errors[] = 'Unable to update project assignment.';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors = ['Unable to update project assignment.'];
        }
        $this->renderAdmin($errors);
    }

    private function renderAdmin($errors = [])
    {
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Project.php';
        $this->render('admin/index', [
            'currentUser' => $this->session->get('user'),
            'roles' => (new User())->getRoles(),
            'allUsers' => (new User())->findAll(),
            'errors' => $errors,
            'projects' => (new Project())->findAll()
        ]);
    }

    public function deleteUser()
    {
        $user = $this->session->get('user');
        if (!Authorization::canManageUsers($user)) {
            echo 'Access denied.';
            return;
        }

        if (!Utils::isPost()) {
            header('Allow: POST');
            return;
        }

        $errors = Utils::validateId(Utils::value($_POST, 'id'), 'user', $id);
        if (!$errors) {
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

        $this->renderAdmin($errors);
    }

    public function createProject()
    {
        $user = $this->session->get('user');
        if (!Authorization::canManageProjects($user)) {
            echo 'Access denied.';
            return;
        }

        if (!Utils::isPost()) {
            header('Allow: POST');
            return;
        }

        $errors = Utils::validateProject($_POST, $data);
        if (!$errors) {
            require_once __DIR__ . '/../models/Project.php';
            try {
                if ((new Project())->create($data)) {
                    header('Location: ?admin');
                    return;
                }
                $errors[] = 'Unable to create project.';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to create project.';
            }
        }

        $this->renderAdmin($errors);
    }

    public function updateProject()
    {
        $user = $this->session->get('user');
        if (!Authorization::canManageProjects($user)) {
            echo 'Access denied.';
            return;
        }

        if (!Utils::isPost()) {
            header('Allow: POST');
            return;
        }

        $errors = Utils::validateProject($_POST, $data, true);

        if (!$errors) {
            require_once __DIR__ . '/../models/Project.php';
            try {
                $projectModel = new Project();
                $errors = Utils::validateProjectReference($projectModel, $data['Id']);
                if (!$errors && $projectModel->update($data['Id'], $data)) {
                    header('Location: ?admin');
                    return;
                } elseif (!$errors) {
                    $errors[] = 'Unable to update project.';
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to update project.';
            }
        }

        $this->renderAdmin($errors);
    }

}

