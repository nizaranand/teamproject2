<?php 
if(session_id()==''){
	session_start();
}
if(!isset($_SESSION['user_id'])){
	$_SESSION['state']="noLogin";
	header('Location: login.php');
	exit;
}

require_once 'config.php';

$mysqli=new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$query = $mysqli->prepare("SELECT user_id,first_name,last_name,user_session_ip FROM user_info WHERE user_id=?");
$query->bind_param('s',$_SESSION['user_id']);
$query->execute();
$query->bind_result($user_id,$firstName,$lastName,$userSessionIP);
$query->fetch();
$query->close();
$mysqli->close();

$ip=$_SERVER['REMOTE_ADDR'];
if($userSessionIP!=$ip){
	$_SESSION['state']="badIP";
	unset($_SESSION['user_id']);
	header('login.php');
}
?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Members</title>
<link rel="stylesheet" href="style.css">
<?php
echo 'Currently logged in as: '.$firstName.' '.$lastName;
?>
<ol>
	<li>
		<a href="profile.php">View Profile</a>
	<li>
		<a href="friends.php">View Friends (Currently not implemented)</a>
	<li>
		<a href="members.php">View Members</a>
	<li>
		<a href="logout.php">Log out</a>
</ol>
<?php

$mysqli=new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$query = $mysqli->prepare("SELECT user_id,first_name,last_name FROM user_info WHERE user_id!=? ORDER BY last_name DESC LIMIT 1000");
$query->bind_param('s',$_SESSION['user_id']);
$query->execute();
$query->bind_result($mem_id,$memFirstName,$memLastName);
?>
<h2>Members:</h2>
<table>
<?php
for($i=0;$query->fetch();$i++) {
	echo "<tr>";
	echo "<form action=\"profile.php\" method=\"GET\">";
	echo "<td>".$memFirstName."</td><td>".$memLastName."</td>";
	echo "<td><a href=\"profile.php?memb=".$mem_id."\">View Profile</a></td>";
	echo "</form>";
	echo "</tr>";
	}
$query->close();
$mysqli->close();

?>
</table>
