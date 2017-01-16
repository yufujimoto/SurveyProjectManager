<?php
	// Start session and unlock the session file.
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
	
	// Initialyze the error message.
	$err = "";
	
	// The page return to after the process.
	$returnTo = "edit_project.php";
    
    // Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id'],
	);
	
    // Initialyze the error message.
	$err = "";
	
	// Check member name.
	if(!$_REQUEST['prj_mem']) {
    // Get the error message.
		moveToLocal($returnTo, $data);
	}
	
	echo $_REQUEST['prj_mem'];

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
	
    $rol_pid = "'".$_REQUEST['prj_id']."'";
	foreach($_REQUEST['prj_mem'] as $check) {
		$rol_uid = "'".GUIDv4()."'";
		$rol_mid = "'".$check."'";
		$rol_frm = str_replace("''","NULL","'".$_REQUEST['from_'.$check]."'");
		$rol_end = str_replace("''","NULL","'".$_REQUEST['to_'.$check]."'");
		
		// Insert new record into the organization table
		$sql_inssert_rol = "INSERT INTO role (
							uuid,
							prj_id,
							mem_id,
							beginning,
							ending
						) VALUES (
							$rol_uid,
							$rol_pid,
							$rol_mid,
							$rol_frm,
							$rol_end
						)";
						
		try {
			// Get the result of the query.
			$sql_res_rol = pg_query($conn, $sql_inssert_rol);
			
			// Check the result.
			if (!$sql_res_rol) {
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
	}
		
	// Close the connection.
    pg_close($conn);
    
	
	// Return to material page.
	moveToLocal($returnTo, $data);
?>