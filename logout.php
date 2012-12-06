<?php
session_start();
session_destroy();
session_start();
$_SESSION['state']="logout";
header('Location: login.php');
?>