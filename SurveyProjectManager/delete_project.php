<?php
	// Start session and unlock the session file.
    session_start();
    session_write_close();
	
	// Load external libraries.
	require_once "lib/config.php";
	require_once "lib/moveTo.php";
	
	// The page return to after the process.
	$returnTo = "project.php";
	
	// Create post data as the array.
	$data = array();
	
	// Get the project id post from previous page.
	$prj_id = $_REQUEST['prj_id'];
	
	// Establish the connection to DB.
	$conn = pg_connect("host=".DBHOST."
					   port=".DBPORT."
					   dbname=".DBNAME."
					   user=".DBUSER."
					   password=".DBPASS);
	
    // Check the connection status.
	if(!$conn){
		// Get the error message.
		$err = array("err" => "DB Error:".pg_last_error());
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
	try {
		$sql_del_prj = "DELETE FROM project WHERE uuid='". $prj_id."'";
		$sql_res_prj = pg_query($conn, $sql_del_prj);
		
		if (!$sql_res_prj) {
			// Get the error message.
			$err = array("err" => "DB Error: ".pg_last_error($conn));
			
			// Close the connection to DB.
			pg_close($conn);
			
			// Return to material page.
			$data = array_merge($data, $err);
			moveToLocal($returnTo, $data);
		}
		// close the connection to DB.
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
