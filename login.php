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
}

if (isset($_POST['submit'])) {
  require 'PasswordHash.php';

  //TODO error checking (ie failed database connection), input validation/sanitation, response

  //TODO Possibly move to config file
  $databaseHost = '127.0.0.1';
  //$databaseUsername = 'team14';
  //$databasePassword = 'teal';
  $databaseUsername = 'root';
  $databasePassword = 'attack12';
  $databaseName = 'team_project_2';
  //base 2 logarithm used in bcrypt security, higher means more stretching done
  $hashCost = 8;
  //force using built-in functions for portability?
  $portable = false;

  $email = $_POST['email'];
  $password = $_POST['password'];//max length 72

  $database = new mysqli($databaseHost, $databaseUsername, $databasePassword, $databaseName);

  $hasher = new PasswordHash($hashCost, $portable);

  $hash = '*';
  $statement = $database->prepare('select password from user_info where email=?');
  $statement->bind_param('s', $email);
  $statement->execute();
  $statement->bind_result($hash);
  $statement->fetch();
  $statement->close();
  $database->close();

if ($hasher->CheckPassword($password, $hash)) {
  echo 'Login succeeded';
  $database = new mysqli($databaseHost, $databaseUsername, $databasePassword, $databaseName);
  $statement = $database->prepare('select user_id from user_info where email=?');
  $statement->bind_param('s', $email);
  $statement->execute();
  $statement->bind_result($_SESSION['user_id']);
  $statement->fetch();
  $statement->close();
  $database->close();

  $ip=$_SERVER['REMOTE_ADDR'];
  $database = new mysqli($databaseHost, $databaseUsername, $databasePassword, $databaseName);
  $statement = $database->prepare('update user_info set user_session_ip=? where user_id=?');
  $statement->bind_param('ss', $ip,$_SESSION['user_id']);
  $statement->execute();
  $statement->bind_result($_SESSION['user_id']);
  $statement->fetch();
  $statement->close();
  $database->close();
  header('Location: home.php');
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
<link rel="stylesheet" href="register.css">
<h1>Social Network</h1>
<h2>Login</h2>
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post">
  <ol>
    <li>
<?php
if(isset($_SESSION['state'])){
  if($_SESSION['state']=="exists"){
    echo "Good news! You're already registered. Please sign in.";
  } elseif($_SESSION['state']=="regSuccess"){
    echo 'Registration Success! Please sign in.';
  } elseif($_SESSION['state']=="invalidLogin"){
    echo 'Invalid username/password, please try again.';
  }
  echo '<br>';
  unset($_SESSION['state']);
}
?>
      <label for="email">Email</label>
      <input type="text" name="email" id="email">
    <li>
      <label for="password">Password</label>
      <input type="password" name="password" id="password">
    <li>
      <input type="submit" name="submit" value="Submit">
  </ol>
</form>
<p><a href="register.php">Register an account</a></p>