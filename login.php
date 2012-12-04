<?php

require 'PasswordHash.php';

//TODO error checking (ie failed database connection), input validation/sanitation, response

//TODO Possibly move to config file
$databaseHost = '127.0.0.1';
//$databaseUsername = 'team14';
//$databasePassword = 'teal';
$databaseUsername = 'root';
$databasePassword = 'rooty';
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

if ($hasher->CheckPassword($password, $hash)) {
	$result = 'Login succeeded';
} 
else {
	$result = 'Login failed';
}

unset($hasher);

$statement->close();
$database->close();

echo $result;

?>
