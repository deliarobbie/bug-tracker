<?php 
$title = "File Not Found";

//could use variables for css/js files. Make sure they exist in header
include(__DIR__."/header.php"); 

//include navigation.php here as well? 
?>
    <div class="container-fluid">
        <h1 class="bg-danger">File Not Found</h1>
        <p class="well"><?php echo "Looking for: $uri";?></p> 
    </div>
<?php
include(__DIR__."/footer.php");