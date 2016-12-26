<?php
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
  $clean_ip = $db->escapeString($_SERVER['REMOTE_ADDR']);
  $user_name_query = 'SELECT ip, value, timestamp FROM "name" WHERE ip = "' .
                     $clean_ip . '" ORDER BY timestamp DESC LIMIT 1';
  $results_name = $db->query($user_name_query);
  if (!empty($results_name)) {
    $name_results_array = $results_name->fetchArray(SQLITE3_ASSOC);
    $ip_name = $name_results_array['value'];
  }
  $previous_command = $_POST['command'];
  $ip = $db->escapeString($_SERVER['REMOTE_ADDR']);
  $exploded_previous_command = explode(" ", $previous_command, 2);
  $arg_count = count($exploded_previous_command);
  if ($arg_count == 1) {
    $table_name = $db->escapeString($exploded_previous_command[0]);
    $query_string = "SELECT id, ip, value, timestamp from " . $table_name .
                    " ORDER BY timestamp DESC LIMIT 20";
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
  }
  if ($arg_count == 2) {
    $table_name = $db->escapeString($exploded_previous_command[0]);
    $value = $db->escapeString($exploded_previous_command[1]);
    if (!empty($value) && $value[0]=='@') {
      $id = $db->escapeString(intval(substr($value, 1)));
      $select_query = 'SELECT id, ip, value, timestamp FROM ' . $table_name .
                      ' WHERE id = ' . substr($value, 1);
      $result = $db->query($select_query);
      if (!empty($result)) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $value = $row['value'];
        if (!empty($value) && ($value[0] == '`')) {
          $exploded_values = explode(" ", substr($value, 1));
          $max_array = count($exploded_values);
          $outputObject->append('"value": [');
          $row = 0;
          for ($i=0; ($i*2) < $max_array; $i++) {
            if ($row > 0) {
              $outputObject->append(',');
            }
            $row++;
            $table_extract_name = $db->escapeString($exploded_values[$i*2]);
            $table_extract_addr = $db->escapeString($exploded_values[($i*2)+1]);
            $select_query = 'SELECT id, ip, value, timestamp FROM ' .
                            $table_extract_name . ' WHERE id = ' .
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
          $outputObject->append(']');
        }
        else {
          $outputObject->append('"value": [');
          $outputObject->append('{"table": "' . $table_name . '"}');
          foreach ($row as $key => $value) {
            $outputObject->append(', ');
            $outputObject->append('{"'. $key . '":"' .$value .'"}');
          }
          $outputObject->append(']');
        }
      }
    }
    else {
      $request_time = $db->escapeString($_SERVER['REQUEST_TIME']);
      $insert_query =  'INSERT INTO ' . $table_name . ' (ip, value, timestamp) ' .
                       'VALUES ("'  . $ip . '", "'. $value . '", "' .
                       $request_time . '")';
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

