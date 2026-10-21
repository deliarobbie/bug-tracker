<?php
// Submission 1 view placeholder: Display login identifier/password form and validation errors. AuthController supplies errors and safe entered values.
$title = 'Login';
include __DIR__ . '/../header.php';
include __DIR__ . '/../navigation.php';
?>
<main class="container-fluid">
    <h1>Login</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul>
                <?php foreach ((array) $errors as $error): ?>
                    <li><?= htmlspecialchars((string) $error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="?login-submit" method="post">
        <div class="form-group">
            <label for="identifier">Login identifier</label>
            <input class="form-control" type="text" id="identifier" name="identifier"
                   value="<?= htmlspecialchars((string) ($identifier ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                   autocomplete="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input class="form-control" type="password" id="password" name="password"
                   autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary" type="submit">Log in</button>
    </form>
</main>
<?php include __DIR__ . '/../footer.php'; ?>

