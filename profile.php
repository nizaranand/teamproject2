<head>
<meta charset="utf-8">
<title>Login</title>
<link rel="stylesheet" href="style.css">
<?php 
if(session_id()==''){
	session_start();
}
if(!isset($_SESSION['user_id'])){
	$_SESSION['state']="noLogin";
	header('Location: login.php');
}

$db_ip="localhost";
$db_user="root";
$db_password="attack12";
$db_name="team_project_2";

$mysqli=new mysqli($db_ip,$db_user,$db_password,$db_name);
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
</head>