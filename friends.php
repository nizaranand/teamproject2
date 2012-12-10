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

$ip=$_SERVER['REMOTE_ADDR'];
if($userSessionIP!=$ip){
	$_SESSION['state']="badIP";
	unset($_SESSION['user_id']);
	header('login.php');
	exit;
}

$sql = "SELECT first_name, last_name
FROM user_info
WHERE user_id IN
(SELECT initiator_id FROM friend WHERE (recipient_id=? AND accepted=1)
UNION
SELECT recipient_id FROM friend WHERE (initiator_id=? AND accepted=1))";
$query = $mysqli->prepare($sql);
$query->bind_param('ii', $userId, $userId);
$query->execute();
$result = $query->get_result(); //hopefully mysqlnd is installed

while ($row = $result->fetch_assoc()) {
  foreach ($row as $key => $value) {
    $row[$key] = htmlentities($value);
  }
  $friendsArray[] = $row;
}
$result->free();
$query->close();

$mysqli->close();
?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Friends</title>
<link rel="stylesheet" href="style.css">
<?php require_once 'menu.php'?>
<h1>Friends</h1>
<?php
  if (!isset($friendsArray)) {
    echo 'No friends';
  }
  else {
    foreach ($friendsArray as $friend) {
      echo "<div> {$friend['first_name']} {$friend['last_name']} </div>";
    }
  }
?>
