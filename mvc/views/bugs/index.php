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
                <thead><tr><th scope="col">ID</th><th scope="col">Project</th><th scope="col">Status</th><th scope="col">Priority</th><th scope="col">Assigned to</th><th scope="col">Target date</th></tr></thead>
                <tbody>
                    <?php if (empty($bugs)): ?>
                        <tr><td colspan="6">No bugs found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($bugs as $item): ?>
                        <tr>
                            <td><a href="<?= $escape('?bug-details&id=' . rawurlencode((string) $item['Id'])) ?>"><?= $escape($item['Id']) ?></a></td>
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
            <?php if ($canManage): ?>
                <div class="form-group">
                    <label for="new-project">Project</label>
                    <select class="form-control" id="new-project" name="project_id" required>
                        <option value="">Select a project</option>
                        <?php foreach ($projectNames as $id => $name): ?>
                            <option value="<?= $escape($id) ?>" <?= (string) $selectedProjectId === (string) $id ? 'selected' : '' ?>><?= $escape($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="project_id" value="<?= $escape($selectedProjectId) ?>">
            <?php endif; ?>
            <?php if ($canAssign): ?>
                <?php foreach (['assigned_to' => ['Assigned to', $userNames], 'status_id' => ['Status', $statusNames], 'priority_id' => ['Priority', $priorityNames]] as $field => $settings): ?>
                    <div class="form-group">
                        <label for="new-<?= $escape($field) ?>"><?= $escape($settings[0]) ?></label>
                        <select class="form-control" id="new-<?= $escape($field) ?>" name="<?= $escape($field) ?>">
                            <option value="">Default</option>
                            <?php foreach ($settings[1] as $id => $label): ?>
                                <option value="<?= $escape($id) ?>"><?= $escape($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
                <div class="form-group">
                    <label for="new-target">Target date</label>
                    <input class="form-control" id="new-target" name="target_date" type="date">
                </div>
            <?php else: ?>
                <p>New bugs are unassigned with medium priority and no target date.</p>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit">Create bug</button>
        </form>
    </section>

    <?php if (!empty($bug)): ?>
        <section aria-labelledby="bug-details-heading">
            <h2 id="bug-details-heading">Bug details</h2>
            <dl class="dl-horizontal">
                <?php foreach (['Id' => 'ID', 'TargetDate' => 'Target date', 'ClosureDate' => 'Closure date', 'Fix' => 'Fix'] as $field => $label): ?>
                    <dt><?= $escape($label) ?></dt><dd style="white-space: pre-wrap"><?= $escape($bug[$field] ?? '') ?></dd>
                <?php endforeach; ?>
                <dt>Project</dt><dd><?= $escape($projectNames[$bug['ProjectId'] ?? ''] ?? '') ?></dd>
                <dt>Status</dt><dd><?= $escape($statusNames[$bug['StatusId'] ?? ''] ?? $bug['Status'] ?? '') ?></dd>
                <dt>Priority</dt><dd><?= $escape($priorityNames[$bug['PriorityId'] ?? ''] ?? $bug['Priority'] ?? '') ?></dd>
                <dt>Assigned to</dt><dd><?= $escape($userNames[$bug['AssignedTo'] ?? ''] ?? 'Unassigned') ?></dd>
            </dl>
            <?php if ($canUpdate ?? ($canManage || (!empty($currentUser['Id']) && (string) ($bug['AssignedTo'] ?? '') === (string) $currentUser['Id']))): ?>
                <h3>Update bug</h3>
                <form action="?bug-update" method="post">
                    <input type="hidden" name="id" value="<?= $escape($bug['Id']) ?>">
                    <?php foreach (['fix' => ['Fix', 'Fix']] as $field => $settings): ?>
                        <div class="form-group">
                            <label for="update-<?= $escape($field) ?>"><?= $escape($settings[0]) ?></label>
                            <textarea class="form-control" id="update-<?= $escape($field) ?>" name="<?= $escape($field) ?>" rows="3" <?= $field !== 'fix' ? 'required' : '' ?>><?= $escape($bug[$settings[1]] ?? '') ?></textarea>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach (['status_id' => ['Status', $statusNames, 'StatusId'], 'priority_id' => ['Priority', $priorityNames, 'PriorityId'], 'assigned_to' => ['Assigned to', $userNames, 'AssignedTo']] as $field => $settings): ?>
                        <?php if ($field !== 'status_id' && !$canAssign) { continue; } ?>
                        <div class="form-group">
                            <label for="update-<?= $escape($field) ?>"><?= $escape($settings[0]) ?></label>
                            <select class="form-control" id="update-<?= $escape($field) ?>" name="<?= $escape($field) ?>">
                                <option value="">None</option>
                                <?php foreach ($settings[1] as $id => $label): ?>
                                    <option value="<?= $escape($id) ?>" <?= (string) ($bug[$settings[2]] ?? '') === (string) $id ? 'selected' : '' ?>><?= $escape($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach (['target_date' => ['Target date', 'TargetDate'], 'closure_date' => ['Closure date', 'ClosureDate']] as $field => $settings): ?>
                        <?php if ($field === 'target_date' && !$canAssign) { continue; } ?>
                        <div class="form-group">
                            <label for="update-<?= $escape($field) ?>"><?= $escape($settings[0]) ?></label>
                            <input class="form-control" id="update-<?= $escape($field) ?>" name="<?= $escape($field) ?>" type="date" value="<?= $escape($bug[$settings[1]] ?? '') ?>">
                        </div>
                    <?php endforeach; ?>
                    <button class="btn btn-primary" type="submit">Update bug</button>
                </form>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../footer.php'; ?>

