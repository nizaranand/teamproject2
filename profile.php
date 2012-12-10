<?php
if(session_id()==''){
	session_start();
}
if(!isset($_SESSION['user_id'])){
	$_SESSION['state']="noLogin";
	header('Location: login.php');
	exit;
}
$userId = $_SESSION['user_id'];

if (!isset($_REQUEST['memb'])) {
  //redirect user to own profile page if memb isn't set i.e. if they navigate to profile.php
  header("Location: profile.php?memb=$userId");
  exit;
}
$memberId = $_REQUEST['memb'];

require_once 'config.php';

$mysqli=new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
if (mysqli_connect_errno()) {
	fail(mysqli_connect_error());
}

//get ip and compare to ip in database, if different send to login page
$query = $mysqli->prepare("SELECT user_session_ip FROM user_info WHERE user_id=?");
$query->bind_param('i',$userId);
$query->execute();
$query->bind_result($userSessionIP);
$query->fetch();
$query->close();

$ip=$_SERVER['REMOTE_ADDR'];
if($userSessionIP!=$ip){
	$_SESSION['state']="badIP";
	unset($_SESSION['user_id']);
	header('login.php');
	exit;
}

($query = $mysqli->prepare("SELECT email,first_name,last_name,gender,birthday FROM user_info WHERE user_id=?"))
  || fail($mysqli->error);
$query->bind_param('s',$memberId)
  || fail($mysqli->error);
$query->execute()
  || fail($mysqli->error);
$query->bind_result($email,$firstName,$lastName,$gender,$birthday)
  || fail($mysqli->error);
if (!$query->fetch() && $mysqli->errno) {
  fail($mysqli->error);
}
$query->close();
if (!isset($email)) {
  exit("User doesn't exist");
}

//0=not friend no requests, 1=user may accept friend, 2=user request pending, 3=friend
$friend = 0;
//id user is looking at someone else's profile
if ($userId != $memberId) {
  //check if user on this page initiated a request to logged in user, and if accepted
  $query = $mysqli->prepare("SELECT accepted FROM friend WHERE initiator_id=? AND recipient_id=?");
  $query->bind_param('ii', $memberId, $userId);
  $query->execute();
  $query->bind_result($accepted);
  $query->fetch();
  $query->close();
  
  if (isset($accepted)) {
    if ($accepted == 1) {
      $friend = 3;
    }
    else {
      $friend = 1;
    }
  }
  else {
    //check if logged in user initiated request
    $query = $mysqli->prepare("SELECT accepted FROM friend WHERE initiator_id=? AND recipient_id=?");
    $query->bind_param('ii', $userId, $memberId);
    $query->execute();
    $query->bind_result($accepted);
    $query->fetch();
    $query->close();
    
    if (isset($accepted)) {
      if ($accepted == 1) {
        //possibly improve deleteion performance by using different value for friend
        //as initiator is different. cost is maintainability
        $friend = 3;
      }
      else {
        $friend = 2;
      }
    }
  }
}

//send friend request or add friend
if (isset($_POST['changeFriend'])) {
  if ($userId == $memberId) {
    exit("Can't add self as friend");
  }
  
  if ($friend == 0) {
    $query = $mysqli->prepare("INSERT INTO friend (initiator_id, recipient_id, accepted) values (?,?,0)");
	  $query->bind_param('ii',$userId,$memberId);
	  $query->execute();
	  $query->close();
	  $friend = 2;
  }
  else if ($friend == 1) {
    $query = $mysqli->prepare("UPDATE friend SET accepted=1 WHERE initiator_id=? AND recipient_id=?");
    $query->bind_param('ii', $memberId, $userId);
    $query->execute();
	  $query->close();
    $friend = 3;
  }
  else if ($friend == 3) {
    $query = $mysqli->prepare("DELETE FROM friend WHERE (initiator_id=? AND recipient_id=?) OR (initiator_id=? AND recipient_id=?)");
    $query->bind_param('iiii', $memberId, $userId, $userId, $memberId);
    $query->execute();
	  $query->close();
    $friend = 0;
  }
}

$mysqli->close();
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
<?php if ($userId == $memberId) { ?>
<h3>Edit profile</h3>
<div>Form here</div>
<?php
  }
  else if ($friend == 0) {
?>
<form action="<?php echo "profile.php?memb=$memberId"; ?>" method="post">
  <input type="submit" name="changeFriend" value="Send friend request">
</form>
<?php
  }
  else if ($friend == 1) {
?>
<form action="<?php echo "profile.php?memb=$memberId"; ?>" method="post">
  <input type="submit" name="changeFriend" value="Accept friend request">
</form>
<?php
  }
  else if ($friend == 2) {
?>
<div>Friend request pending</div>
<?php
  }
  else if ($friend == 3) {
?>
<form action="<?php echo "profile.php?memb=$memberId"; ?>" method="post">
  <input type="submit" name="changeFriend" value="Remove friend">
</form>
<?php
  }
?>
