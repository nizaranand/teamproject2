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
	fail(mysqli_connect_error());
}

($query = $mysqli->prepare("SELECT user_id,first_name,last_name,user_session_ip FROM user_info WHERE user_id=?")) || fail($mysqli->error);
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
	exit;
}
?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Home Page</title>
<link rel="stylesheet" href="style.css">
<?php
echo "<div>Welcome, " . htmlentities($firstName . " " . $lastName) . "</div>";
$_SESSION['profile_id']=$_SESSION['user_id'];
?>
<ol>
	<li>
		<?php echo "<a href=\"profile.php?memb=".$_SESSION['user_id']."\">"; ?> View Profile</a>
	<li>
		<a href="friends.php">View Friends (Currently not implemented)</a>
	<li>
		<a href="members.php">View Members</a>
	<li>
		<a href="logout.php">Log out</a>
</ol>
