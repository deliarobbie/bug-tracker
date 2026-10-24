<?php
require_once __DIR__ . '/../utils/Session.php';
require_once __DIR__ . '/../utils/Authorization.php';

$currentUser = Session::getInstance()->get('user');
$isLoggedIn = Authorization::isLoggedIn($currentUser);
$canManage = $isLoggedIn && Authorization::canManageProjects($currentUser);
$homeRoute = '?login';
if ($isLoggedIn) {
    $homeRoute = '?bugs';
    if ($canManage) {
        $homeRoute = '?admin';
    }
}
$displayName = '';
if (isset($currentUser['Name'])) {
    $displayName = $currentUser['Name'];
}
?>

<nav class="navbar navbar-inverse" aria-label="Main navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand"
               href="<?= $homeRoute ?>">
                Bug Tracker
            </a>
        </div>

        <div id="main-navigation">
            <?php if ($isLoggedIn): ?>
                <ul class="nav navbar-nav">
                    <li><a href="?bugs">Bugs</a></li>

                    <?php if ($canManage): ?>
                        <li><a href="?admin">Administration</a></li>
                    <?php endif; ?>
                </ul>

                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <p class="navbar-text">
                            <?= htmlspecialchars(
                                (string) $displayName,
                                ENT_QUOTES | ENT_SUBSTITUTE,
                                'UTF-8'
                            ) ?>
                        </p>
                    </li>
                    <li><a href="?logout">Logout</a></li>
                </ul>
            <?php else: ?>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="?login">Login</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
