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
    if (!isset($projects)) {
        $projects = [];
    }
    if (!isset($users)) {
        $users = [];
    }
    if (!isset($statuses)) {
        $statuses = [];
    }
    if (!isset($priorities)) {
        $priorities = [];
    }
    if (!isset($canAssign)) {
        $canAssign = $canManage;
    }
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
        <?php
        if (isset($bug['projectId'])) {
            $projectId = $bug['projectId'];
        } else {
            $projectId = '';
        }
        $projectName = '';
        if (isset($projectNames[$projectId])) {
            $projectName = $projectNames[$projectId];
        }
        if (isset($bug['ownerId'])) {
            $ownerId = $bug['ownerId'];
        } else {
            $ownerId = '';
        }
        $ownerName = '';
        if (isset($userNames[$ownerId])) {
            $ownerName = $userNames[$ownerId];
        }
        if (isset($bug['statusId'])) {
            $statusId = $bug['statusId'];
        } else {
            $statusId = '';
        }
        if (isset($bug['Status'])) {
            $statusName = $bug['Status'];
        } else {
            $statusName = '';
        }
        if (isset($statusNames[$statusId])) {
            $statusName = $statusNames[$statusId];
        }
        if (isset($bug['priorityId'])) {
            $priorityId = $bug['priorityId'];
        } else {
            $priorityId = '';
        }
        if (isset($bug['Priority'])) {
            $priorityName = $bug['Priority'];
        } else {
            $priorityName = '';
        }
        if (isset($priorityNames[$priorityId])) {
            $priorityName = $priorityNames[$priorityId];
        }
        if (isset($bug['assignedToId'])) {
            $assignedTo = $bug['assignedToId'];
        } else {
            $assignedTo = '';
        }
        $assigneeName = 'Unassigned';
        if (isset($userNames[$assignedTo])) {
            $assigneeName = $userNames[$assignedTo];
        }
        ?>
        <section class="bug-details-card" aria-labelledby="bug-details-heading">
            <h2 id="bug-details-heading">Bug #<?= $escape($bug['id']) ?></h2>
            <dl class="bug-details-list">
                <?php foreach (['id' => 'ID', 'summary' => 'Summary', 'description' => 'Description', 'dateRaised' => 'Date raised', 'targetDate' => 'Target date', 'dateClosed' => 'Closure date', 'fixDescription' => 'Fix'] as $field => $label): ?>
                    <?php
                    $fieldValue = '';
                    if (isset($bug[$field])) {
                        $fieldValue = $bug[$field];
                    }
                    ?>
                    <dt><?= $escape($label) ?></dt><dd><?= $escape($fieldValue) ?></dd>
                <?php endforeach; ?>
                <dt>Project</dt><dd><?= $escape($projectName) ?></dd>
                <dt>Raised by</dt><dd><?= $escape($ownerName) ?></dd>
                <dt>Status</dt><dd><?= $escape($statusName) ?></dd>
                <dt>Priority</dt><dd><?= $escape($priorityName) ?></dd>
                <dt>Assigned to</dt><dd><?= $escape($assigneeName) ?></dd>
            </dl>
            <?php
            if (!isset($canUpdate)) {
                $canUpdate = Authorization::canUpdateBug($currentUser, $bug);
            }
            ?>
            <?php if ($canUpdate): ?>
                <form class="bug-update-form" action="?bug-update" method="post" aria-labelledby="update-bug-heading">
                    <h3 id="update-bug-heading">Update bug</h3>
                    <input type="hidden" name="id" value="<?= $escape($bug['id']) ?>">
                    <div class="form-group">
                        <label for="update-fix">Fix</label>
                        <?php
                        $fixDescription = '';
                        if (isset($bug['fixDescription'])) {
                            $fixDescription = $bug['fixDescription'];
                        }
                        ?>
                        <textarea class="form-control" id="update-fix" name="fix" rows="3"><?= $escape($fixDescription) ?></textarea>
                    </div>
                    <?php foreach (['priority_id' => ['Priority', $priorityNames, 'priorityId'], 'assigned_to' => ['Assigned to', $userNames, 'assignedToId']] as $field => $settings): ?>
                        <?php if (!$canAssign) { continue; } ?>
                        <div class="form-group">
                            <label for="update-<?= $escape($field) ?>"><?= $escape($settings[0]) ?></label>
                            <select class="form-control" id="update-<?= $escape($field) ?>" name="<?= $escape($field) ?>">
                                <option value="">None</option>
                                <?php foreach ($settings[1] as $id => $label): ?>
                                    <?php
                                    $selectedValue = '';
                                    if (isset($bug[$settings[2]])) {
                                        $selectedValue = $bug[$settings[2]];
                                    }
                                    ?>
                                    <option value="<?= $escape($id) ?>" <?php if ((string) ($selectedValue) === (string) $id): ?>selected<?php endif; ?>><?= $escape($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach (['target_date' => ['Target date', 'targetDate'], 'closure_date' => ['Closure date', 'dateClosed']] as $field => $settings): ?>
                        <?php if ($field === 'target_date' && !$canAssign) { continue; } ?>
                        <div class="form-group">
                            <label for="update-<?= $escape($field) ?>"><?= $escape($settings[0]) ?></label>
                            <?php
                            $dateValue = '';
                            if (isset($bug[$settings[1]])) {
                                $dateValue = $bug[$settings[1]];
                            }
                            ?>
                            <input class="form-control" id="update-<?= $escape($field) ?>" name="<?= $escape($field) ?>" type="date" value="<?= $escape(substr((string) ($dateValue), 0, 10)) ?>">
                        </div>
                    <?php endforeach; ?>
                    <button class="btn btn-primary" type="submit">Update bug</button>
                </form>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
