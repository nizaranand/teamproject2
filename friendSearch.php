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

//TODO extra: improve search
if (isset($_POST['nameSearch'])) {
  $firstName = $_POST['firstName'];
  $lastName = $_POST['lastName'];
  
  if (empty($firstName)) {
    $firstName = "%";
  }
  if (empty($lastName)) {
    $firstName = "%";
  }
  
  try {
    $dbh = new PDO("mysql:host=$databaseHost;dbname=$databaseName", $databaseUser, $databasePassword);
    
    $statement = $dbh->prepare("SELECT user_id, email, first_name, last_name, gender, birthday, picture_extension FROM user_info WHERE (first_name LIKE :firstName AND last_name LIKE :lastName)");
    $statement->bindParam(':firstName', $firstName, PDO::PARAM_STR);
    $statement->bindParam(':lastName', $lastName, PDO::PARAM_STR);
    $statement->execute();
    
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
      foreach ($row as $key => $value) {
        $row[$key] = htmlentities($value);
      }
      $membersArray[] = $row;
    }
    
    //close db
    $dbh = null;
  }
  catch(PDOException $e) {
    fail($e->getMessage());
  }
}
else if (isset($_POST['emailSearch'])) {
  $email = $_POST['email'];
  
  try {
    $dbh = new PDO("mysql:host=$databaseHost;dbname=$databaseName", $databaseUser, $databasePassword);
    
    $statement = $dbh->prepare("SELECT user_id, email, first_name, last_name, gender, birthday, picture_extension FROM user_info WHERE email LIKE :email");
    $statement->bindParam(':email', $email, PDO::PARAM_STR);
    $statement->execute();
    
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
      foreach ($row as $key => $value) {
        $row[$key] = htmlentities($value);
      }
      $membersArray[] = $row;
    }
    
    //close db
    $dbh = null;
  }
  catch(PDOException $e) {
    fail($e->getMessage());
  }
}

$mysqli->close();
?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Friend Search</title>
<link rel="stylesheet" href="style.css">
<?php require_once 'menu.php'?>
<h1>Member search</h1>
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post">
  <ol>
    <li>
      <label for="firstName">First Name</label>
      <input type="text" name="firstName" id="firstName">
    <li>
      <label for="lastName">Last Name</label>
      <input type="text" name="lastName" id="lastName">
    <li>
      <input type="submit" name="nameSearch" value="Submit">
  </ol>
</form>
<div>or</div>
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post">
  <ol>
    <li>
      <label for="">Email</label>
      <input type="text" name="email" id="email">
    <li>
      <input type="submit" name="emailSearch" value="Submit">
  </ol>
</form>
<?php
  if (isset($membersArray)) {
    foreach ($membersArray as $member) {
      echo "<div class=\"memberData\">";
      echo "<div class=\"thumbnail\">{$member['user_id']}</div>";
      echo "<div class=\"firstName\"><a href=\"profile.php?memb={$member['user_id']}\">{$member['first_name']}</a></div>";
      echo "<div class=\"lastName\">{$member['last_name']}</div>";
      echo "<div class=\"email\">{$member['email']}</div>";
      echo "<div class=\"gender\">" . ($member['gender'] == 0 ? "Undisclosed" : ($member['gender'] == 1 ? "Male" : "Female")) . "</div>";
      echo "<div class=\"birthday\">{$member['birthday']}</div>";
      echo "</div>"; 
    }
  }
  else {
    echo "No results.";
  }
?>
