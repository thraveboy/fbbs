<!DOCTYPE html>
<html>
<head>
<style>
body, input {
    font-family: monospace;
    font-size: xx-small;
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
  require 'fbbs-api.php';

  $previous_command = $_GET['command'].split(" ")[0];
  echo '<div id="previous_command" hidden>';
  print($previous_command);
  echo '</div>';
?>

<p>
____
<br>
||||\
<br>
|||||\
<br>
******\
<br>
*Fury's\\
<br>
*Fortress\
<br>
*fbbs  *//
<br>
*******//
<br>
 ===========
<br>
 -=======---
<br>
 ----===---
<br>
 -----==-----

<p>
<FORM NAME="form1" METHOD="GET" ACTION="fbbs-dash.php">
    <INPUT TYPE="Text" VALUE="" id="command" NAME="command" SIZE="80" autofocus>
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

function showDash(str_full) {
  var xhttp;
  var str = str_full.split(" ")[0];
  if (str.length == 0) {
    document.getElementById("dash").innerHTML = "=-=";
    return;
  }
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("dash").innerHTML = "<p>" +
                      ":::::::::::::<br>" +
                      ": board :::::<b> " + str + "</b><br>" +
                      ":::::::::::::<br></p>";
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
               case 'ip':
                 output_val = '[' + dash_val.hashCode() + '] ';
                 break;
               case 'id':
                 output_val = '(@' + dash_val + ')';
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
