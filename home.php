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
$sql = "SELECT status_update.message, user_info.first_name, user_info.last_name, status_update.time_posted
FROM status_update
INNER JOIN user_info
ON (user_info.user_id=status_update.user_id) AND (user_info.user_id=? OR user_info.user_id IN
((SELECT initiator_id FROM friend WHERE (recipient_id=? AND accepted=1)
UNION
SELECT recipient_id FROM friend WHERE (initiator_id=? AND accepted=1))))
ORDER BY status_update.time_posted DESC
LIMIT 20";
$query = $mysqli->prepare($sql);
$query->bind_param('iii', $userId, $userId, $userId);
$query->execute();
$result = $query->get_result(); //hopefully mysqlnd is installed

while ($row = $result->fetch_assoc()) {
  foreach ($row as $key => $value) {
    $row[$key] = htmlentities($value);
  }
  $statusArray[] = $row;
}
$result->free();
$query->close();

$mysqli->close();
?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Home Page</title>
<link rel="stylesheet" href="style.css">
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
      echo "<div>{$status['message']} {$status['first_name']} {$status['last_name']}f at {$status['time_posted']}</div>";
    }
  }
?>
<ol>
	<li>
		<?php echo "<a href=\"profile.php?memb=".$userId."\">"; ?> View Profile</a>
	<li>
		<a href="friends.php">View Friends</a>
	<li>
		<a href="friendSearch.php">Search Members</a>
	<li>
		<a href="logout.php">Log out</a>
</ol>
