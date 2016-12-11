<?php
  $ftp_server = "server";
  $ftp_user = "user";
  $ftp_pass = "pass";

  // connection
  $conn_id = ftp_connect($ftp_server) or die("Failed to connect to $ftp_server\n"); 

  // login
  if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
      echo "Logged on $ftp_server under the name $ftp_user\n";
  } else {
      echo "Failed to login as $ftp_user\n";
  }

  // get type system
  if ($type = ftp_systype($conn_id)) {
      echo "$ftp_server uses $type\n";
  } else {
      echo "Unable to determine system type\n";
  }

  // passive mode on
  ftp_pasv($conn_id, true);

  $dir = ""; // enter the path to the folder
  echo "Check the folder: $dir\n";
  $files = get_ftp_files($conn_id, $dir);
  echo $files;

  ftp_close($conn_id);

  function get_ftp_files($conn_id, $dir)
  {
    $file_list = ftp_rawlist($conn_id, $dir);
    if (!empty($file_list)) {
      foreach ($file_list as $file) {
        // splitting a string by whitespace
        list($acc, $bloks, $group, $user, $size, $month, $day, $year, $file) = preg_split("/[\s]+/", $file);

        if ($acc[0] == 'd' && $file != ".." && $file != ".") {
          $dir_in = $dir.$file;
          $global_size = 0; // bytes
          $count = 0;
          list ($global_size1, $count1) = get_ftp_size($conn_id, $dir_in, $global_size, $count);
          $global_size1 = $global_size1 / 1000000;
          echo "The size of the folder $file: \t\t $global_size1 мб; \t The number of files: $count1\n";
        }
      }
    }
  }

  // the function counts the number of bytes occupied by the directory $dir
  function get_ftp_size($conn_id, $dir_in, $global_size, $count)
  {
    $file_list = ftp_rawlist($conn_id, $dir_in);
    if (!empty($file_list)) {      
      foreach ($file_list as $file) {        
        // splitting a string by whitespace
        list($acc, $bloks, $group, $user, $size, $month, $day, $year, $file) = preg_split("/[\s]+/", $file);
        if(in_array(trim($file), array("..", "."))) continue;

        if ($acc[0] == 'd' && $file != ".." && $file != ".") {
          $dir_new = trim($dir_in."/".$file,"/");
          list($global_size, $count) = get_ftp_size($conn_id, $dir_new, $global_size, $count);
        } else {
          $global_size += $size;
          $count += 1;
        }
      }
    }
    return array($global_size, $count);
  }
?>