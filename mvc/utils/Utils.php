<?php

require_once __DIR__ . '/Authorization.php';

class Utils
{
    // gets an input value, or returns the default
    public static function value($input, $key, $default = '')
    {
        if (isset($input[$key])) {
            return $input[$key];
        }

        return $default;
    }
    // gets a string value, or returns an empty string
    public static function stringValue($input, $key)
    {
        $value = self::value($input, $key);

        if (is_string($value)) {
            return $value;
        }

        return '';
    }
    // checks a string is not blank
    public static function required($value)
    {
        return is_string($value) && trim($value) !== '';
    }
    // checks text is valid UTF-8 and within the character limit
    public static function validText($value, $maxLength)
    {
        if (!is_string($value)) {
            return false;
        }

        
        $length = preg_match_all('/./us', $value);
        return $length !== false && $length <= $maxLength;
    }
    // returns a positive integer, or false if invalid
    public static function parseId($value)
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
    }
    // checks the date exists and uses YYYY-MM-DD
    public static function validDate($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false
            && $date->format('Y-m-d') === $value;
    }
    // checks the date is after today
    public static function futureDate($value)
    {
        return self::validDate($value) && $value > date('Y-m-d');
    }

    // checks the request uses POST
    public static function isPost()
    {
        return self::value($_SERVER, 'REQUEST_METHOD') === 'POST';
    }

    // returns an allowed bug filter
    public static function bugFilter($value)
    {
        if (in_array($value, ['all', 'open', 'overdue'], true)) {
            return $value;
        }

        return 'all';
    }

    // returns an allowed sort field
    public static function bugSort($value)
    {
        $fields = [
            'id',
            'project_id',
            'status_id',
            'priority_id',
            'target_date',
            'date_raised',
        ];

        if (in_array($value, $fields, true)) {
            return $value;
        }

        return 'id';
    }

    // returns an allowed sort direction
    public static function sortDirection($value)
    {
        if (is_string($value) && strtoupper($value) === 'ASC') {
            return 'ASC';
        }

        return 'DESC';
    }

    // checks new user details and prepares the data
    public static function validateUser($input, &$data = [])
    {
        $data = [
            'name' => trim(self::stringValue($input, 'name')),
            'username' => trim(self::stringValue($input, 'username')),
            'password' => self::value($input, 'password'),
            'role_id' => self::parseId(self::value($input, 'role_id')),
            'project_id' => null,
        ];

        $projectInput = self::value($input, 'project_id');

        if ($projectInput !== '') {
            $data['project_id'] = self::parseId($projectInput);
        }

        $errors = [];

        if (!self::required($data['name'])
            || !self::validText($data['name'], 250)) {
            $errors[] = 'Enter a name of 1 to 250 characters.';
        }

        if (!self::required($data['username'])
            || !self::validText($data['username'], 50)) {
            $errors[] = 'Enter a username of 1 to 50 characters.';
        }

        $password = $data['password'];

        if (!is_string($password) || $password === ''
            || strlen($password) > 72
            || strpos($password, "\0") !== false) {
            $errors[] = 'Enter a password of 1 to 72 bytes without null characters.';
        }

        if (!in_array($data['role_id'], [1, 2, 3], true)) {
            $errors[] = 'Select a valid role.';
        }

        if ($data['project_id'] === false) {
            $errors[] = 'Select a valid project.';
        } elseif ($data['project_id'] !== null && $data['role_id'] !== 3) {
            $errors[] = 'Only Users may have a project assignment.';
        }

        return $errors;
    }

    // checks the username is not already taken
    public static function validateUserReferences($model, $data)
    {
        if ($model->findByUsername($data['username'])) {
            return ['That username is already in use.'];
        }

        return [];
    }

    // checks an id and returns an error if invalid
    public static function validateId($input, $label, &$id)
    {
        $id = self::parseId($input);

        if ($id === false) {
            return ['Select a valid ' . $label . '.'];
        }

        return [];
    }

    // checks the project name and update id
    public static function validateProject($input, &$data = [], $updating = false)
    {
        $data = [
            'Project' => trim(self::stringValue($input, 'name')),
        ];

        $errors = [];

        if ($updating) {
            $errors = self::validateId(
                self::value($input, 'id'),
                'project',
                $data['Id']
            );
        }

        if (!self::required($data['Project'])
            || !self::validText($data['Project'], 50)) {
            $errors[] = 'Enter a project name of 1 to 50 characters.';
        }

        return $errors;
    }

    // checks the project exists
    public static function validateProjectReference($model, $id)
    {
        if (!$model->findById($id)) {
            return ['Select a valid project.'];
        }

        return [];
    }

    // checks login fields have been entered
    public static function validateLogin($input, &$data = [])
    {
        $data = [
            'identifier' => trim(self::stringValue($input, 'identifier')),
            'password' => self::value($input, 'password'),
        ];

        if ($data['identifier'] === ''
            || !is_string($data['password'])
            || $data['password'] === '') {
            return ['Enter your login identifier and password.'];
        }

        return [];
    }

    // checks new bug details and prepares the data
    public static function validateBugCreate($input, $user, &$data = [])
    {
        if (Authorization::canAssignBugs($user)) {
            $projectInput = self::value($input, 'project_id');
        } else {
            $projectInput = self::value($user, 'ProjectId');
        }

        $data = [
            'projectId' => self::parseId($projectInput),
            'ownerId' => $user['Id'],
            'summary' => self::value($input, 'summary'),
            'description' => self::value($input, 'description'),
            'assignedToId' => null,
            'priorityId' => null,
            'targetDate' => null,
            'dateClosed' => null,
            'fixDescription' => null,
        ];

        $errors = [];

        if ($data['projectId'] === false) {
            $errors[] = 'Select a valid project.';
        }

        if (!self::required($data['summary'])
            || !self::validText($data['summary'], 250)) {
            $errors[] = 'Enter a summary of 1 to 250 characters.';
        }

        if (!self::required($data['description'])
            || !self::validText($data['description'], 2500)) {
            $errors[] = 'Enter a description of 1 to 2500 characters.';
        }

        return $errors;
    }

    // checks bug changes and keeps fields that were not submitted
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
            if (!self::validText($input['fix'], 2500)) {
                $errors[] = 'Enter a fix description of at most 2,500 characters.';
            } else {
                $data['fixDescription'] = $input['fix'];
            }
        }

        if ($canManage && array_key_exists('assigned_to', $input)) {
            $data['assignedToId'] = null;

            if ($input['assigned_to'] !== '') {
                $data['assignedToId'] = self::parseId($input['assigned_to']);
            }

            if ($data['assignedToId'] === false) {
                $errors[] = 'Select a valid assignee.';
            }
        }

        if ($canManage && array_key_exists('priority_id', $input)) {
            $data['priorityId'] = null;

            if ($input['priority_id'] !== '') {
                $data['priorityId'] = self::parseId($input['priority_id']);
            }

            if ($data['priorityId'] === false) {
                $errors[] = 'Select a valid priority.';
            }
        }

        if ($canManage && array_key_exists('target_date', $input)) {
            $targetDate = $input['target_date'];

            if ($targetDate === '') {
                $data['targetDate'] = null;
            } elseif (!self::futureDate($targetDate)) {
                $errors[] = 'Enter a target date in the future.';
            } else {
                $data['targetDate'] = $targetDate;
            }
        }

        if (array_key_exists('closure_date', $input)) {
            $closureDate = $input['closure_date'];

            if ($closureDate === '') {
                $data['dateClosed'] = null;
            } elseif (!self::validDate($closureDate)) {
                $errors[] = 'Enter a valid closure date.';
            } else {
                $data['dateClosed'] = $closureDate;
            }
        }

        return $errors;
    }

    // checks related projects, users, and priorities exist
    public static function validateReferences($model, $data, &$errors)
    {
        if (isset($data['projectId'])
            && !$model->referenceExists('project', $data['projectId'])) {
            $errors[] = 'Select a valid project.';
        }

        if (isset($data['assignedToId'])
            && !$model->referenceExists('user_details', $data['assignedToId'])) {
            $errors[] = 'Select a valid assignee.';
        }

        if (isset($data['priorityId'])
            && !$model->referenceExists('priority', $data['priorityId'])) {
            $errors[] = 'Select a valid priority.';
        }
    }

    // escapes text for safe display in HTML
    public static function escape($value)
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}