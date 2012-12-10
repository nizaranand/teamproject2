<?php
if(session_id()==''){
  session_start();
}

/*if(isset($_SESSION['state'])){
  echo $_SESSION['state'];
}*/

if(isset($_SESSION['user_id'])){
  header('Location: home.php');
  exit;
}

if (isset($_POST['submit'])) {
  require_once 'PasswordHash.php';

  require_once 'config.php';

  $firstName = $_POST['firstName'];
  $lastName = $_POST['lastName'];
  $email = $_POST['email'];
  $password = $_POST['password'];//max length 72
  $password2 = $_POST['password2'];
  $gender = $_POST['gender'];
  $birthMonth = $_POST['birthMonth'];
  $birthDay = $_POST['birthDay'];
  $birthYear = $_POST['birthYear'];
  
  $errorMessage = '';
  //TODO make image file name the user's ID on upload, add extension type to database, verify image type, sanitize
  if(is_uploaded_file($_FILES['image']['tmp_name'])) {
    if ($_FILES["image"]["error"] > 0) {
      $errorMessage .= $_FILES["image"]["error"] . "<br>";
    }
    else {
      $database = new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
      $result = $database->query("SHOW TABLE STATUS LIKE 'user_info'");
      $userId = $result->fetch_array();
      $userId = $userId['Auto_increment'];
      $result->free();
      $database->close();
      
      move_uploaded_file($_FILES["image"]["tmp_name"], './' . $userId);
    }
  }
  
  if (strlen($firstName) > 50) {
    $errorMessage .= 'First name may be a maximum of 50 characters<br>';
  }
  else if (strlen($firstName)==0){
    $errorMessage .= 'First name cannot be blank!<br>';
  }
  if (strlen($lastName) > 50) {
    $errorMessage .= 'Last name may be a maximum of 50 characters<br>';
  }
  else if (strlen($lastName)==0){
    $errorMessage .= 'Last name cannot be blank!<br>';
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errorMessage .= 'Email invalid<br>';
  }
  else {
    //email uniqueness verification
    $database = new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
    $statement = $database->prepare('select count(user_id) from user_info where email=?');
    $statement->bind_param('s', $email);
    $statement->execute();
    $statement->bind_result($num_users);
    $statement->fetch();
    $statement->close();
    $database->close();
    if($num_users!=0){
      $errorMessage.='Email already exists! Please sign in.<br>';
      $_SESSION['state']="exists";
      header('Location: login.php');
      exit;
    }
  }
  //password max length 72, 1 and 2 has to be the same
  if (strlen($password) > 72) {
    $errorMessage .= 'Password can have a maximum length of 72 characters<br>';
  }
  else if(strlen($password) < 6) {
    $errorMessage.= 'Password cannot be less than 6 characters.<br>';
  }
  else if ($password !== $password2) {
    $errorMessage .= 'Password must match in both fields.<br>';
  }
  //convert gender from string value to int
  if ($gender == 'undisclosed') {
    $gender = 0;
  }
  else if ($gender == 'male') {
    $gender = 1;
  }
  else if ($gender == 'female') {
    $gender = 2;
  }
  else {
    $errorMessage .= 'Error validating gender<br>';
  }
  //strip leading zeros from date as filter_var can't seem to handle them
  $birthDay = ltrim($birthDay, '0');
  $birthMonth = ltrim($birthMonth, '0');
  $birthYear = ltrim($birthYear, '0');
  //validate date
  if (filter_var($birthMonth, FILTER_VALIDATE_INT) && filter_var($birthDay, FILTER_VALIDATE_INT) && filter_var($birthYear, FILTER_VALIDATE_INT)) {
    if (!checkdate($birthMonth, $birthDay, $birthYear)) {
      $errorMessage .= 'Invalid birthday<br>';
    }
    else {
      $birthdate = "$birthYear-$birthMonth-$birthDay";
    }
  }
  else if (!empty($birthMonth) || !empty($birthDay) || !empty($birthYear)) {
    $errorMessage .= 'Invalid date input<br>';
  }
  
  if ($errorMessage === '') {
    $hasher = new PasswordHash($hashCost, $portable);
    $hash = $hasher->HashPassword($password);//min length 20
    unset($hasher);
    if (strlen($hash) < 20) {
      fail("Hash below minimum possible length");
    }
    
    $activationKey = mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand(); 
    
    $database = new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
    $statement = $database->prepare('insert into user_info (password, email, first_name, last_name, gender, birthday, active, activation_key) values (?, ?, ?, ?, ?, ?, 0, ?)');
    $statement->bind_param('ssssiss', $hash, $email, $firstName, $lastName, $gender, $birthdate, $activationKey);
    $statement->execute();
    $statement->close();
    $database->close();
  }
}

if (isset($_POST['submit'])) {
  if ($errorMessage === '') {
    $_SESSION['state']="regSuccess";
    header('Location: login.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Create a new account</title>
<link rel="stylesheet" href="style.css">
</head>
<h1>Social Network</h1>
<h2>Register a new account</h2>
<?php
  if (!empty($errorMessage)) {
    echo "<div>$errorMessage</div>";
  }
?>
<div>Asterisk (*) indicates a required field. </div>
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" method="post" enctype="multipart/form-data">
  <ol>
    <li>
      <label for="firstName">First name*</label>
      <input type="text" name="firstName" id="firstName">
    <li>
      <label for="lastName">Last name*</label>
      <input type="text" name="lastName" id="lastName">
    <li>
      <label for="email">Email address*</label>
      <input type="text" name="email" id="email">
    <li>
      <label for="password">Password*</label>
      <input type="password" name="password" id="password">
    <li>
      <label for="password2">Confirm password*</label>
      <input type="password" name="password2" id="password2">
    <li>
      <label for="gender">Gender</label>
      <select name="gender" id="gender">
        <option value="undisclosed" selected="selected">Undisclosed</option>
        <option value="female">Female</option>
        <option value="male">Male</option>
      </select>
    <li>
      <fieldset>
        <legend>Birthday</legend>
        <select name="birthMonth" id="birthMonth">
          <option value="" selected="selected">Month</option>
          <option value="1">January</option>
          <option value="2">February</option>
          <option value="3">March</option>
          <option value="4">April</option>
          <option value="5">May</option>
          <option value="6">June</option>
          <option value="7">July</option>
          <option value="8">August</option>
          <option value="9">September</option>
          <option value="10">October</option>
          <option value="11">November</option>
          <option value="12">December</option>
        </select>
        <select name="birthDay" id="birthDay">
          <option value="">Day</option>;
          <?php
          for($i=1;$i<32;$i++){
            echo "<option value=\"$i\">$i</option>";
          }
          ?>
        </select>
        <select name="birthYear" id="birthYear">
          <option value="">Year</option>
          <?php
          for($i=1900;$i<2001;$i++){
            echo "<option value=\"$i\">$i</option>";
          }
          ?>
        </select>
      </fieldset>
    <li>
      <label for="image">Upload image</label>
      <input type="file" name="image" id="image">
    <li>
      <input type="submit" name="submit" value="Submit">
  </ol>
</form>
<div><a href="login.php">Existing user login</div></p>
