<?php

require_once __DIR__ . '/Authorization.php';

class Utils
{
    public static function value($input, $key, $default = '')
    {
        if (isset($input[$key])) {
            return $input[$key];
        }
        return $default;
    }

    public static function text($value)
    {
        if (is_string($value)) {
            return trim($value);
        }
        return '';
    }

    public static function required($value)
    {
        return is_string($value) && trim($value) !== '';
    }

    public static function validText($value, $maxLength)
    {
        if (!is_string($value)) {
            return false;
        }
        $length = preg_match_all('/./us', $value);
        return $length !== false && $length <= $maxLength;
    }

    public static function parseId($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    }

    public static function validId($value)
    {
        return self::parseId($value) !== false;
    }

    public static function validDate($value)
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
            return false;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    public static function futureDate($value)
    {
        return self::validDate($value) && $value > date('Y-m-d');
    }

    public static function allowedValue($value, $allowedValues)
    {
        return in_array($value, $allowedValues, true);
    }

    public static function isPost()
    {
        return self::value($_SERVER, 'REQUEST_METHOD') === 'POST';
    }

    public static function bugFilter($value)
    {
        if (self::allowedValue($value, ['all', 'open', 'overdue'])) {
            return $value;
        }
        return 'all';
    }

    public static function stringValue($input, $key)
    {
        $value = self::value($input, $key);
        if (is_string($value)) {
            return $value;
        }
        return '';
    }

    public static function bugSort($value)
    {
        if (self::allowedValue($value, ['id', 'project_id', 'status_id', 'priority_id', 'target_date', 'date_raised'])) {
            return $value;
        }
        return 'id';
    }

    public static function sortDirection($value)
    {
        if (is_string($value) && strtoupper($value) === 'ASC') {
            return 'ASC';
        }
        return 'DESC';
    }

    // Validators return errors and supply normalized model data through $data.
    public static function validateUser($input, &$data = [])
    {
        $name = self::text(self::value($input, 'name'));
        $username = self::text(self::value($input, 'username'));
        $password = self::value($input, 'password');
        $roleId = filter_var(self::value($input, 'role_id'), FILTER_VALIDATE_INT);
        $projectInput = self::value($input, 'project_id');
        $projectId = null;
        if ($projectInput !== '') {
            $projectId = self::parseId($projectInput);
        }
        $errors = [];
        if ($name === '' || $username === '') {
            $errors[] = 'Enter a name and username.';
        }
        if (!is_string($password) || $password === ''
            || strlen($password) > 72 || strpos($password, "\0") !== false) {
            $errors[] = 'Enter a password of 1 to 72 bytes without null characters.';
        }
        if (!self::allowedValue($roleId, [1, 2, 3])) {
            $errors[] = 'Select a valid role.';
        }
        if ($projectId === false) {
            $errors[] = 'Select a valid project.';
        } elseif ($projectId !== null && $roleId !== 3) {
            $errors[] = 'Only Users may have a project assignment.';
        }
        $data = [
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'role_id' => $roleId,
            'project_id' => $projectId,
        ];
        return $errors;
    }

    public static function validateUserReferences($model, $data)
    {
        if ($model->findByUsername($data['username'])) {
            return ['That username is already in use.'];
        }
        return [];
    }

    public static function validateId($input, $label, &$id)
    {
        $id = self::parseId($input);
        if ($id === false) {
            return ['Select a valid ' . $label . '.'];
        }
        return [];
    }

    public static function validateProject($input, &$data = [], $updating = false)
    {
        $data = ['Project' => self::text(self::value($input, 'name'))];
        $errors = [];
        if ($updating) {
            $errors = self::validateId(self::value($input, 'id'), 'project', $data['Id']);
        }
        $nameLength = strlen($data['Project']);
        if (function_exists('mb_strlen')) {
            $nameLength = mb_strlen($data['Project'], 'UTF-8');
        }
        if (!$updating && $data['Project'] === '') {
            $errors[] = 'Enter a project name.';
        } elseif ($updating && ($data['Project'] === '' || $nameLength > 50)) {
            $errors[] = 'Enter a project name of 1 to 50 characters.';
        }
        return $errors;
    }

    public static function validateProjectReference($model, $id)
    {
        if (!$model->findById($id)) {
            return ['Select a valid project.'];
        }
        return [];
    }

    public static function validateLogin($input, &$data = [])
    {
        $identifier = self::text(self::value($input, 'identifier'));
        $password = self::value($input, 'password');
        $data = ['identifier' => $identifier, 'password' => $password];
        if ($identifier === '' || !is_string($password) || $password === '') {
            return ['Enter your login identifier and password.'];
        }
        return [];
    }

    public static function validateBugCreate($input, $user, &$data = [])
    {
        $canAssign = Authorization::canAssignBugs($user);
        if ($canAssign) {
            if (isset($input['project_id'])) {
                $projectInput = $input['project_id'];
            } else {
                $projectInput = '';
            }
        } else {
            if (isset($user['ProjectId'])) {
                $projectInput = $user['ProjectId'];
            } else {
                $projectInput = '';
            }
        }
        $projectId = self::parseId($projectInput);
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
            if (isset($input[$field])) {
                $value = $input[$field];
            } else {
                $value = '';
            }
            if (!self::validText($value, $limit)) {
                $errors[] = 'Enter a valid ' . $field . ' of at most ' . $limit . ' characters.';
            } else {
                $data[$field] = $value;
            }
        }
        return $errors;
    }

    public static function validateBugUpdate($input, $bug, $canManage, &$data = [])
    {
        $data = [
            'assignedToId' => $bug['assignedToId'],
            'priorityId' => $bug['priorityId'],
            'targetDate' => $bug['targetDate'],
            'fixDescription' => $bug['fixDescription'],
            'dateClosed' => $bug['dateClosed'],
        ];
        $errors = [];
        if (array_key_exists('fix', $input)) {
            $fix = $input['fix'];
            if (!self::validText($fix, 2500)) {
                $errors[] = 'Enter a fix description of at most 2,500 characters.';
            } else {
                $data['fixDescription'] = $fix;
            }
        }
        if ($canManage) {
            foreach (['assigned_to' => 'assignedToId', 'priority_id' => 'priorityId'] as $field => $key) {
                if (!array_key_exists($field, $input)) {
                    continue;
                }
                $value = $input[$field];
                $data[$key] = null;
                if ($value !== '') {
                    $data[$key] = self::parseId($value);
                }
                if ($data[$key] === false) {
                    $errors[] = 'Select a valid ' . str_replace('_', ' ', $field) . '.';
                }
            }
        }
        foreach (['closure_date' => 'dateClosed', 'target_date' => 'targetDate'] as $field => $key) {
            if (($field === 'target_date' && !$canManage) || !array_key_exists($field, $input)) {
                continue;
            }
            $value = $input[$field];
            if ($value === '') {
                $data[$key] = null;
                continue;
            }
            if (!self::validDate($value)) {
                $errors[] = 'Enter a valid ' . str_replace('_', ' ', $field) . '.';
            } else {
                $data[$key] = $value;
            }
        }
        return $errors;
    }

    public static function validateReferences($model, $data, &$errors)
    {
        foreach (['projectId' => 'project', 'assignedToId' => 'user_details', 'priorityId' => 'priority'] as $key => $table) {
            if (isset($data[$key]) && !$model->referenceExists($table, $data[$key])) {
                $errors[] = 'Select a valid ' . ['projectId' => 'project', 'assignedToId' => 'assignee', 'priorityId' => 'priority'][$key] . '.';
            }
        }
    }

    public static function escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
