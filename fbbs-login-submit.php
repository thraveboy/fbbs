<!DOCTYPE html>
<html>
<head>
<style>
body, input {
    font-family: monospace;
    font-size: small;
    background-color: blue;
    color: cyan;
}

input {
    border-top-width: 1px;
    border-bottom-width: 1px;
    border-left-width: 0px;
    border-right-width: 0px;
    border-color: cyan;
    outline-width: 1px;
    outline-width-left: 0px;
    outline-width-right: 0px;
    outline-color: cyan;
}

</style>
</head>

<body>
<?php
  $_LOCAL_API_CALLS = 1;

  class FDB extends SQLite3
  {
    function __construct()
    {
      $this->open('fbbs-user.db');
    }
  }

  $usernamepost = $_POST["username"];
  $passwordpost = $_POST["password"];
  $passwordagainpost = $_POST["password-again"];

  $username_emptyq = empty($usernamepost);
  $password_emptyq = empty($passwordpost);
  $passwordagain_emptyq = empty($passwordagainpost);

  if ($username_emptyq || $password_emptyq) {
    if ($username_emptyq) {
      echo 'no user:';
    }
    if ($password_emptyq) {
      echo 'no password:';
    }
  }
  else {

    $db = new FDB();
    if(!$db){
      echo $db->lastErrorMsg();
    }
    $cleanusername = $db->escapeString($usernamepost);
    $user_info_query = 'SELECT * FROM "users" WHERE username = "' .
                        $cleanusername . '" ORDER BY timestamp DESC LIMIT 1';
    $results_user_info = $db->query($user_info_query);
    $userfound = FALSE;
    if (!empty($results_user_info)) {
      $user_info_array = $results_user_info->fetchArray(SQLITE3_ASSOC);
      if ($user_info_array) {
        var_dump($user_info_array);
        $retrievedusername = $user_info_array["username"];
        $retrievedpassword = $user_info_array["password"];
        $retrievedsalt = $user_info_array["salt"];
        $userfound = TRUE;
      }
      echo 'HERE!';
    }
    if (!$passwordagain_emptyq) {
      echo 'attempting to create new account for ' . $usernamepost . '<br>';
      $passwordhashed = password_hash($passwordpost, PASSWORD_DEFAULT);
      var_dump($passwordhashed);
      echo 'THERE!';
    }
  }
  echo $usernamepost . ":";
  echo $passwordpost . ":";
  echo $passwordagainpost . ":";
?>

<p>
|\______
<br>
--------->>>>
<br>
[o]----[o]---=>>>>>>>
<br>
-------------->>>>>>>>>
<br>
****************************
<br>
** Fury's Fortress (fbbs) **
<br>
**------------------------**
<br>
****************************
<br>
<FORM NAME="form1" METHOD="POST" ACTION="fbbs-login-submit.php">
username:
<INPUT TYPE="Text" VALUE="" id="username" NAME="username" SIZE="40" autofocus>
<br>
password:
<INPUT TYPE="Text" VALUE="" id="password" NAME="password" SIZE="40" autofocus>
<br>
password again (if new user):
<INPUT TYPE="Text" VALUE="" id="password-again" NAME="password-again" SIZE="40" autofocus>
<div class="userpassDiv" />
<input type="submit" style="display:none" />
</FORM>
</p>

</body>
</html>
