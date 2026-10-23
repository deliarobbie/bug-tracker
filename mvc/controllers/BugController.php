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
        $this->render('bugs/index');
    }

    public function details()
    {
        // TODO: Check visibility and load bug details for the bug view.
    }

    public function create()
    {
        $user = $this->session->get('user');
        if (!is_array($user)
            || !in_array((int) ($user['RoleID'] ?? 0), [1, 2, 3], true)
            || empty($user['Id'])) {
            http_response_code(403);
            echo 'Access denied.';
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $canAssign = in_array((int) $user['RoleID'], [1, 2], true);
        $positiveId = static function ($value) {
            return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        };
        $projectId = $positiveId($canAssign ? ($_POST['project_id'] ?? '') : ($user['ProjectId'] ?? ''));
        $summary = is_string($_POST['title'] ?? null) ? trim($_POST['title']) : '';
        $description = is_string($_POST['description'] ?? null) ? trim($_POST['description']) : '';
        $errors = [];
        if (!$projectId) {
            $errors[] = 'Select a valid project.';
        }
        if ($summary === '' || $description === '') {
            $errors[] = 'Enter a title and description.';
        }

        $data = [
            'projectId' => $projectId,
            'ownerId' => $user['Id'],
            'summary' => $summary,
            'description' => $description,
            'assignedToId' => null,
            'targetDate' => null,
            'statusId' => null,
            'priorityId' => null,
            'dateClosed' => null,
            'fixDescription' => null,
        ];
        if ($canAssign) {
            foreach (['assigned_to' => 'assignedToId', 'status_id' => 'statusId', 'priority_id' => 'priorityId'] as $field => $key) {
                $value = $_POST[$field] ?? '';
                if ($value !== '') {
                    $data[$key] = $positiveId($value);
                    if ($data[$key] === false) {
                        $errors[] = 'Select a valid ' . str_replace('_', ' ', $field) . '.';
                    }
                }
            }
            $targetDate = $_POST['target_date'] ?? '';
            if ($targetDate !== '') {
                $date = is_string($targetDate)
                    && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $targetDate)
                    ? DateTimeImmutable::createFromFormat('!Y-m-d', $targetDate) : false;
                if (!$date || $date->format('Y-m-d') !== $targetDate) {
                    $errors[] = 'Enter a valid target date.';
                } else {
                    $data['targetDate'] = $targetDate;
                }
            }
        }

        if (!$errors) {
            require_once __DIR__ . '/../models/Bug.php';
            try {
                if ((new Bug())->create($data)) {
                    header('Location: ?bugs');
                    return;
                }
                $errors[] = 'Unable to create bug.';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to create bug. Check the project and assignment selections.';
            }
        }

        http_response_code(422);
        $this->render('bugs/index', [
            'currentUser' => $user,
            'canManage' => $canAssign,
            'canAssign' => $canAssign,
            'selectedProjectId' => $projectId,
            'errors' => $errors,
        ]);
    }

    public function update()
    {
        // TODO: Check access: Users update only assigned bugs; Admins/Managers all bugs. Validate permitted changes.
    }

}


