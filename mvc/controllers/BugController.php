<?php

require_once __DIR__ . '/../Controller.php';
// Handles bug listing, details, creation, and updates with permission and input checks.
class BugController extends Controller
{
    public function index()
    {
        $user = $this->session->get('user');
        if (!Authorization::canAccessBugs($user)) {
            echo 'Access denied.';
            return;
        }

        $canManage = Authorization::canAssignBugs($user);
        if ($canManage) {
            if (isset($_GET['project_id'])) {
                $projectInput = $_GET['project_id'];
            } else {
                $projectInput = '';
            }
            $projectId = Utils::parseId($projectInput);
        } else {
            if (isset($user['ProjectId'])) {
                $projectId = $user['ProjectId'];
            } else {
                $projectId = null;
            }
        }
        if (!$projectId) {
            $projectId = '';
        }
        $this->renderBugPage($user, $canManage, $projectId);
    }

    private function renderBugPage($user, $canManage, $projectId, $errors = [], $bug = null)
    {
        require_once __DIR__ . '/../models/Project.php';
        require_once __DIR__ . '/../models/Bug.php';
        require_once __DIR__ . '/../models/User.php';
        $projectModel = new Project();
        if ($canManage) {
            $projects = $projectModel->findAll();
        } else {
            $project = $projectModel->findForUser($user['Id']);
            if ($project) {
                $projects = [$project];
            } else {
                $projects = [];
            }
        }

        $filter = Utils::bugFilter(Utils::value($_GET, 'filter', 'all'));
        $bugs = [];
        $view = 'bugs/details';
        if (!$bug) {
            $view = 'bugs/index';
            $bugs = (new Bug())->findAllVisibleToUser($user, [
                'project_id' => $projectId,
                'filter' => $filter,
            ]);
        }
        $priorities = (new Bug())->getPriorities();
        $statuses = (new Bug())->getStatuses();
        $users = (new User())->findAll();
        $this->render($view, [
            'currentUser' => $user,
            'canManage' => $canManage,
            'canAssign' => $canManage,
            'selectedProjectId' => $projectId,
            'projects' => $projects,
            'bugs' => $bugs,
            'filter' => $filter,
            'errors' => $errors,
            'bug' => $bug,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'users' => $users
        ]);
    }

    public function details()
    {
        $user = Authorization::requireLogin();
        if (!$user) {
            return;
        }
        try {
            $idInput = '';
            if (isset($_GET['id'])) {
                $idInput = $_GET['id'];
            }
            $bug = $this->loadVisibleBug($user, $idInput);
            if (!$bug) {
                return;
            }
            $this->renderBugPage($user, Authorization::canAssignBugs($user), $bug['ProjectId'], [], $bug);
        } catch (PDOException $e) {
            $this->databaseError($e);
        }
    }

    public function create()
    {
        $user = $this->session->get('user');
        if (!Authorization::isLoggedIn($user) || !Authorization::canAccessBugs($user)) {
            echo 'Access denied.';
            return;
        }
        if (!Utils::isPost()) {
            header('Allow: POST');
            return;
        }

        $canAssign = Authorization::canAssignBugs($user);
        $errors = Utils::validateBugCreate($_POST, $user, $data);
        $projectId = $data['projectId'];
        if (!$errors) {
            require_once __DIR__ . '/../models/Bug.php';
            try {
                $model = new Bug();
                Utils::validateReferences($model, $data, $errors);
                if (!$errors && $model->create($data)) {
                    header('Location: ?bugs');
                    return;
                }
                if (!$errors) {
                    $errors[] = 'Unable to create bug.';
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to create bug. Check the project and assignment selections.';
            }
        }

        if (!$projectId) {
            $projectId = '';
        }
        $this->renderBugPage($user, $canAssign, $projectId, $errors);
    }

    public function update()
    {
        $user = Authorization::requireLogin();
        if (!$user) {
            return;
        }
        if (!Utils::isPost()) {
            header('Allow: POST');
            return;
        }
        try {
            $idInput = '';
            if (isset($_POST['id'])) {
                $idInput = $_POST['id'];
            }
            $bug = $this->loadVisibleBug($user, $idInput);
            if (!$bug) {
                return;
            }
            $canManage = Authorization::canAssignBugs($user);
            if (!Authorization::canUpdateBug($user, $bug)) {
                echo 'Access denied.';
                return;
            }
            $errors = Utils::validateBugUpdate($_POST, $bug, $canManage, $data);
            $model = new Bug();
            if (!$errors) {
                Utils::validateReferences($model, $data, $errors);
            }
            if (!$errors) {
                if ($model->update($bug['Id'], $data)) {
                    header('Location: ?bug-details&id=' . rawurlencode((string) $bug['Id']));
                    return;
                }
                $errors[] = 'Unable to update bug.';
            }
            foreach (['assignedToId' => 'AssignedTo', 'priorityId' => 'PriorityId', 'targetDate' => 'TargetDate',
                      'fixDescription' => 'Fix', 'dateClosed' => 'ClosureDate'] as $key => $alias) {
                if ($data[$key] !== false) {
                    $bug[$alias] = $data[$key];
                }
            }
            $this->renderBugPage($user, $canManage, $bug['ProjectId'], $errors, $bug);
        } catch (PDOException $e) {
            $this->databaseError($e);
        }
    }

    private function loadVisibleBug($user, $id)
    {
        $id = Utils::parseId($id);
        require_once __DIR__ . '/../models/Bug.php';
        if ($id) {
            $bug = (new Bug())->findById($id);
        } else {
            $bug = false;
        }
        if (!$bug) {
            echo 'Bug not found.';
            return false;
        }
        if (!Authorization::canViewBug($user, $bug)) {
            echo 'Access denied.';
            return false;
        }
        return $bug;
    }

    private function databaseError($e)
    {
        error_log($e->getMessage());
        echo 'Unable to load or save bug.';
    }

}


