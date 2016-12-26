<!DOCTYPE html>
<html>
<head>
<style>
body {
    background-color: blue;
    color: cyan;
}
</style>
</head>

<body>
<?php
  $_LOCAL_API_CALLS = 1;
  require 'fbbs-api.php';

  $previous_command = $_POST['command'];
  echo '<p>';
  print($previous_command);
  echo '</p>';
?>

<?php
   $name = '(anonymous - type "name '. '(yourname)' . '")';
   if ($ip_name) {
     $name = $ip_name;
   }
   echo '<p>';
   echo '[' . $_SERVER['REMOTE_ADDR']  . ' - ' . $name . '] ';
   echo '</p>';
?>

<FORM NAME="form1" METHOD="POST" ACTION="">
    <INPUT TYPE="Text" VALUE="" NAME="command" SIZE="80" autofocus>
</FORM>

<?php
  $exploded_previous_command = explode(" ", $previous_command, 2);
  $arg_count = count($exploded_previous_command);
  if ($arg_count == 1) {
    $json_str = $outputObject->retrieve();
    $json_obj = json_decode($json_str);
    $json_value_array = $json_obj->value;
    foreach ($json_value_array as $value_obj) {
      echo '<p>';
      foreach ($value_obj as $property) {
        echo '<p>';
        foreach ($property as $row) {
          foreach (get_object_vars($row) as $key => $value) {
            echo $key . ' <b>' . $value . '</b><br>';
          }
        }
        echo '</p>';
      }
      echo '</p>';
    }
  }
  if ($arg_count == 2) {
    $json_str = $outputObject->retrieve();
    $json_obj = json_decode($json_str);
    $json_value_array = $json_obj->value;
    echo '<p>';
    foreach ($json_value_array as $value_obj) {
      foreach ($value_obj as $key => $value) {
        echo '<b>';
        echo $key . '</b> ' . $value . '<br>';
      }
    }
    echo '</p>';
  }
?>

</body>
</html>
