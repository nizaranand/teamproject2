<head>
<meta charset="utf-8">
<title>Profile</title>
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

if (isset($_SESSION['friend_id'])){
	$mysqli=new mysqli($db_ip,$db_user,$db_password,$db_name);
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	$false=(1==0);
	$query = $mysqli->prepare("INSERT INTO friend (initiator_id, recipient_id, accepted) values (?,?,?)" );
	$query->bind_param('sss',$_SESSION['user_id'],$_REQUEST['memb'],$false);
	$query->execute();
	$query->fetch();
	$query->close();
	$mysqli->close();
	echo 'Friend Added!';
	unset($_SESSION['friend_id']);
}
?>
</head>
<?php 
	$mysqli=new mysqli($db_ip,$db_user,$db_password,$db_name);
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	$memb_id=$_REQUEST['memb']; //TODO Add injection sanitation and protection.
	$query = $mysqli->prepare("SELECT email,first_name,last_name,gender,birthday FROM user_info WHERE user_id=?");
	$query->bind_param('s',$memb_id);
	$query->execute();
	$query->bind_result($email,$firstName,$lastName,$gender,$birthday);
	$query->fetch();
	$query->close();
	$mysqli->close();
?>

<h2><?php echo $firstName." ".$lastName; ?></h2>
<ol>
	<li>
		Gender: <?php if($gender==0){echo "Undisclosed";}
		elseif($gender==1){echo "Male";}
		elseif($gender==2){echo "Female";}
		?>
	<li>
		Email: <?php echo $email; ?>
	<li>
		Birthday: <?php echo $birthday; ?>
</ol>
<form action=<?php echo "\"profile.php?memb=".$memb_id;?> method="post" enctype="multipart/form-data">
	<input type="hidden" name="memb" value="15">
	<?php $_SESSION['friend_id']=$memb_id ?>
	<input type="submit" name="" value="Add Friend">
</form>