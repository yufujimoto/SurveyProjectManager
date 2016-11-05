<?php
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
    
    require 'lib/guid.php';
    require "lib/config.php";
    
    // Connect to the Database.
    $dbconn = pg_connect("host=".DBHOST." port=5432 dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
    
    if (!$_POST['name']) {
        $err = "プロジェクト名が空白です。";
        header("Location: add_project.php?err=".$err);
        exit ;
    }
    
    date_default_timezone_set('Asia/Tokyo');
    
    $uuid = "'".GUIDv4()."'";
    $name = "'".$_POST['name']."'";
    $title = str_replace("''", "NULL", "'".$_POST['title']."'");
    $begin = str_replace("'--'", "NULL", "'".$_POST['date_from']."'");
    $end = str_replace("'--'", "NULL", "'".$_POST['date_to']."'");
    $phase = $_POST['phase'];
    $intro = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_POST['intro']))."'");
    $cause = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_POST['cause']))."'");
    $desc = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_POST['desc']))."'");
    $now = str_replace("''", "NULL", "'".date('Y-m-d G:i:s', time())."'");
    $user = str_replace("''", "NULL", "'".$_SESSION['USERNAME']."'");
    
    try{
        // Insert new record into the project table.
        $sql_inssert = "INSERT INTO project (
                            uuid,
                            name,
                            title,
                            beginning,
                            ending,
                            phase,
                            introduction,
                            cause,
                            descriptions,
                            created,
                            created_by
                        ) VALUES (
                            $uuid,
                            $name,
                            $title,
                            $begin,
                            $end,
                            $phase,
                            $intro,
                            $cause,
                            $desc,
                            $now,
                            $user
                        )";
        $sql_result = pg_query($dbconn, $sql_inssert);
        
        // Check the result.
        if (!$sql_result) {
            // Get the error message.
            $err = pg_last_error($dbconn);
            
            // Back to projects page.
            header("Location: project.php?err=".$err);
        }
    } catch (Exception $err) {
        // Get the error message.
        $err->getMessage();
        
        // Back to projects page.
        header("Location: project.php?err=".$err);
    }
    
    // Back to projects page without error messages.
    header("Location: project.php");
?>
