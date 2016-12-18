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
  echo '<div id="previous_command" hidden>';
  print($previous_command);
  echo '</div>';
?>

<p>
<FORM NAME="form1" METHOD="GET" ACTION="fbbs-dash.php">
    <INPUT TYPE="Text" VALUE="" id="command" NAME="command" SIZE="80" autofocus
           onkeyup="showDash(this.value)">
</FORM>
</p>

<p>Dash
<br>
<span id="dash"></span>
</p>

<script>
function showDash(str) {
  var xhttp;
  if (str.length == 0) {
    document.getElementById("dash").innerHTML = "=-=";
    return;
  }
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("dash").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "fbbs-index.php?command="+str, true);
  xhttp.send();
}

var prev_cmd_val = document.getElementById("previous_command").innerText;

if (prev_cmd_val) {
 showDash(prev_cmd_val);
 document.getElementById("command").value = prev_cmd_val;
}

function updateDash() {
  var dashName = document.getElementById("command").value;
  if (dashName) {
    showDash(dashName);
  }
}

var dashUpdater = setInterval(updateDash, 5000);

</script>

</body>
</html>
