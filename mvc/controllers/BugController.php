<?php

require_once __DIR__ . '/../Controller.php';
// handles bug actions 
// bug list, details, creation, updates
// checks login permissions, validates input calls bug move_uploaded_file
// includes bug project retrieval from project model
// Submission 1: intentional function shells. Database methods will use prepared statements.
class BugController extends Controller
{
    public function index()
    {
        $user = $this->session->get('user');
        if (!is_array($user)
            || !in_array((int) ($user['RoleID'] ?? 0), [1, 2, 3], true)) {
            echo 'Access denied.';
            return;
        }

        $canManage = in_array((int) $user['RoleID'], [1, 2], true);
        $projectId = $canManage
            ? filter_var($_GET['project_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : ($user['ProjectId'] ?? null);
        $this->renderBugPage($user, $canManage, $projectId ?: '');
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
            $projects = $project ? [$project] : [];
        }

        $filter = $_GET['filter'] ?? 'all';
        if (!in_array($filter, ['all', 'open', 'overdue'], true)) {
            $filter = 'all';
        }
        $bugs = $bug ? [] : (new Bug())->findAllVisibleToUser($user, [
            'project_id' => $projectId,
            'filter' => $filter,
        ]);
        $priorties = (new Bug())->getPriorities();
        $statuses = (new Bug())->getStatuses();
        $users = (new User())->findAll();
        $this->render($bug ? 'bugs/details' : 'bugs/index', [
            'currentUser' => $user,
            'canManage' => $canManage,
            'canAssign' => $canManage,
            'selectedProjectId' => $projectId,
            'projects' => $projects,
            'bugs' => $bugs,
            'filter' => $filter,
            'errors' => $errors,
            'bug' => $bug,
            'statuses' =>$statuses,
            'priorities' => $priorties,
            'users' => $users
        ]);
    }

    public function details()
    {
        $user = $this->authorizedUser();
        if (!$user) {
            return;
        }
        try {
            $bug = $this->loadVisibleBug($user, $_GET['id'] ?? '');
            if (!$bug) {
                return;
            }
            $this->renderBugPage($user, $this->canManage($user), $bug['ProjectId'], [], $bug);
        } catch (PDOException $e) {
            $this->databaseError($e);
        }
    }

    public function create()
    {
        $user = $this->session->get('user');
        if (!is_array($user)
            || !in_array((int) ($user['RoleID'] ?? 0), [1, 2, 3], true)
            || empty($user['Id'])) {
            echo 'Access denied.';
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            return;
        }

        $canAssign = in_array((int) $user['RoleID'], [1, 2], true);
        $positiveId = static function ($value) {
            return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        };
        $projectId = $positiveId($canAssign ? ($_POST['project_id'] ?? '') : ($user['ProjectId'] ?? ''));
        $errors = [];
        if (!$projectId) {
            $errors[] = 'Select a valid project.';
        }

        $data = [
            'projectId' => $projectId,
            'ownerId' => $user['Id'],
            'assignedToId' => null,
            'targetDate' => null,
            'priorityId' => null,
            'dateClosed' => null,
            'fixDescription' => null,
        ];
        foreach (['summary' => 250, 'description' => 2500] as $field => $limit) {
            $value = $_POST[$field] ?? '';
            $length = is_string($value) ? preg_match_all('/./us', $value) : false;
            if ($length === false || $length > $limit) {
                $errors[] = 'Enter a valid ' . $field . ' of at most ' . $limit . ' characters.';
            } else {
                $data[$field] = $value;
            }
        }
        if (!$errors) {
            require_once __DIR__ . '/../models/Bug.php';
            try {
                $model = new Bug();
                $this->validateReferences($model, $data, $errors);
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

        $this->renderBugPage($user, $canAssign, $projectId ?: '', $errors);
    }

    public function update()
    {
        $user = $this->authorizedUser();
        if (!$user) {
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            return;
        }
        try {
            $bug = $this->loadVisibleBug($user, $_POST['id'] ?? '');
            if (!$bug) {
                return;
            }
            $canManage = $this->canManage($user);
            if (!$canManage && (string) $bug['AssignedTo'] !== (string) $user['Id']) {
                echo 'Access denied.';
                return;
            }
            $data = [
                'assignedToId' => $bug['AssignedTo'],
                'priorityId' => $bug['PriorityId'],
                'targetDate' => $bug['TargetDate'],
                'fixDescription' => $bug['Fix'],
                'dateClosed' => $bug['ClosureDate'],
            ];
            $errors = [];
            if (array_key_exists('fix', $_POST)) {
                $fix = $_POST['fix'];
                $length = is_string($fix) ? preg_match_all('/./us', $fix) : false;
                if ($length === false || $length > 2500) {
                    $errors[] = 'Enter a fix description of at most 2,500 characters.';
                } else {
                    $data['fixDescription'] = $fix;
                }
            }
            if ($canManage) {
                foreach (['assigned_to' => 'assignedToId', 'priority_id' => 'priorityId'] as $field => $key) {
                    if (!array_key_exists($field, $_POST)) {
                        continue;
                    }
                    $value = $_POST[$field];
                    $data[$key] = $value === '' ? null : filter_var(
                        $value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]
                    );
                    if ($data[$key] === false) {
                        $errors[] = 'Select a valid ' . str_replace('_', ' ', $field) . '.';
                    }
                }
            }
            foreach (['closure_date' => 'dateClosed', 'target_date' => 'targetDate'] as $field => $key) {
                if (($field === 'target_date' && !$canManage) || !array_key_exists($field, $_POST)) {
                    continue;
                }
                $value = $_POST[$field];
                if ($value === '') {
                    $data[$key] = null;
                    continue;
                }
                $date = is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)
                    ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
                if (!$date || $date->format('Y-m-d') !== $value) {
                    $errors[] = 'Enter a valid ' . str_replace('_', ' ', $field) . '.';
                } else {
                    $data[$key] = $value;
                }
            }
            $model = new Bug();
            if (!$errors) {
                $this->validateReferences($model, $data, $errors);
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

    private function canManage($user)
    {
        return in_array((int) $user['RoleID'], [1, 2], true);
    }

    private function authorizedUser()
    {
        $user = $this->session->get('user');
        if (!is_array($user) || empty($user['Id'])
            || !in_array((int) ($user['RoleID'] ?? 0), [1, 2, 3], true)) {
            echo 'Access denied.';
            return false;
        }
        return $user;
    }

    private function loadVisibleBug($user, $id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        require_once __DIR__ . '/../models/Bug.php';
        $bug = $id ? (new Bug())->findById($id) : false;
        if (!$bug) {
            echo 'Bug not found.';
            return false;
        }
        if (!$this->canManage($user)
            && (string) $bug['ProjectId'] !== (string) ($user['ProjectId'] ?? '')) {
            echo 'Access denied.';
            return false;
        }
        return $bug;
    }

    private function validateReferences($model, $data, &$errors)
    {
        foreach (['projectId' => 'project', 'assignedToId' => 'user_details', 'priorityId' => 'priority'] as $key => $table) {
            if (isset($data[$key]) && !$model->referenceExists($table, $data[$key])) {
                $errors[] = 'Select a valid ' . ['projectId' => 'project', 'assignedToId' => 'assignee', 'priorityId' => 'priority'][$key] . '.';
            }
        }
    }

    private function databaseError($e)
    {
        error_log($e->getMessage());
        echo 'Unable to load or save bug.';
    }

}


