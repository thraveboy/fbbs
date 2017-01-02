<?php

  function isSysOpQ() {
     $sysopResult = False;
     if (!empty($_COOKIE['username'])) {
       if ($_COOKIE['username'] == 'SysOp') {
         $sysopResult = True;
       }
     }
     return $sysopResult;
  }

  class jsonEncoder
  {
    private $_output_string = "";

    public function append($output) {
      $this->_output_string .= $output;
    }
    public function retrieve() {
      return '{' . $this->_output_string . '}';
    }
    public function send() {
      echo $this->retrieve();
    }
  }

  $outputObject = new jsonEncoder();

  class FDB extends SQLite3
  {
    function __construct()
    {
      $this->open('fbbs.db');
    }
  }

  $db = new FDB();
  if(!$db){
    echo $db->lastErrorMsg();
  }
  $previous_command = trim($_POST['command']);
  $ip = $db->escapeString($_SERVER['REMOTE_ADDR']);
  $exploded_previous_command = explode(" ", $previous_command, 3);
  $arg_count = count($exploded_previous_command);
  $retrieved_value = FALSE;
  if (($arg_count == 1) ||
      (($arg_count == 2) && ($exploded_previous_command[1] == '@'))) {
    $table_name = $db->escapeString($exploded_previous_command[0]);
    $order_type = "DESC";
    $max_limit = "20";
    $order_column = "timestamp";
    if ($arg_count == 2) {
      $order_type = "ASC";
      $max_limit = 5;
      $order_column = "id";
    }
    $query_string = "SELECT id, ip, value, timestamp from " . $table_name .
                    " ORDER BY  ".  $order_column. "  ". $order_type .
                    " LIMIT ". $max_limit;
    $results = $db->query($query_string);
    if (!empty($results)) {
      $outputObject->append('"value":[{');
      $row_num = 0;
      while ($row_results = $results->fetchArray(SQLITE3_ASSOC)) {
        if ($row_num > 0) {
          $outputObject->append(',');
        }
        $outputObject->append('"value ' . $row_num . '":[');
        $col_num = 0;
        foreach ($row_results as $key => $value) {
          if ($col_num > 0) {
            $outputObject->append(',');
          }
          $outputObject->append('{"' . $key . '": "' . $value . '"}');
          $col_num++;
        }
        $outputObject->append(']');
        $row_num++;
      }
      $outputObject->append('}]');
    }
    else {
      $table_create_query = "CREATE TABLE " . $table_name .
                            " (id INTEGER PRIMARY KEY ASC, ip TEXT," .
                            "value TEXT, timestamp INTEGER)";
      $db->exec($table_create_query);
    }
    $retrieved_value = TRUE;
  }

  $sysopRequest = isSysOpQ();

  if (($arg_count > 1) && (!$retrieved_value)) {
    $table_name = $db->escapeString($exploded_previous_command[0]);
    $value = $db->escapeString($exploded_previous_command[1]);
    if (!empty($value) && (($value[0]=='@') && ($arg_count==2))) {
      $id = intval($db->escapeString(substr($value, 1)));
      $select_query = 'SELECT id, ip, value, timestamp FROM ' . $table_name .
                      ' WHERE id = ' . $id;
      $result = $db->query($select_query);
      if (!empty($result)) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $value = $row['value'];
        if (!empty($value) && ($value[0] == '`')) {
          $exploded_values = explode(" ", substr($value, 1));
          $max_array = count($exploded_values);
          $outputObject->append('{"value": [{"value_0": [');
          $row = 0;
          for ($i=0; ($i*2) < $max_array; $i++) {
            if ($row > 0) {
              $outputObject->append(',');
            }
            $row++;
            $table_extract_name = $db->escapeString($exploded_values[$i*2]);
            $table_extract_addr = $db->escapeString($exploded_values[($i*2)+1]);
            $select_query = "SELECT id, ip, value, timestamp FROM " .
                            $table_extract_name . " WHERE id = " .
                            $table_extract_addr;

            $result = $db->query($select_query);
            if (!empty($result)) {
              $outputObject->append('{"table": "' . $table_extract_name .
                                    '"}');
              foreach ($result->fetchArray(SQLITE3_ASSOC) as $key => $value) {
                $outputObject->append(', ');
                $outputObject->append('{"'. $key . '":"' .$value .'"}');
                $row++;
              }
            }
          }
          $outputObject->append(']}]}');
        }
        else {
          $outputObject->append('"value": {"values": [');
          $outputObject->append('{"table": "' . $table_name . '"}');
          foreach ($row as $key => $value) {
            $outputObject->append(', ');
            $outputObject->append('{"'. $key . '":"' .$value .'"}');
          }
          $outputObject->append(']}');
        }
      }
    }
    else {
      $value .=  " " . $db->escapeString($exploded_previous_command[2]);
      $update_val = FALSE;
      $update_location = -1;
      if ($arg_count > 2 && $sysopRequest) {
        if ($value[0] == '@') {
          $update_val = TRUE;
          $update_location =
            substr($db->escapeString($exploded_previous_command[1]), 1);
          $value = $db->escapeString($exploded_previous_command[2]);
        }
      }

      $request_time = $db->escapeString($_SERVER['REQUEST_TIME']);
      if (!$update_val) {
        $insert_query =  'INSERT INTO ' . $table_name .
                         ' (ip, value, timestamp) ' .
                         'VALUES ("'  . $ip . '", "'. $value . '", "' .
                         $request_time . '")';
      }
      else {
        $insert_query =  'UPDATE '. $table_name . ' SET ' .
                         ' ip = "' . $ip . '", value = "'. $value .
                         '", timestamp = "' . $request_time . '" ' .
                         'WHERE id = ' . $update_location;
      }
      $db->exec($insert_query);
      $insert_id = $db->lastInsertRowid();
      $outputObject->append('"value":[{');
      $outputObject->append('"retrieve": "' . $table_name .
                            ' @' . $insert_id . '"');
      $outputObject->append('}]');
    }
  }
  if (!$_LOCAL_API_CALLS) {
    $outputObject->send();
  }
?>

