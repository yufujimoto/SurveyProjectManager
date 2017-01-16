<?php
	// Setup default timezone.
	date_default_timezone_set('Asia/Tokyo');
	
	// Start the session and unlock session file.
    session_start();
    session_write_close();
    
	// Check session status.
	if (!isset($_SESSION["USERNAME"])) {
	  header("Location: logout.php");
	  exit;
	}
	
	// Load external libraries.
	require_once "lib/guid.php";
	require_once "lib/config.php";
    require_once "lib/moveTo.php";
    
    // The page return to after the process.
	$returnTo = "project.php";
    
    // Create post data as the array.
	$data = array();
	
	if (!$_REQUEST['prj_nam']) {
        $err = "プロジェクト名は必須項目です。";
        header("Location: add_project.php?err=".$err);
        exit ;
    }
	
	// Get avatar for the prjsolidation.
    $prj_img_nam = $_REQUEST['img_fl'];
    if ($prj_img_nam != "") {
        if (file_exists("uploads/".$prj_img_nam)) {
            $prj_img_fl = "uploads/thumbnail_".$prj_img_nam;
            $prj_img_str = fopen($prj_img_fl,'r');
            $prj_img_obj = fread($prj_img_str,filesize($prj_img_fl));
            $prj_img= pg_escape_bytea($prj_img_obj);
        } else {
			$prj_img_fl = "images/noimage.jpg";
            $prj_img_str = fopen($filename,'r');
            $prj_img_obj = fread($filestream,filesize($filename));
            $prj_img = pg_escape_bytea($data);
		}
	} else {
		$prj_img_fl = "images/noimage.jpg";
		$prj_img_str = fopen($filename,'r');
		$prj_img_obj = fread($filestream,filesize($filename));
		$prj_img = pg_escape_bytea($data);
	}
    
	// Connect to the Database.
    $conn = pg_connect("host=".DBHOST.
                       " port=".DBPORT.
                       " dbname=".DBNAME.
                       " user=".DBUSER.
                       " password=".DBPASS);
    
    // Check the connection status.
	if(!$conn){
		// Get the error message.
		$err = array("err" => "DB Error:".pg_last_error());
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
    $prj_id = "'".GUIDv4()."'";
    $prj_nam = "'".$_REQUEST['prj_nam']."'";
    $prj_ttl = str_replace("''", "NULL", "'".$_REQUEST['prj_ttl']."'");
    $prj_bgn = str_replace("'--'", "NULL", "'".$_REQUEST['prj_bgn']."'");
    $prj_end = str_replace("'--'", "NULL", "'".$_REQUEST['prj_end']."'");
    $prj_phs = $_REQUEST['prj_phs'];
    $prj_int = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['prj_int']))."'");
    $prj_cas = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['prj_cas']))."'");
    $prj_dsc = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['prj_dsc']))."'");
    $prj_cdt = str_replace("''", "NULL", "'".date('Y-m-d G:i:s', time())."'");
    $prj_usr = str_replace("''", "NULL", "'".$_SESSION['USERNAME']."'");
	
	// Insert new record into the project table.
	$sql_ins_prj = "INSERT INTO project (
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
						$prj_id,
						$prj_nam,
						$prj_ttl,
						$prj_bgn,
						$prj_end,
						$prj_phs,
						$prj_int,
						$prj_cas,
						$prj_dsc,
						$prj_cdt,
						$prj_usr,
						'{$prj_img}'
					)";
	try {
        $sql_res_prj = pg_query($conn, $sql_ins_prj);
        
        // Check the result.
        if (!$sql_res_prj) {
            // Get the error message.
			$err = array("err" => "DB Error: ".pg_last_error($conn));
			
			// Close the connection to DB.
			pg_close($conn);
			
			// Return to material page.
			$data = array_merge($data, $err);
			moveToLocal($returnTo, $data);
        }
    } catch (Exception $e) {
		// Get error message
		$err = array("err" => "Caught exception: ".$e);
		
		// Close the connection to DB.
		pg_close($conn);
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
	// Add current user as the first member of the project.
	$sql_sel_mem = "SELECT uuid FROM member WHERE username = ".$prj_usr;
    $sql_res_mem = pg_query($conn, $sql_sel_mem);
	if (!$sql_res_mem) {
		// Get the error message.
		$err = array("err" => "DB Error: ".pg_last_error($conn));
		
		// Close the connection to DB.
		pg_close($conn);
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
    while ($row = pg_fetch_assoc($sql_res_mem)) {
		$mem_id = $row['uuid'];
    }
	
	$rol_uid = "'".GUIDv4()."'";
	$rol_pid = $prj_id;
	$rol_mid = "'".$mem_id."'";
	$rol_frm = $prj_bgn;
	$rol_end = $prj_end;
	
	// Insert new record into the organization table
	$sql_ins_rol = "INSERT INTO role (
							uuid,
							prj_id,
							mem_id,
							beginning,
							ending,
							rolename
						) VALUES (
							$rol_uid,
							$rol_pid,
							$rol_mid,
							$rol_frm,
							$rol_end,
							'Administrator'
						)";
	try {
		// Get the result of the query.
		$sql_res_rol = pg_query($conn, $sql_ins_rol);
		
		 // Check the result.
        if (!$sql_res_rol) {
            // Get the error message.
			$err = array("err" => "DB Error: ".pg_last_error($conn).$sql_ins_rol);
			
			// Close the connection to DB.
			pg_close($conn);
			
			// Return to material page.
			$data = array_merge($data, $err);
			moveToLocal($returnTo, $data);
        }
    } catch (Exception $e) {
		// Get error message
		$err = array("err" => "Caught exception: ".$e);
		
		// Close the connection to DB.
		pg_close($conn);
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	// Close the connection to DB.
    pg_close($conn);
		
	// Return to material page.
	moveToLocal($returnTo, $data);
?>