<?php
require_once __DIR__ . '/../utils/Session.php';

$currentUser = Session::getInstance()->get('user');
$isLoggedIn = is_array($currentUser) && !empty($currentUser['Id']);
$canManage = $isLoggedIn
    && in_array((int) ($currentUser['RoleID'] ?? 0), [1, 2], true);
?>

<nav class="navbar navbar-inverse" aria-label="Main navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button"
                    class="navbar-toggle collapsed"
                    data-toggle="collapse"
                    data-target="#main-navigation"
                    aria-expanded="false"
                    aria-controls="main-navigation">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <a class="navbar-brand"
               href="<?= $isLoggedIn ? ($canManage ? '?admin' : '?bugs') : '?login' ?>">
                Bug Tracker
            </a>
        </div>

        <div class="collapse navbar-collapse" id="main-navigation">
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
                                (string) ($currentUser['Name'] ?? ''),
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