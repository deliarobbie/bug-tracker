<!-- Display a list of users -->
<!-- could use a templating system/language too -->
<?php 
$title = "User List";
// previously user view
// i am keeping this file for debugging purposes, 
// it may serve as user debugging or any other debugging purposes

//could use variables for css/js files. Make sure they exist in header
include(__DIR__."/../header.php"); 

//include navigation.php where needed adding variables to help highligh
//page that you are on
?>
<ul class="well">
    <?php foreach ($users as $user): ?>
        <li>
            <?= htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8') ?>
            (<?= htmlspecialchars($user['Username'], ENT_QUOTES, 'UTF-8') ?>)
        </li>
    <?php endforeach; ?>

    <?php if (empty($users)): ?>
        <li>No users found.</li>
    <?php endif; ?>
</ul>
<?php

include(__DIR__."/../footer.php");



    