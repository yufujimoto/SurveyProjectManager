<?php
	// Start session and unlock the session file.
    session_start();
    session_write_close();
	
	// Load external libraries.
	require_once "lib/config.php";
	require_once "lib/moveTo.php";
	
	// The page return to after the process.
	$returnTo = "edit_project.php";
	
	// Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id']
	);
	
	// Get the project ids from previous page.
	$prj_id = $_REQUEST['prj_id'];
	$rep_id = $_REQUEST['rep_id'];
	
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
	
	// Make a SQL query.
	$sql_del_rep = "DELETE FROM report WHERE uuid='". $rep_id."'";
	
	try {
		// Finally Delete the report.
		$sql_res_rep = pg_query($conn, $sql_del_rep);
		
		if (!$sql_res_rep) {
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