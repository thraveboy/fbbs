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
        if (password_verify($passwordpost, $retrievedpassword)) {
          echo 'password matched';
          $auth_token = bin2hex(openssl_random_pseudo_bytes(16));
          echo 'token generated';
          $auth_insert_query = 'REPLACE INTO auth_tokens ' .
                               '(username, token, expire, timestamp) ' .
                               'VALUES ("' . $retrievedusername . '", "'.
                               $auth_token . '", "", "' . $request_time .
                               '")';
          $db->exec($auth_insert_query);
          echo $db->lastErrorMsg();
          echo 'auth token generated : ' . $auth_token . '<br>';
        }
        else {
          echo "password didn't match";
        }

        $userfound = TRUE;
      }
      echo 'HERE!';
    }
    if (!$userfound && !$passwordagain_emptyq) {
      echo 'attempting to create new account for ' . $usernamepost . '<br>';
      $passwordhashed = password_hash($passwordpost, PASSWORD_DEFAULT);
      if (password_verify($passwordagainpost, $passwordhashed)) {
         echo 'going to create new account for ' . $cleanusername . ':' . $passwordhashed . '-';
         $request_time = $db->escapeString($_SERVER['REQUEST_TIME']);
         $create_query = 'INSERT INTO users (username, password, timestamp) ' .
                         'VALUES ("'. $cleanusername . '", "' .
                         $passwordhashed . '", "'. $request_time . '")';
         echo '<br>' . $create_query . '<br>';
         $db->exec($create_query);
         echo $db->lastErrorMsg();
         $insert_id = $db->lastInsertRowid();
         echo 'created user id ' . $insert_id . '<br>';
      }
      else {
         echo 'password and password again did not match';
      }
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
