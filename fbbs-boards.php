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

<p>
|||........................|| :::::::::::::
<br>
||| <b>f</b>ury's <b>f</b>ortress (<b>fbbs</b>) || : board :::::
<span id="board_name"></span>

<br>
|||........................|| :::::::::::::
<br>
..last active...<b>[<span id="last_active"><?=$lastauth?></span>]</b>.....

<FORM NAME="form1" METHOD="POST" ACTION="fbbs-boards.php">
    board name:
<?php
  echo '<INPUT TYPE="Text" VALUE="' . $previous_command  . ' " ';
  echo 'id="command" NAME="command" SIZE="20" autofocus>';
?>
    <INPUT TYPE="Submit" Value="|/\enter/\|">
</FORM>
<br>
<FORM NAME="postmsg" METHOD="POST" ACTION="fbbs-boards.php">
  post message=>
<?php
  echo '<INPUT TYPE="Text" VALUE="' . $previous_command . ' [';
  echo $username . '] "';
  echo 'id="message" NAME="command" SIZE="60">';
?>
  <INPUT TYPE="Submit" Value="<-enter|" SIZE="7">
</FORM>
</p>
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
    if (msgObj["id"] != undefined) {
      return_html += "[" + funPrefixes(4) + "] " + msgObj["id"] + " ";
    }
    if (msgObj["value"] !=  undefined) {
      return_html += "[" + funPrefixes(4) + "] <b><u>" + msgObj["value"] +
                     "</u></b>  ";
    }
    if (msgObj["timestamp"] != undefined) {
      var current_time = (new Date()).getTime();
      var dashtime = ((current_time/1000) - msgObj["timestamp"]) / 3600;
      return_html += (Math.trunc((dashtime*1000)))/1000 + " hours ago ";
    }
    if (msgObj["ip"] != undefined) {
      var hashed_ip = "" + msgObj["ip"].hashCode();
      hashed_ip = hashed_ip.replace('-', '>');
      hashed_ip = hashed_ip.replace(/[0-9]/g, function (c) {
          return {
            '0': 'o',
            '1': 'O',
            '2': '.',
            '3': '_',
            '4': '-',
            '5': '=',
            '6': ':',
            '7': '|',
            '8': '^',
            '9': '~'
          }[c]
        });
      return_html += "[" + hashed_ip + "]";
    }
  }
  return return_html;
}

function showDash(str_full) {
  var xhttp;
  var str_trim = str_full.trim();
  var str = str_trim.split(" ")[0];
  if (str.length == 0) {
    document.getElementById("dash").innerHTML = "=-=";
    return;
  }
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("dash").innerHTML = "<p>";
      document.getElementById("board_name").innerHTML =str;
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
