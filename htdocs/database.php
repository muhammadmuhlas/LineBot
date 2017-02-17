<?php
/* Database Configuration */
$hostname   = "localhost";
$database   = "kotor"; 
$username   = "kotor";    
$password   = "jokem123";   

try {

    $dbo = new PDO('mysql:host='.$hostname.';dbname='.$database, $username, $password);
} catch (PDOException $e) {

    $msg = $e->getMessage();
    echo $msg;
    die();
}
?>