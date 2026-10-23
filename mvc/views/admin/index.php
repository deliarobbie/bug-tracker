<?php
// Submission 1 view placeholder: Display project create/update tools for Admins and Managers. Display user create/delete tools only for Admins. AdminController supplies users, projects, roles and errors.
$title = 'Administration';
include __DIR__ . '/../header.php';
include __DIR__ . '/../navigation.php';
?>
<main class="container-fluid">
    <h1>Administration</h1>
    <?php
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
    $projects = $projects ?? [];
    $users = $allUsers ?? [];
    $roles = $roles ?? [];
    $isAdmin = (int) ($currentUser['RoleID'] ?? 0) === 1;
    $roleNames = [];
    foreach ($roles as $role) {
        $roleNames[$role['Id']] = $role['Role'];
    }
    $projectNames = [];
    foreach ($projects as $project) {
        $projectNames[$project['Id']] = $project['Project'];
    }
    ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul>
                <?php foreach ((array) $errors as $error): ?>
                    <li><?= $escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section aria-labelledby="projects-heading">
        <h2 id="projects-heading">Projects</h2>
        <h3>Create project</h3>
        <form action="?project-create" method="post">
            <div class="form-group">
                <label for="project-name">Name</label>
                <input class="form-control" id="project-name" name="name" type="text" maxlength="50" required>
            </div>
            <button class="btn btn-primary" type="submit">Create project</button>
        </form>

        <h3>Existing projects</h3>
        <?php if (empty($projects)): ?>
            <p>No projects available.</p>
        <?php endif; ?>
        <?php foreach ($projects as $index => $project): ?>
            <form action="?project-update" method="post" class="well">
                <input type="hidden" name="id" value="<?= $escape($project['Id']) ?>">
                <div class="form-group">
                    <label for="project-name-<?= $escape($index) ?>">Name</label>
                    <input class="form-control" id="project-name-<?= $escape($index) ?>" name="name"
                           type="text" value="<?= $escape($project['Project'] ?? '') ?>" maxlength="50" required>
                </div>
                <button class="btn btn-default" type="submit">Update project</button>
            </form>
        <?php endforeach; ?>
    </section>

    <?php if ($isAdmin): ?>
        <section aria-labelledby="users-heading">
            <h2 id="users-heading">Users</h2>
            <h3>Create user</h3>
            <form action="?user-create" method="post">
                <div class="form-group">
                    <label for="user-name">Name</label>
                    <input class="form-control" id="user-name" name="name" type="text" required>
                </div>
                <div class="form-group">
                    <label for="user-username">Username</label>
                    <input class="form-control" id="user-username" name="username" type="text" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="user-password">Password</label>
                    <input class="form-control" id="user-password" name="password" type="password" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label for="user-role">Role</label>
                    <select class="form-control" id="user-role" name="role_id" required>
                        <option value="">Select a role</option>
                        <?php foreach ($roleNames as $id => $role): ?>
                            <option value="<?= $escape($id) ?>"><?= $escape($role) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="user-project">Project (Users only)</label>
                    <select class="form-control" id="user-project" name="project_id" aria-describedby="project-help">
                        <option value="">No project</option>
                        <?php foreach ($projectNames as $id => $name): ?>
                            <option value="<?= $escape($id) ?>"><?= $escape($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="help-block" id="project-help">Users may have one project. Admins and Managers have no project assignment.</p>
                </div>
                <button class="btn btn-primary" type="submit">Create user</button>
            </form>

            <h3>Existing users</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th scope="col">Name</th><th scope="col">Username</th><th scope="col">Role</th><th scope="col">Project</th><th scope="col">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="5">No users available.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $escape($user['Name'] ?? '') ?></td>
                                <td><?= $escape($user['Username'] ?? '') ?></td>
                                <td><?= $escape($roleNames[$user['RoleID']] ?? $user['RoleID'] ?? '') ?></td>
                                <td><?= $escape($projectNames[$user['ProjectId'] ?? ''] ?? 'No project') ?></td>
                                <td>
                                    <form action="?user-delete" method="post">
                                        <input type="hidden" name="id" value="<?= $escape($user['Id']) ?>">
                                        <button class="btn btn-danger" type="submit" aria-label="<?= $escape('Delete user ' . ($user['Username'] ?? '')) ?>">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../footer.php'; ?>

