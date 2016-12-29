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
    color: cyan;
}

input[type=submit] {
    outline-color: cyan;
    background-color: blue;
}

p {
  white-space: nowrap;
}

</style>
</head>

<body>
<?php
  $previous_cmd_trim = trim($_POST['command']);
  $_POST['command'] = $previous_cmd_trim;

  $_LOCAL_API_CALLS = 1;

  require_once 'fbbs-api.php';

  $previous_command = explode(" ", $previous_cmd_trim)[0];
  echo '<div id="previous_command" hidden>';
  print($previous_command);
  echo '</div>';

  require_once 'fbbs-user-auth.php';

  $username = authorize_user();

  $lastauth = last_auth_user();
?>
|\\::::::::::::::::::::::::|\::::::::::::::::::
<br>
||| <b>f</b>ury's <b>f</b>ortress (<b>fbbs</b>) ||: command :
<span id="board_name"></span>
<br>
|||........................|/:::::::::::::::...last online...
<b>[<span id="last_active"><?=$lastauth?></span>]</b>...

<br>
<br>

<FORM id="command_form" ACTION="" METHOD="POST">
<?php
  echo '<INPUT TYPE="Text" VALUE="' . $previous_command  . ' " ';
  echo 'id="command" NAME="command" SIZE="20" autofocus>';
?>
    <INPUT ID="submission" TYPE="Submit" Value="|/\enter/\|"
     onclick="launchCommand()">
</FORM>

<script>
  function launchCommand() {
    var str = document.getElementById("command").value;
    var str_trim = str.trim();
    var str = str_trim.split(" ")[0];
    if (str.length == 0) {
      document.getElementById("dash").innerHTML = "Try help... ";
      return false;
    }
    else {
      document.getElementById("command_form").action ="fbbs-" + str + ".php";
      document.getElementById("command_form").submit();
    }
    return false;
  }
</script>

<p>
<span id="dash"></span>
</p>

<script>

String.prototype.hashCode = function(){
	var hash = 0;
	if (this.length == 0) return hash;
	for (i = 0; i < this.length; i++) {
		char = this.charCodeAt(i);
		hash = ((hash<<5)-hash)+char;
		hash = hash & hash; // Convert to 32bit integer
	}
	return hash;
}
var prev_cmd_val = document.getElementById("previous_command").innerText;
prev_cmd_val = prev_cmd_val.split(" ")[0];

function funPrefixes(prefix_length = 5) {
  var char_set = ["_", "_", "_", "_", "_", "_", "_", "_",
                  "_", "_", "_", "_", "_", "_", "|", "O", "0", "o"];


  var return_string = "";
  for (var i=0; i<prefix_length; i++) {
    return_string += char_set[Math.floor(Math.random() * char_set.length)];
  }
  return return_string;
}

function messageOutput(msgObj) {
  var return_html = "";
  if (msgObj) {
    if (msgObj["value"] !=  undefined) {
     return_html += "<u>[" + funPrefixes(2) + "]</u> " + msgObj["value"];
    }
  }
  return return_html;
}

function showDash(str_full) {
  var xhttp;
  var str_trim = str_full.trim();
  var str = str_trim.split(" ")[0];
  if (str.length == 0) {
    document.getElementById("dash").innerHTML = "Try help... ";
    return;
  }
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("dash").innerHTML = "<p>";
      var current_time = (new Date()).getTime();
      var jsonresponseobj = JSON.parse(this.responseText).value[0];
      Object.keys(jsonresponseobj).forEach(function(key,index) {
        var array_obj = jsonresponseobj[key];
        var entry_obj = new Object();
        for (var i=0; i < array_obj.length; i++) {
          var keyval_obj = array_obj[i];
          Object.keys(keyval_obj).forEach(function(key,index) {
            Object.keys(keyval_obj).forEach(function(id,idx) {
                entry_obj[id] = keyval_obj[id];
              });
          });
        }
        var entry_output = messageOutput(entry_obj);

        document.getElementById("dash").innerHTML += entry_output + "<br>";
      });
    }
  }
  xhttp.open("POST", "fbbs-api.php", true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send("command="+str);

  var xhttp_dashinfo;
  xhttp_dashinfo = new XMLHttpRequest();
  xhttp_dashinfo.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      var jsonresponsearray = JSON.parse(this.responseText).value;
      for (var i=0; i < jsonresponsearray.length; i++) {
        var keyval_obj = jsonresponsearray[i];
        Object.keys(keyval_obj).forEach(function(key,index) {
          if (key == "value") {
            document.getElementById("board_info").innerHTML = keyval_obj[key];
          }
        });
      }
    }
  }
  xhttp_dashinfo.open("POST", "fbbs-api.php", true);
  xhttp_dashinfo.setRequestHeader("Content-type",
                                  "application/x-www-form-urlencoded");
  xhttp_dashinfo.send("command="+str+" @1");

  document.getElementById("board_name").innerHTML = str;
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

var dashUpdater = setInterval(updateDash, 2000);

</script>

</body>
</html>
