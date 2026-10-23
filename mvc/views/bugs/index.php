<?php
// Submission 1 view placeholder: Display assigned project and permitted bugs; all/open/overdue filters; project selection for Admins/Managers; creation, details and authorized update sections. BugController supplies data and permission flags.
$title = 'Bugs';
include __DIR__ . '/../header.php';
include __DIR__ . '/../navigation.php';
?>
<main class="container-fluid">
    <h1>Bugs</h1>
    <?php
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
    $bugs = $bugs ?? [];
    $projects = $projects ?? [];
    $users = $users ?? [];
    $statuses = $statuses ?? [];
    $priorities = $priorities ?? [];
    $canAssign = $canAssign ?? $canManage;
    $selectedProjectId = $selectedProjectId ?? ($currentUser['ProjectId'] ?? '');
    $filter = $filter ?? 'all';
    $projectNames = [];
    foreach ($projects as $project) {
        $projectNames[$project['Id']] = $project['Project'];
    }
    $userNames = [];
    foreach ($users as $user) {
        $userNames[$user['Id']] = $user['Username'];
    }
    $statusNames = [];
    foreach ($statuses as $status) {
        $statusNames[$status['Id']] = $status['Status'];
    }
    $priorityNames = [];
    foreach ($priorities as $priority) {
        $priorityNames[$priority['Id']] = $priority['Priority'];
    }
    ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert"><ul>
            <?php foreach ((array) $errors as $error): ?>
                <li><?= $escape($error) ?></li>
            <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <p>Project: <?= $escape($projectNames[$selectedProjectId] ?? 'No project selected') ?></p>
    <form action="" method="get" class="form-inline">
        <input type="hidden" name="bugs" value="">
        <?php if ($canManage): ?>
            <label for="filter-project">Project</label>
            <select class="form-control" id="filter-project" name="project_id">
                <option value="">All projects</option>
                <?php foreach ($projectNames as $id => $name): ?>
                    <option value="<?= $escape($id) ?>" <?= (string) $selectedProjectId === (string) $id ? 'selected' : '' ?>><?= $escape($name) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <label for="bug-filter">Show</label>
        <select class="form-control" id="bug-filter" name="filter">
            <?php foreach (['all' => 'All bugs', 'open' => 'Open bugs', 'overdue' => 'Overdue bugs'] as $value => $label): ?>
                <option value="<?= $escape($value) ?>" <?= $filter === $value ? 'selected' : '' ?>><?= $escape($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-default" type="submit">Apply filters</button>
    </form>

    <section aria-labelledby="bug-list-heading">
        <h2 id="bug-list-heading">Bugs</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th scope="col">ID</th><th scope="col">Name</th><th scope="col">Project</th><th scope="col">Status</th><th scope="col">Priority</th><th scope="col">Assigned to</th><th scope="col">Target date</th></tr></thead>
                <tbody>
                    <?php if (empty($bugs)): ?>
                        <tr><td colspan="7">No bugs found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($bugs as $item): ?>
                        <tr>
                            <td><a href="<?= $escape('?bug-details&id=' . rawurlencode((string) $item['Id'])) ?>"><?= $escape($item['Id']) ?></a></td>
                            <td><?= $escape($item['Summary'] ?? '') ?></td>
                            <td><?= $escape($projectNames[$item['ProjectId'] ?? ''] ?? '') ?></td>
                            <td><?= $escape($statusNames[$item['StatusId'] ?? ''] ?? $item['Status'] ?? '') ?></td>
                            <td><?= $escape($priorityNames[$item['PriorityId'] ?? ''] ?? $item['Priority'] ?? '') ?></td>
                            <td><?= $escape($userNames[$item['AssignedTo'] ?? ''] ?? $item['AssignedToName'] ?? 'Unassigned') ?></td>
                            <td><?= $escape($item['TargetDate'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section aria-labelledby="create-bug-heading">
        <h2 id="create-bug-heading">Create bug</h2>
        <form action="?bug-create" method="post">
            <div class="form-group">
                <label for="new-summary">Summary (optional)</label>
                <input class="form-control" id="new-summary" name="summary" type="text" maxlength="250" value="<?= $escape(is_string($_POST['summary'] ?? null) ? $_POST['summary'] : '') ?>">
            </div>
            <div class="form-group">
                <label for="new-description">Description (optional)</label>
                <textarea class="form-control" id="new-description" name="description" rows="5" maxlength="2500"><?= $escape(is_string($_POST['description'] ?? null) ? $_POST['description'] : '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="new-project">Project</label>
                <select class="form-control" id="new-project" name="project_id" required>
                    <option value="">Select a project</option>
                    <?php foreach ($projectNames as $id => $name): ?>
                        <option value="<?= $escape($id) ?>" <?= (string) $selectedProjectId === (string) $id ? 'selected' : '' ?>><?= $escape($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p>New bugs are unassigned with medium priority and no target date.</p>
            <button class="btn btn-primary" type="submit">Create bug</button>
        </form>
    </section>

</main>
<?php include __DIR__ . '/../footer.php'; ?>

