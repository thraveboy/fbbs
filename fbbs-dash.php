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

var prev_cmd_val = document.getElementById("previous_command").innerText;

function showDash(str) {
  var xhttp;
  if (str.length == 0) {
    document.getElementById("dash").innerHTML = "=-=";
    return;
  }
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("dash").innerHTML = "<p><b>" + prev_cmd_val +
                                                  "</b></p>";
      var current_time = (new Date()).getTime();
      var jsonresponseobj = JSON.parse(this.responseText).value[0];
      Object.keys(jsonresponseobj).forEach(function(key,index) {
        var dash_entries = jsonresponseobj[key].length;
        for (var entry_index=0; entry_index < dash_entries; entry_index++) {
          var dash_entry = jsonresponseobj[key][entry_index];
          Object.keys(dash_entry).forEach(function(key2,index2) {
             var dash_val = dash_entry[key2];
             var output_val = '';
             switch(key2) {
               case 'value':
                 output_val = '<b>' + dash_val + '</b> ';
                 break;
               case 'timestamp':
                 var dashtime = ((current_time/1000) - dash_val) / 3600;
                 output_val = (Math.trunc((dashtime*1000)))/1000 + ' hours ago';
                 break;
               default:
                 break;
             }
             document.getElementById("dash").innerHTML += output_val;
            });
        };
        document.getElementById("dash").innerHTML += '<br>';
      });
    }
  }
  xhttp.open("GET", "fbbs-api.php?command="+str, true);
  xhttp.send();
}

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
