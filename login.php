<?php
if(session_id()==''){
  session_start();
}
/*echo session_id()."<br>";
if(isset($_SESSION['state'])){
  echo $_SESSION['state'];
}*/

if(isset($_SESSION['user_id'])){
  header('Location: home.php');
  exit;
}

if (isset($_POST['submit'])) {
  require_once 'PasswordHash.php';

  require_once 'config.php';

  $email = $_POST['email'];
  $password = $_POST['password'];//max length 72
  
  $hasher = new PasswordHash($hashCost, $portable);
  $hash = '*';

  $database = new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
  $statement = $database->prepare('select password from user_info where email=?');
  $statement->bind_param('s', $email);
  $statement->execute();
  $statement->bind_result($hash);
  $statement->fetch();
  $statement->close();
  $database->close();

  if ($hasher->CheckPassword($password, $hash)) {
    echo 'Login succeeded';
    $database = new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
    $statement = $database->prepare('select user_id from user_info where email=?');
    $statement->bind_param('s', $email);
    $statement->execute();
    $statement->bind_result($_SESSION['user_id']);
    $statement->fetch();
    $statement->close();
    $database->close();

    $ip=$_SERVER['REMOTE_ADDR'];
    $database = new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
    $statement = $database->prepare('update user_info set user_session_ip=? where user_id=?');
    $statement->bind_param('ss', $ip,$_SESSION['user_id']);
    $statement->execute();
    $statement->bind_result($_SESSION['user_id']);
    $statement->fetch();
    $statement->close();
    $database->close();
    header('Location: home.php');
    exit;
  }
  else {
    $_SESSION['state']="invalidLogin";
  }

  unset($hasher);
}

?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Login</title>
<link rel="stylesheet" href="style.css">
<h1>Social Network</h1>
<h2>Login</h2>
<?php
if(isset($_SESSION['state'])){
  echo '<div>';
  if($_SESSION['state']=="exists"){
    echo "Good news! You're already registered. Please sign in.";
  } elseif($_SESSION['state']=="regSuccess"){
    echo 'Registration Success! Please sign in.';
  } elseif($_SESSION['state']=="invalidLogin"){
    echo 'Invalid username/password, please try again.';
  } elseif($_SESSION['state']=="badIP"){
    echo 'It appears that you have logged in from a different IP. Please try again.';
  } elseif($_SESSION['state']=="noLogin"){
    echo 'You must be logged in to view this page!';
  } elseif($_SESSION['state']=="logout"){
    echo 'You have been successfully logged out!';
  }
  echo '</div>';
  unset($_SESSION['state']);
}
?>
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post">
  <ol>
    <li>
      <label for="email">Email</label>
      <input type="text" name="email" id="email">
    <li>
      <label for="password">Password</label>
      <input type="password" name="password" id="password">
    <li>
      <input type="submit" name="submit" value="Submit">
  </ol>
</form>
<div><a href="register.php">Register an account</a></div>
