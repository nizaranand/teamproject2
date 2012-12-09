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
	exit;
}
?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Friends</title>
<link rel="stylesheet" href="style.css">
<object width="560" height="315">
	<param name="movie" value="http://www.youtube.com/v/Qm1osmEK3f8&autoplay=1?version=3&amp;hl=en_US&amp;rel=0"></param>
	<param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param>
	<embed src="http://www.youtube.com/v/Qm1osmEK3f8&autoplay=1?version=3&amp;hl=en_US&amp;rel=0" type="application/x-shockwave-flash" width="1020" height="630" allowscriptaccess="always" allowfullscreen="false"></embed>
</object>
