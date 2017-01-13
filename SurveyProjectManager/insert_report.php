<?php
	// Start session and unlock the session file.
    session_start();
    session_write_close();
	
	// Load external libraries.
	require_once "lib/guid.php";
	require_once "lib/config.php";
	require_once "lib/moveTo.php";
	
	// The page return to after the process.
	$returnTo = "edit_project.php";
	
	// Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id']
	);
	
	// Get the project ids from previous page.
	$prj_id = "'".$_REQUEST['prj_id']."'";
	
    
	// Initialyze the error message.
	$err = "";
	
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
	
    $rep_id = "'".GUIDv4()."'";
	$rep_ttl = str_replace("''","NULL","'".$_REQUEST['rep_nam'.$check]."'");
	$rep_vol = str_replace("''","NULL","'".$_REQUEST['rep_vol'.$check]."'");
	$rep_num = str_replace("''","NULL","'".$_REQUEST['rep_num'.$check]."'");
	$rep_srs = str_replace("''","NULL","'".$_REQUEST['rep_srs'.$check]."'");
	$rep_pub = str_replace("''","NULL","'".$_REQUEST['rep_pub'.$check]."'");
	$rep_yer = str_replace("''","NULL","'".$_REQUEST['rep_yer'.$check]."'");
	$rep_dsc = str_replace("''","NULL","'".$_REQUEST['rep_dsc'.$check]."'");
	
	// Insert new record into the organization table
	$sql_ins_rep = "INSERT INTO report (
						uuid,
						prj_id,
						title,
						volume,
						edition,
						series,
						publisher,
						year,
						descriptions
					) VALUES (
						$rep_id,
						$prj_id,
						$rep_ttl,
						$rep_vol,
						$rep_num,
						$rep_srs,
						$rep_pub,
						$rep_yer,
						$rep_dsc
					)";
	
	try {
		// Get the result of the query.
		$sql_res_rep = pg_query($conn, $sql_ins_rep);
		
		if (!$sql_res_rep) {
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
		
	} catch (Exception $err) {
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