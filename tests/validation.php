<?php

require_once __DIR__ . '/../mvc/utils/Utils.php';

$checks = 0;
function check($condition, $message)
{
    global $checks;
    $checks++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$admin = ['Id' => 1, 'RoleID' => 1];
$manager = ['Id' => 2, 'RoleID' => 2];
$user = ['Id' => 3, 'RoleID' => 3, 'ProjectId' => 10];
$bug = ['id' => 7, 'projectId' => 10, 'assignedToId' => 3, 'priorityId' => 1,
        'targetDate' => null, 'fixDescription' => '', 'dateClosed' => null];

check(Authorization::canManageUsers($admin), 'Admin can manage users.');
check(!Authorization::canManageUsers($manager), 'Manager cannot manage users.');
check(Authorization::canManageProjects($manager), 'Manager can manage projects.');
check(!Authorization::canAssignBugs($user), 'User cannot assign bugs.');
check(Authorization::canViewBug($user, $bug), 'User can view their project.');
check(Authorization::canUpdateBug($user, $bug), 'Assigned user can update.');
check(Authorization::canUpdateBug($admin, $bug), 'Admin can update.');
check(!Authorization::canViewBug(['Id' => 4, 'RoleID' => 3, 'ProjectId' => 20], $bug), 'Other project is hidden.');
check(!Authorization::canUpdateBug(['Id' => 4, 'RoleID' => 3, 'ProjectId' => 10], $bug), 'Unassigned user cannot update.');
check(!Authorization::canAccessBugs(null), 'Missing session is rejected.');
check(!Authorization::canAccessBugs(['RoleID' => 4]), 'Unknown role is rejected.');
check(!Authorization::canViewBug(null, $bug), 'Anonymous bug access is rejected.');

check(Utils::parseId('12') === 12, 'Positive IDs are parsed.');
foreach (['', '0', '-1', 'abc', [], null] as $invalidId) {
    check(!Utils::validId($invalidId), 'Invalid ID is rejected.');
}
check(Utils::validDate('2024-02-29'), 'Leap date is valid.');
check(!Utils::validDate('2025-02-29'), 'Impossible date is rejected.');
check(!Utils::validDate('2025-2-01'), 'Date format is enforced.');
check(!Utils::validDate([]), 'Array date is rejected.');
check(Utils::validText('', 250), 'Optional text may be blank.');
check(Utils::validText(str_repeat('a', 250), 250), 'Text limit is inclusive.');
check(!Utils::validText(str_repeat('a', 251), 250), 'Overlong text is rejected.');
check(!Utils::validText([], 250), 'Array text is rejected.');
check(Utils::bugFilter([]) === 'all', 'Invalid filter falls back.');
check(Utils::bugSort('id; DROP TABLE bugs') === 'id', 'Invalid sort falls back.');
check(Utils::sortDirection('asc') === 'ASC', 'Sort direction is normalized.');
check(Utils::sortDirection([]) === 'DESC', 'Array direction falls back.');
check(Utils::escape('<script>') === '&lt;script&gt;', 'HTML is escaped.');

$errors = Utils::validateUser(['name' => ' Test ', 'username' => ' test ', 'password' => 'secret', 'role_id' => '3', 'project_id' => '10'], $data);
check($errors === [] && $data['name'] === 'Test' && $data['project_id'] === 10, 'User data is normalized.');
$errors = Utils::validateUser(['name' => 'Test', 'username' => 'test', 'password' => 'secret', 'role_id' => '2', 'project_id' => '10'], $data);
check(in_array('Only Users may have a project assignment.', $errors, true), 'Manager project assignment is rejected.');
check(count(Utils::validateUser([], $data)) === 3, 'Missing user fields are rejected.');
$errors = Utils::validateUser(['name' => 'Test', 'username' => 'test', 'password' => str_repeat('x', 73), 'role_id' => 1], $data);
check(count($errors) === 1, 'Password byte limit is enforced.');

check(Utils::validateProject(['id' => '1', 'name' => ' Test '], $data, true) === [], 'Project update is valid.');
check($data === ['Project' => 'Test', 'Id' => 1], 'Project uses exact schema keys.');
check(count(Utils::validateProject(['id' => '0', 'name' => ''], $data, true)) === 2, 'Invalid project update is rejected.');
check(count(Utils::validateProject(['id' => '1', 'name' => str_repeat('x', 51)], $data, true)) === 1, 'Project name limit is enforced.');
check(Utils::validateLogin(['identifier' => ' test ', 'password' => 'secret'], $data) === [] && $data['identifier'] === 'test', 'Login input is normalized.');
check(count(Utils::validateLogin(['identifier' => [], 'password' => []], $data)) === 1, 'Malformed login input is rejected.');
$credentials = ['Password' => password_hash('secret', PASSWORD_DEFAULT)];
check(Authorization::validCredentials($credentials, 'secret'), 'Valid password matches.');
check(!Authorization::validCredentials($credentials, 'wrong'), 'Wrong password is rejected.');
check(!Authorization::validCredentials(false, 'secret'), 'Missing account is rejected.');

check(Utils::validateBugCreate(['project_id' => '20', 'summary' => 'Bug'], $user, $data) === [], 'User can create bug.');
check($data['projectId'] === 10, 'User cannot override assigned project.');
check(Utils::validateBugCreate(['project_id' => '20'], $manager, $data) === [] && $data['projectId'] === 20, 'Manager may choose project.');
check(count(Utils::validateBugCreate(['project_id' => 'bad', 'summary' => []], $manager, $data)) === 2, 'Malformed bug input is rejected.');

$errors = Utils::validateBugUpdate(['assigned_to' => '99', 'priority_id' => '2', 'target_date' => '2026-12-01', 'fix' => 'Fixed'], $bug, false, $data);
check($errors === [] && $data['assignedToId'] === 3 && $data['priorityId'] === 1 && $data['targetDate'] === null, 'User cannot change manager fields.');
check($data['fixDescription'] === 'Fixed', 'User can update fix description.');
check(count(Utils::validateBugUpdate(['assigned_to' => 'bad', 'closure_date' => '2025-02-30'], $bug, true, $data)) === 2, 'Invalid assignment and date are rejected.');
check(Utils::validateBugUpdate(['assigned_to' => '', 'closure_date' => ''], $bug, true, $data) === [] && $data['assignedToId'] === null && $data['dateClosed'] === null, 'Optional fields may be cleared.');
check(count(Utils::validateBugUpdate(['fix' => str_repeat('x', 2501)], $bug, true, $data)) === 1, 'Fix length limit is enforced.');

$model = new class {
    public function referenceExists($table, $id) { return $id === 10; }
    public function findByUsername($username) { return $username === 'taken'; }
    public function findById($id) { return $id === 10; }
};
$errors = [];
Utils::validateReferences($model, ['projectId' => 10, 'assignedToId' => 99, 'priorityId' => null], $errors);
check($errors === ['Select a valid assignee.'], 'Database references are validated.');
check(Utils::validateUserReferences($model, ['username' => 'taken']) === ['That username is already in use.'], 'Duplicate username is rejected.');
check(Utils::validateProjectReference($model, 99) === ['Select a valid project.'], 'Missing project is rejected.');

echo "Passed $checks validation and authorization checks.\n";
