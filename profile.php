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
//user is person executing code, member is owner of profile page passed in ?memb=

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
  header('Location: profile.php');
  exit;
}
if (!is_null($birthday)) {
  $birthYear = substr($birthday, 0, 4);
  $birthMonth = substr($birthday, 5, 2);
  $birthDay = substr($birthday, 8, 2);
}

//0=not friend no requests, 1=user may accept friend, 2=user request pending, 3=friend
$friend = 0;
$errorMessage = '';

if ($userId == $memberId) {
  if (isset($_POST['statusUpdate'])) {
    $status = $_POST['status'];
    if (strlen($status) <= 0 || strlen($status) > 255) {
      $errorMessage .= 'String length must be between 1 and 255 characters inclusive';
    }
    
    if ($errorMessage === '') {
      $query = $mysqli->prepare("INSERT INTO status_update (user_id, message) values (?,?)");
	    $query->bind_param('is',$userId, $status);
	    $query->execute();
	    $query->close();
    }
  }
  else if (isset($_POST['editProfile'])) {
    require_once 'PasswordHash.php';
    
    $currentPassword = $_POST['currentPassword'];
    $newFirstName = $_POST['firstName'];
    $newLastName = $_POST['lastName'];
    $newEmail = $_POST['email'];
    $newPassword = $_POST['password'];//max length 72
    $newPassword2 = $_POST['password2'];
    $newGender = $_POST['gender'];
    $newBirthMonth = $_POST['birthMonth'];
    $newBirthDay = $_POST['birthDay'];
    $newBirthYear = $_POST['birthYear'];
    
    $hasher = new PasswordHash($hashCost, $portable);
    $hash = '*';
    $query = $mysqli->prepare('select password from user_info where user_id=?');
    $query->bind_param('s', $userId);
    $query->execute();
    $query->bind_result($hash);
    $query->fetch();
    $query->close();
    if (!($hasher->CheckPassword($currentPassword, $hash))) {
      $errorMessage .= 'Failed to enter current password<br>';
    }
    unset($hasher);
    
    if (strlen($newFirstName) > 50) {
      $errorMessage .= 'First name may be a maximum of 50 characters<br>';
    }
    if (strlen($newLastName) > 50) {
      $errorMessage .= 'Last name may be a maximum of 50 characters<br>';
    }
    if (!empty($newEmail)) {
      if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMessage .= 'Email invalid<br>';
      }
      else {
        //email uniqueness verification
        $query = $mysqli->prepare('select count(user_id) from user_info where email=?');
        $query->bind_param('s', $newEmail);
        $query->execute();
        $query->bind_result($num_users);
        $query->fetch();
        $query->close();
        if($num_users!=0){
          $errorMessage.='Email in use.<br>';
        }
      }
    }
    //password max length 72, 1 and 2 has to be the same
    if (strlen($newPassword) > 72) {
      $errorMessage .= 'Password can have a maximum length of 72 characters<br>';
    }
    else if ($newPassword !== $newPassword2) {
      $errorMessage .= 'Password must match in both fields.<br>';
    }
    //convert gender from string value to int
    if ($newGender == 'undisclosed') {
      $newGender = 0;
    }
    else if ($newGender == 'male') {
      $newGender = 1;
    }
    else if ($newGender == 'female') {
      $newGender = 2;
    }
    else {
      $errorMessage .= 'Error validating gender<br>';
    }
    //strip leading zeros from date as filter_var can't seem to handle them
    $newBirthDay = ltrim($newBirthDay, '0');
    $newBirthMonth = ltrim($newBirthMonth, '0');
    $newBirthYear = ltrim($newBirthYear, '0');
    //validate date
    if (filter_var($newBirthMonth, FILTER_VALIDATE_INT) && filter_var($newBirthDay, FILTER_VALIDATE_INT) && filter_var($newBirthYear, FILTER_VALIDATE_INT)) {
      if (!checkdate($newBirthMonth, $newBirthDay, $newBirthYear)) {
        $errorMessage .= 'Invalid birthday<br>';
      }
      else {
        $birthdate = "$newBirthYear-$newBirthMonth-$newBirthDay";
      }
    }
    else if (!empty($birthMonth) || !empty($birthDay) || !empty($birthYear)) {
      $errorMessage .= 'Invalid date input<br>';
    }
    
    if ($errorMessage === '') {
      if (empty($newFirstName)) {
        $newFirstName = $firstName;
      }
      else {
        $firstName = $newFirstName;
      }
      if (empty($newLastName)) {
        $newLastName = $lastName;
      }
      else {
        $lastName = $newLastName;
      }
      if (empty($newEmail)) {
        $newEmail = $email;
      }
      else {
        $email = $newEmail;
      }
      $gender = $newGender;
      $birthday = $birthdate;
      if (!is_null($birthday)) {
        $birthYear = substr($birthday, 0, 4);
        $birthMonth = substr($birthday, 5, 2);
        $birthDay = substr($birthday, 8, 2);
      }
      if (!empty($newPassword)) {
        $hasher = new PasswordHash($hashCost, $portable);
        $hash = $hasher->HashPassword($newPassword);//min length 20
        unset($hasher);
        if (strlen($hash) < 20) {
          fail("Hash below minimum possible length");
        }
      }
      
      $query = $mysqli->prepare('UPDATE user_info SET password=?, email=?, first_name=?, last_name=?, gender=?, birthday=? WHERE user_id=?');
      $query->bind_param('ssssisi', $hash, $newEmail, $newFirstName, $newLastName, $newGender, $birthdate, $userId);
      $query->execute();
      $query->close();
    }
  }
}
else if ($userId != $memberId) {
  //check if user on this page initiated a request to logged in user, and if accepted
  $query = $mysqli->prepare("SELECT accepted FROM friend WHERE (initiator_id=? AND recipient_id=?)");
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
    $query = $mysqli->prepare("SELECT accepted FROM friend WHERE (initiator_id=? AND recipient_id=?)");
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

  //send friend request or add friend
  if (isset($_POST['changeFriend'])) {
    if ($friend == 0) {
      $query = $mysqli->prepare("INSERT INTO friend (initiator_id, recipient_id, accepted) values (?,?,0)");
	    $query->bind_param('ii',$userId,$memberId);
	    $query->execute();
	    $query->close();
	    $friend = 2;
    }
    else if ($friend == 1) {
      $query = $mysqli->prepare("UPDATE friend SET accepted=1 WHERE (initiator_id=? AND recipient_id=?)");
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
}

//retrieve status updates\
try {
  $dbh = new PDO("mysql:host=$databaseHost;dbname=$databaseName", $databaseUser, $databasePassword);
  
  $statement = $dbh->prepare("SELECT message, time_posted FROM status_update WHERE user_id= :memberId ORDER BY time_posted DESC LIMIT 5");
  $statement->bindParam(':memberId', $memberId, PDO::PARAM_INT);
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
<title>Profile</title>
<link rel="stylesheet" href="style.css">
<?php require_once 'menu.php'?>
<h2><?php echo htmlentities($firstName . " " . $lastName); ?></h2>
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
<h3>Latest 5 status updates</h3>
<?php
  if (!isset($statusArray)) {
    echo 'No statuses';
  }
  else {
    foreach ($statusArray as $status) {
      echo "<div>{$status['message']} " . htmlentities($firstName . " " . $lastName) . " at {$status['time_posted']}</div>";
    }
  }
?>
<?php
  if (!empty($errorMessage)) {
    echo "<div>$errorMessage</div>";
  }
?>
<?php if ($userId == $memberId) { ?>
<h3>Post status update</h3>
<form action="<?php echo "profile.php?memb=$memberId"; ?>" method="post">
  <div>
    <textarea maxlength="255" cols="50" rows="6" name="status"></textarea>
    <br>
    <input type="submit" name="statusUpdate" value="Submit">
  </div>
</form>
<h3>Edit profile</h3>
<form action="<?php echo "profile.php?memb=$memberId"; ?>" method="post">
  <ol>
    <li>
      <label for="currentPassword">Current password*</label>
      <input type="password" name="currentPassword" id="currentPassword">
    <li>
      <label for="firstName">First name</label>
      <input type="text" name="firstName" id="firstName">
    <li>
      <label for="lastName">Last name</label>
      <input type="text" name="lastName" id="lastName">
    <li>
      <label for="email">Email address</label>
      <input type="text" name="email" id="email">
    <li>
      <label for="password">New password</label>
      <input type="password" name="password" id="password">
    <li>
      <label for="password2">Confirm new password</label>
      <input type="password" name="password2" id="password2">
    <li>
      <label for="gender">Gender</label>
      <select name="gender" id="gender">
        <option value="undisclosed" <?php if ($gender==0){echo "selected=\"selected\"";}?>>Undisclosed</option>
        <option value="female" <?php if ($gender==2){echo "selected=\"selected\"";}?>>Female</option>
        <option value="male" <?php if ($gender==1){echo "selected=\"selected\"";}?>>Male</option>
      </select>
    <li>
      <fieldset>
        <legend>Birthday</legend>
        <select name="birthMonth" id="birthMonth">
          <option value="">Month</option>
          <option value="1" <?php if (isset($birthMonth) && $birthMonth == 1){echo "selected=\"selected\"";}?>>January</option>
          <option value="2" <?php if (isset($birthMonth) && $birthMonth == 2){echo "selected=\"selected\"";}?>>February</option>
          <option value="3" <?php if (isset($birthMonth) && $birthMonth == 3){echo "selected=\"selected\"";}?>>March</option>
          <option value="4" <?php if (isset($birthMonth) && $birthMonth == 4){echo "selected=\"selected\"";}?>>April</option>
          <option value="5" <?php if (isset($birthMonth) && $birthMonth == 5){echo "selected=\"selected\"";}?>>May</option>
          <option value="6" <?php if (isset($birthMonth) && $birthMonth == 6){echo "selected=\"selected\"";}?>>June</option>
          <option value="7" <?php if (isset($birthMonth) && $birthMonth == 7){echo "selected=\"selected\"";}?>>July</option>
          <option value="8" <?php if (isset($birthMonth) && $birthMonth == 8){echo "selected=\"selected\"";}?>>August</option>
          <option value="9" <?php if (isset($birthMonth) && $birthMonth == 9){echo "selected=\"selected\"";}?>>September</option>
          <option value="10" <?php if (isset($birthMonth) && $birthMonth == 10){echo "selected=\"selected\"";}?>>October</option>
          <option value="11" <?php if (isset($birthMonth) && $birthMonth == 11){echo "selected=\"selected\"";}?>>November</option>
          <option value="12" <?php if (isset($birthMonth) && $birthMonth == 12){echo "selected=\"selected\"";}?>>December</option>
        </select>
        <select name="birthDay" id="birthDay">
          <option value="">Day</option>;
          <?php
          for($i=1;$i<32;$i++){
            echo "<option value=\"$i\"";
            if (isset($birthDay) && $i == $birthDay) {
              echo "selected=\"selected\"";
            }
            echo ">$i</option>";
          }
          ?>
        </select>
        <select name="birthYear" id="birthYear">
          <option value="">Year</option>
          <?php
          for($i=1900;$i<2001;$i++){
            echo "<option value=\"$i\"";
            if (isset($birthYear) && $i == $birthYear) {
              echo "selected=\"selected\"";
            }
            echo ">$i</option>";
          }
          ?>
        </select>
      </fieldset>
    <li>
    <input type="submit" name="editProfile" value="Submit">
  </ol>
</form>
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
