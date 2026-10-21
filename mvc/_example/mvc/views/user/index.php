<!-- Display a list of users -->
<!-- could use a templating system/language too -->
<?php 
$title = "User List";

//could use variables for css/js files. Make sure they exist in header
include(__DIR__."/../header.php"); 

//include navigation.php where needed adding variables to help highligh
//page that you are on
?>
    <div class="container-fluid">
        <h1>User List</h1>
        <ul class="well">
            <?php foreach ($users as $user) { ?>
                <li><?php echo $user->name; ?> (<?php echo $user->email; ?>)</li>
            <?php } ?>
        </ul>
    </div>
<?php

include(__DIR__."/../footer.php");
    