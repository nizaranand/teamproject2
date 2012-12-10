<?php
$databaseHost = '127.0.0.1';
$databaseUser = 'teal';
$databasePassword = 'team14';
$databaseName = 'team14';
//base 2 logarithm used in bcrypt security, higher means more stretching done
$hashCost = 14;
//force using built-in functions for portability?
$portable = false;
//hide important details using fail() if true
$debug = true;

function fail($details)
{
  $message = "Error";
  global $debug;
  if ($debug) {
    $message .= ": $details";
  }
  exit($message);
}
?>
