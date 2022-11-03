<?php 
    require('db.php');

    extract($_POST);
    $id = $_SESSION['id'];
    $query    = "SELECT * FROM `urls` WHERE user_id='$id'";
    $url_rows = mysqli_query($con, $query) or die(mysql_error());
    $url_count = mysqli_num_rows($url_rows);
    if($url_rows){
        
    }else{
        echo "<pre>";
        echo "An Error occured.<br>";
        echo "Error: ".$con->error."<br>";
        echo "SQL: ".$sql."<br>";
        echo "</pre>";
    }
    
    $con->close();
?>