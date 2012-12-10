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

try {
  $dbh = new PDO("mysql:host=$databaseHost;dbname=$databaseName", $databaseUser, $databasePassword);
  
  $sql = "SELECT first_name, last_name, user_id
  FROM user_info
  WHERE user_id IN
  (SELECT initiator_id FROM friend WHERE (recipient_id= :userId AND accepted=1)
  UNION
  SELECT recipient_id FROM friend WHERE (initiator_id= :userId AND accepted=1))";
  
  $statement = $dbh->prepare($sql);
  $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
  $statement->execute();
  
  while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    foreach ($row as $key => $value) {
      $row[$key] = htmlentities($value);
    }
    $friendsArray[] = $row;
  }
  
  //close db
  $dbh = null;
}
catch(PDOException $e) {
  fail($e->getMessage());
}

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
      echo "<div> <a href=\"profile.php?memb={$friend['user_id']}\">{$friend['first_name']} {$friend['last_name']}</a></div>";
    }
  }
?>
