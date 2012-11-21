<!DOCTYPE html>
<meta charset="utf-8">
<title>Create a new account</title>
<link rel="stylesheet" href="register.css">
<h1>Social Network</h1>
<h2>Register a new account</h2>
<form action="#" method="post">
  <ol>
    <li>
      <label for="first-name">First name</label>
      <input type="text" name="first-name" id="first-name">
    <li>
      <label for="last-name">Last name</label>
      <input type="text" name="last-name" id="last-name">
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
      <label for="age">Age (Optional)</label>
      <input type="text" name="age" id="age">
    <li>
      <label for="image">Upload image</label>
      <input type="text" name="image" id="image">
    <li>
      <input type="submit" value="Submit">
  </ol>
</form>
