<!-- Display a list of users -->
<!-- could use a templating system/language too -->
<?php 
$title = "Some Form";

//could use variables for css/js files. Make sure they exist in header
include(__DIR__."/../header.php"); 

//include navigation.php where needed adding variables to help highligh
//page that you are on
?>
    <div class="container-fluid">
        <h1>Some Form</h1>
        <form action="index.php?dummytest" method="post">
            <input type="submit" name="testing" value="Submit" />
        </form>
    </div>
<?php

include(__DIR__."/../footer.php");
    