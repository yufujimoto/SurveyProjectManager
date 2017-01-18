<?php
	// Start the session and unlock session file.
	session_cache_limiter("private_no_expire");
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
	$returnTo = "main.php";
    
    // Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id'],
	);
	
    // Initialyze the error message.
	$err = "";
    
	// Connect to the DB.
	$conn = pg_connect(
				"host=".DBHOST.
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
    
    // Find the project.
	$sql_sel_prj = "SELECT * FROM project WHERE uuid = '".$_REQUEST['prj_id']."'";
    $sql_res_prj = pg_query($conn, $sql_sel_prj);
	if (!$sql_res_prj) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
    while ($prj_row = pg_fetch_assoc($sql_res_prj)) {
		$prj_id = $prj_row['uuid'];
        $prj_nam = $prj_row['name'];
        $prj_ttl = $prj_row['title'];
		$prj_bgn = $prj_row['beginning'];
		$prj_end = $prj_row['ending'];
		$prj_phs = $prj_row['phase'];
		$prj_int = $prj_row['introduction'];
		$prj_cas = $prj_row['cause'];
		$prj_dsc = $prj_row['descriptions'];
		$prj_img = $prj_row['faceimage'];
    }
    
    // Get avatar for the project.
    $prj_img_nam = $_REQUEST['img_fl'];
    if ($prj_img_nam != "") {
        if (file_exists("uploads/".$prj_img_nam)) {
            $prj_img_fl = "uploads/thumbnail_".$prj_img_nam;
            $prj_img_str = fopen($prj_img_fl,'r');
            $prj_img_obj = fread($prj_img_str,filesize($prj_img_fl));
            $prj_img= pg_escape_bytea($prj_img_obj);
        } 
    }
    
    $prj_nam = "'".$_REQUEST['prj_nam']."'";
    $prj_ttl = str_replace("''", "NULL", "'".$_REQUEST['prj_ttl']."'");
    $prj_bgn = str_replace("'--'", "NULL", "'".$_REQUEST['prj_bgn']."'");
    $prj_end = str_replace("'--'", "NULL", "'".$_REQUEST['prj_end']."'");
    $prj_phs = $_REQUEST['prj_phs'];
    $prj_int = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['prj_int']))."'");
    $prj_cas = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['prj_cas']))."'");
    $prj_dsc = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['prj_dsc']))."'");
    
    // Update existing record.
	$sql_udt_prj = "UPDATE project SET 
						name=$prj_nam,
                        title=$prj_ttl,
                        beginning=$prj_bgn,
                        ending=$prj_end,
                        phase=$prj_phs,
                        introduction=$prj_int,
                        cause=$prj_cas,
                        descriptions=$prj_dsc,
						faceimage='{$prj_img}'
					WHERE uuid='".$_REQUEST['prj_id']."'";
    try {
        $sql_res_prj = pg_query($conn, $sql_udt_prj);
		
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
		
		// Close the connection.
		pg_close($conn);
        
    } catch (Exception $e) {
        
		// Get error message
		$err = array("err" => "Caught exception: ".$e);
		
		// Close the connection to DB.
		pg_close($conn);
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
    
	// Return to material page.
	moveToLocal($returnTo, $data);
?>