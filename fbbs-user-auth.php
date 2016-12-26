<?php
  function authorize_user() {
    $userauthorized = FALSE;
    $username = $_COOKIE['username'];
    $token = $_COOKIE['authToken'];
    if (($username != "") && ($token != "")) {
      class FDBUSER extends SQLite3
      {
        function __construct()
        {
          $this->open('fbbs-user.db');
        }
      }
      $fdbuser = new FDBUSER();
      if (!$fdbuser) {
        echo $fdbuser->lastErrorMsg();
      }
      $auth_query = 'SELECT token FROM auth_tokens where username = "' .
                    $username . '"';
      $auth_result = $fdbuser->query($auth_query);
      if (!empty($auth_result)) {
        $auth_array = $auth_result->fetchArray(SQLITE3_ASSOC);
        $auth_encoded = $auth_array['token'];
        if (!empty($auth_encoded)) {
          if (password_verify($token, $auth_encoded)) {
            $userauthorized = TRUE;
          }
        }
      }
    }
    if ($userauthorized) {
      return $username;
    }
    else {
      header("Location: index.php");
    }
    return FALSE;
  }
?>
