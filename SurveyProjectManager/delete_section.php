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
	$returnTo = "report.php";
    
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
	
	// Get the secdtion id and project id post from previous page.
	$sec_id = $_REQUEST['sec_id'];
	$prj_id = $_REQUEST['prj_id'];
	
	$sql_del_sec = "DELETE FROM section WHERE uuid='". $sec_id."'";
	
	try {
		$sql_res_sec = pg_query($conn, $sql_del_sec);
		// Check the result.
		if (!$sql_res_sec) {
			// Get the error message.
			$err = array("err" => "DB Error: ".pg_last_error($conn));
			
			// Close the connection to DB.
			pg_close($conn);
			
			// Return to material page.
			$data = array_merge($data, $err);
			moveToLocal($returnTo, $data);
		}
		// Close the connection to DB.
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
	
	// Return to report page.
	moveToLocal($returnTo, $data);
?>