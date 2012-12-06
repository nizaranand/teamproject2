<?php 
if(session_id()==''){
	session_start();
}
if(isset($_SESSION['user_id'])){
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
	$query->bind_result($user_id,$firstName,$lastName,$userSessionIp);
	$query->fetch();
	$query->close();
	$mysqli->close();
}
echo $user_id." ".$first_name." ".$last_name." ".$user_session_ip;
?>