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
  require 'api.php';

  $previous_command = $_GET['command'];
  echo '<p>';
  print($previous_command);
  echo '</p>';
?>

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
