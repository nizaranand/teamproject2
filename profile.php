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

$query = $mysqli->prepare("SELECT user_session_ip FROM user_info WHERE user_id=?");
$query->bind_param('s',$_SESSION['user_id']);
$query->execute();
$query->bind_result($userSessionIP);
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

if (isset($_POST['addFriend'])){
	$mysqli=new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
	if (mysqli_connect_errno()) {
		fail(mysqli_connect_error());
	}
		
	$query = $mysqli->prepare("INSERT INTO friend (initiator_id, recipient_id, accepted) values (?,?,0)" );
	$query->bind_param('ii',$_SESSION['user_id'],$_REQUEST['memb']);
	$query->execute();
	$query->fetch();
	$query->close();
	$mysqli->close();
	echo 'Friend request sent.';
}
?>
<?php
	$mysqli=new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
	if (mysqli_connect_errno()) {
		fail(mysqli_connect_error());
	}

	$memb_id=$_REQUEST['memb']; //TODO Add injection sanitation and protection.
	($query = $mysqli->prepare("SELECT user_id,email,first_name,last_name,gender,birthday FROM user_info WHERE user_id=?"))
	  || fail($mysqli->error);
	$query->bind_param('s',$memb_id)
	  || fail($mysqli->error);
	$query->execute()
	  || fail($mysqli->error);
	$query->bind_result($user,$email,$firstName,$lastName,$gender,$birthday)
	  || fail($mysqli->error);
	if (!$query->fetch() && $mysqli->errno) {
	  fail($mysqli->error);
  }
	$query->close();
	$mysqli->close();
	if (empty($user))
	  exit("User doesn't exist");
?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Profile</title>
<link rel="stylesheet" href="style.css">
<h2><?php echo htmlentities($firstName ." ". $lastName); ?></h2>
<ol>
	<li>
		Gender: <?php if($gender==0){echo "Undisclosed";}
		elseif($gender==1){echo "Male";}
		elseif($gender==2){echo "Female";}
		?>
	<li>
		Email: <?php echo htmlentities($email); ?>
	<li>
		Birthday: <?php echo htmlentities($birthday); ?>
</ol>
<?php if ($_SESSION['user_id'] == $memb_id) { ?>
<h3>Edit profile</h3>
<div>Form here</div>
<?php }
  else { ?>
<form action="<?php echo "profile.php?memb=$memb_id" ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="memb" value="15">
	<input type="submit" name="" value="Add Friend">
</form>
<?php } ?>
