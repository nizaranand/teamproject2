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
	fail(mysqli_connect_error());
}

($query = $mysqli->prepare("SELECT user_id,first_name,last_name,user_session_ip FROM user_info WHERE user_id=?"))
  || fail($mysqli->error);
$query->bind_param('s',$userId);
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
//get list of friends
//get latest 20 updates with user_id of self or friends
try {
  $dbh = new PDO("mysql:host=$databaseHost;dbname=$databaseName", $databaseUser, $databasePassword);
  
  $sql = "SELECT status_update.message, user_info.first_name, user_info.last_name, status_update.time_posted
  FROM status_update
  INNER JOIN user_info
  ON (user_info.user_id=status_update.user_id) AND (user_info.user_id= :userId OR user_info.user_id IN
  ((SELECT initiator_id FROM friend WHERE (recipient_id= :userId AND accepted=1)
  UNION
  SELECT recipient_id FROM friend WHERE (initiator_id= :userId AND accepted=1))))
  ORDER BY status_update.time_posted DESC
  LIMIT 20";
  
  $statement = $dbh->prepare($sql);
  $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
  $statement->execute();
  
  while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    foreach ($row as $key => $value) {
      $row[$key] = htmlentities($value);
    }
    $statusArray[] = $row;
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
<title>Home Page</title>
<link rel="stylesheet" href="style.css">
<?php require_once 'menu.php'?>
<?php
echo "<div>Welcome, " . htmlentities($firstName . " " . $lastName) . "</div>";
?>
<h3>Latest 20 personal and friends status updates</h3>
<?php
  if (!isset($statusArray)) {
    echo 'No statuses';
  }
  else {
    foreach ($statusArray as $status) {
      echo "<div class=\"status\">{$status['message']} <div class=\"byline\">{$status['first_name']} {$status['last_name']} at {$status['time_posted']}</div></div>";
    }
  }
?>
