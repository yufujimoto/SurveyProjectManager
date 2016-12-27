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
    $conn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
    
    if (!$_REQUEST['name']) {
        $err = "プロジェクト名が空白です。";
        header("Location: add_project.php?err=".$err);
        exit ;
    }
    
    // Get avatar.
    $prj_faceimage = $_REQUEST['prj_fimg'];
	if ($prj_faceimage != "") {
		if (file_exists("uploads/".$prj_faceimage)) {
			$filename = "uploads/thumbnail_".$prj_faceimage;
			$filestream = fopen($filename,'r');
			$data = fread($filestream,filesize($filename));
			$escaped= pg_escape_bytea($data);
            
            
			
		} else {
            $filename = "images/noimage.jpg";
            $filestream = fopen($filename,'r');
            $data = fread($filestream,filesize($filename));
            $escaped= pg_escape_bytea($data);
        }
	} else {
		$filename = "images/noimage.jpg";
		$filestream = fopen($filename,'r');
		$data = fread($filestream,filesize($filename));
		$escaped= pg_escape_bytea($data); 
	}
    
    date_default_timezone_set('Asia/Tokyo');
    
    $uuid = "'".GUIDv4()."'";
    $name = "'".$_REQUEST['name']."'";
    $title = str_replace("''", "NULL", "'".$_REQUEST['title']."'");
    $begin = str_replace("'--'", "NULL", "'".$_REQUEST['date_from']."'");
    $end = str_replace("'--'", "NULL", "'".$_REQUEST['date_to']."'");
    $phase = $_REQUEST['phase'];
    $intro = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['intro']))."'");
    $cause = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['cause']))."'");
    $desc = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['desc']))."'");
    $now = str_replace("''", "NULL", "'".date('Y-m-d G:i:s', time())."'");
    $user = str_replace("''", "NULL", "'".$_SESSION['USERNAME']."'");
    $faceimage = $escaped;
    
    try{
        // Insert new record into the project table.
        $sql_inssert_prj = "INSERT INTO project (
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
                            created_by,
                            faceimage
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
                            $user,
                            '{$faceimage}'
                        )";
        $sql_result_prj = pg_query($conn, $sql_inssert_prj);
        
        // Check the result.
        if (!$sql_result_prj) {
            // Get the error message.
            $err = pg_last_error($conn);
			
			// close the connection to DB.
			pg_close($conn);
            
            // Back to projects page.
            header("Location: project.php?err=".$err);
        }
    } catch (Exception $err) {
        // Get the error message.
        $err->getMessage();
		
		// close the connection to DB.
		pg_close($conn);
        
        // Back to projects page.
        header("Location: project.php?err=".$err);
    }
    
    // Back to projects page without error messages.
    header("Location: project.php");
	
	// close the connection to DB.
	pg_close($conn);
?>
