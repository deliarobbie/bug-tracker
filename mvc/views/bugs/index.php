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
    if (!isset($bugs)) {
        $bugs = [];
    }
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
    if (!isset($selectedProjectId)) {
        if (isset($currentUser['ProjectId'])) {
            $selectedProjectId = $currentUser['ProjectId'];
        } else {
            $selectedProjectId = '';
        }
    }
    if (!isset($filter)) {
        $filter = 'all';
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

    <?php
    $selectedProjectName = 'No project selected';
    if (isset($projectNames[$selectedProjectId])) {
        $selectedProjectName = $projectNames[$selectedProjectId];
    }
    ?>
    <p>Project: <?= $escape(($selectedProjectName)) ?></p>
    <form action="" method="get" class="form-inline">
        <input type="hidden" name="bugs" value="">
        <?php if ($canManage): ?>
            <label for="filter-project">Project</label>
            <select class="form-control" id="filter-project" name="project_id">
                <option value="">All projects</option>
                <?php foreach ($projectNames as $id => $name): ?>
                    <option value="<?= $escape($id) ?>" <?php if ((string) $selectedProjectId === (string) $id): ?>selected<?php endif; ?>><?= $escape($name) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <label for="bug-filter">Show</label>
        <select class="form-control" id="bug-filter" name="filter">
            <?php foreach (['all' => 'All bugs', 'open' => 'Open bugs', 'overdue' => 'Overdue bugs'] as $value => $label): ?>
                <option value="<?= $escape($value) ?>" <?php if ($filter === $value): ?>selected<?php endif; ?>><?= $escape($label) ?></option>
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
                        <?php
                        if (isset($item['projectId'])) {
                            $projectId = $item['projectId'];
                        } else {
                            $projectId = '';
                        }
                        $projectName = '';
                        if (isset($projectNames[$projectId])) {
                            $projectName = $projectNames[$projectId];
                        }
                        if (isset($item['statusId'])) {
                            $statusId = $item['statusId'];
                        } else {
                            $statusId = '';
                        }
                        if (isset($item['Status'])) {
                            $statusName = $item['Status'];
                        } else {
                            $statusName = '';
                        }
                        if (isset($statusNames[$statusId])) {
                            $statusName = $statusNames[$statusId];
                        }
                        if (isset($item['priorityId'])) {
                            $priorityId = $item['priorityId'];
                        } else {
                            $priorityId = '';
                        }
                        if (isset($item['Priority'])) {
                            $priorityName = $item['Priority'];
                        } else {
                            $priorityName = '';
                        }
                        if (isset($priorityNames[$priorityId])) {
                            $priorityName = $priorityNames[$priorityId];
                        }
                        if (isset($item['assignedToId'])) {
                            $assignedTo = $item['assignedToId'];
                        } else {
                            $assignedTo = '';
                        }
                        if (isset($item['Name'])) {
                            $assigneeName = $item['Name'];
                        } else {
                            $assigneeName = 'Unassigned';
                        }
                        if (isset($userNames[$assignedTo])) {
                            $assigneeName = $userNames[$assignedTo];
                        }
                        ?>
                        <tr>
                            <td><a href="<?= $escape('?bug-details&id=' . rawurlencode((string) $item['id'])) ?>"><?= $escape($item['id']) ?></a></td>
                            <?php
                            $summary = '';
                            if (isset($item['summary'])) {
                                $summary = $item['summary'];
                            }
                            ?>
                            <td><?= $escape($summary) ?></td>
                            <td><?= $escape($projectName) ?></td>
                            <td><?= $escape($statusName) ?></td>
                            <td><?= $escape($priorityName) ?></td>
                            <td><?= $escape($assigneeName) ?></td>
                            <?php
                            $targetDate = '';
                            if (isset($item['targetDate'])) {
                                $targetDate = $item['targetDate'];
                            }
                            ?>
                            <td><?= $escape($targetDate) ?></td>
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
                <?php
                $submittedSummary = Utils::stringValue($_POST, 'summary');
                ?>
                <input class="form-control" id="new-summary" name="summary" type="text" maxlength="250" value="<?= $escape($submittedSummary) ?>">
            </div>
            <div class="form-group">
                <label for="new-description">Description (optional)</label>
                <?php
                $submittedDescription = Utils::stringValue($_POST, 'description');
                ?>
                <textarea class="form-control" id="new-description" name="description" rows="5" maxlength="2500"><?= $escape($submittedDescription) ?></textarea>
            </div>
            <div class="form-group">
                <label for="new-project">Project</label>
                <select class="form-control" id="new-project" name="project_id" required>
                    <option value="">Select a project</option>
                    <?php foreach ($projectNames as $id => $name): ?>
                        <option value="<?= $escape($id) ?>" <?php if ((string) $selectedProjectId === (string) $id): ?>selected<?php endif; ?>><?= $escape($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p>New bugs are unassigned with medium priority and no target date.</p>
            <button class="btn btn-primary" type="submit">Create bug</button>
        </form>
    </section>

</main>
<?php include __DIR__ . '/../footer.php'; ?>

