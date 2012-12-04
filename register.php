<!DOCTYPE html>
<meta charset="utf-8">
<title>Create a new account</title>
<link rel="stylesheet" href="register.css">
<h1>Social Network</h1>
<h2>Register a new account</h2>
<form action="#" method="post">
  <ol>
    <li>
      <label for="first_name">First name</label>
      <input type="text" name="first_name" id="first_name">
    <li>
      <label for="last_name">Last name</label>
      <input type="text" name="last_name" id="last_name">
    <li>
      <label for="email">Email address</label>
      <input type="text" name="email" id="email">
    <li>
      <label for="password">Password</label>
      <input type="password" name="password" id="password">
    <li>
      <label for="password2">Confirm password</label>
      <input type="password" name="password2" id="password2">
    <li>
      <label for="gender">Gender (Optional)</label>
      <select name="gender" id="gender">
        <option value="undisclosed" selected="selected">Undisclosed</option>
        <option value="female">Female</option>
        <option value="male">Male</option>
        <option value="other">Other</option>
      </select>
    <li>
      <label for="birthday">Birthday (Optional)</label>
      <select name="agemonth" id="month">
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
      <select name="ageday" id="day">
        <option value="" selected="selected">Day</option>
        <?php
        for($i=1;$i<32;$i++){ //This is not a smart month checker. We can validate month/day after this page.
          echo "<option value=\"$i\">$i</option>";
        }
        ?>
      </select>
      <select>
        <?php
        for($i=1900;$i<2013;$i++){ //Yes, this covers a very...comprehensive age range.
          echo "<option value=\"$i\">$i</option>";
        }
        ?>
      </select>
    <li>
      <label for="image">Upload image</label>
      <input type="text" name="image" id="image">
    <li>
      <input type="submit" value="Submit">
  </ol>
</form>
