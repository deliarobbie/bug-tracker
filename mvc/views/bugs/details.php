<?php
$title = 'Bug details';
include __DIR__ . '/../header.php';
include __DIR__ . '/../navigation.php';
?>
<main class="container-fluid bug-details-page">
    <h1>Bug details</h1>
    <?php
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
    $projects = $projects ?? [];
    $users = $users ?? [];
    $statuses = $statuses ?? [];
    $priorities = $priorities ?? [];
    $canAssign = $canAssign ?? $canManage;
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

    <p><a href="?bugs">Back to bugs</a></p>
    <?php if (!empty($bug)): ?>
        <section class="bug-details-card" aria-labelledby="bug-details-heading">
            <h2 id="bug-details-heading">Bug #<?= $escape($bug['Id']) ?></h2>
            <dl class="bug-details-list">
                <?php foreach (['Id' => 'ID', 'Summary' => 'Summary', 'Description' => 'Description', 'DateRaised' => 'Date raised', 'TargetDate' => 'Target date', 'ClosureDate' => 'Closure date', 'Fix' => 'Fix'] as $field => $label): ?>
                    <dt><?= $escape($label) ?></dt><dd><?= $escape($bug[$field] ?? '') ?></dd>
                <?php endforeach; ?>
                <dt>Project</dt><dd><?= $escape($projectNames[$bug['ProjectId'] ?? ''] ?? '') ?></dd>
                <dt>Raised by</dt><dd><?= $escape($userNames[$bug['OwnerId'] ?? ''] ?? '') ?></dd>
                <dt>Status</dt><dd><?= $escape($statusNames[$bug['StatusId'] ?? ''] ?? $bug['Status'] ?? '') ?></dd>
                <dt>Priority</dt><dd><?= $escape($priorityNames[$bug['PriorityId'] ?? ''] ?? $bug['Priority'] ?? '') ?></dd>
                <dt>Assigned to</dt><dd><?= $escape($userNames[$bug['AssignedTo'] ?? ''] ?? 'Unassigned') ?></dd>
            </dl>
            <?php if ($canUpdate ?? ($canManage || (!empty($currentUser['Id']) && (string) ($bug['AssignedTo'] ?? '') === (string) $currentUser['Id']))): ?>
                <form class="bug-update-form" action="?bug-update" method="post" aria-labelledby="update-bug-heading">
                    <h3 id="update-bug-heading">Update bug</h3>
                    <input type="hidden" name="id" value="<?= $escape($bug['Id']) ?>">
                    <?php foreach (['fix' => ['Fix', 'Fix']] as $field => $settings): ?>
                        <div class="form-group">
                            <label for="update-<?= $escape($field) ?>"><?= $escape($settings[0]) ?></label>
                            <textarea class="form-control" id="update-<?= $escape($field) ?>" name="<?= $escape($field) ?>" rows="3" <?= $field !== 'fix' ? 'required' : '' ?>><?= $escape($bug[$settings[1]] ?? '') ?></textarea>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach (['priority_id' => ['Priority', $priorityNames, 'PriorityId'], 'assigned_to' => ['Assigned to', $userNames, 'AssignedTo']] as $field => $settings): ?>
                        <?php if (!$canAssign) { continue; } ?>
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
                            <input class="form-control" id="update-<?= $escape($field) ?>" name="<?= $escape($field) ?>" type="date" value="<?= $escape(substr((string) ($bug[$settings[1]] ?? ''), 0, 10)) ?>">
                        </div>
                    <?php endforeach; ?>
                    <button class="btn btn-primary" type="submit">Update bug</button>
                </form>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
