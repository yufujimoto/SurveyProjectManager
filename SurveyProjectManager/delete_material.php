<?php
	// Get DB connection information.
	require "lib/config.php";
	
	// Establish the connection to DB.
	$conn = pg_connect("host=".DBHOST."
					   port=".DBPORT."
					   dbname=".DBNAME."
					   user=".DBUSER."
					   password=".DBPASS)
		or die('Connection failed: ' . pg_last_error());
	
	// Get the material id and project id post from previous page.
	$uuid = $_REQUEST['uuid'];
	$con_id = $_REQUEST['con_id'];
	$prj_id= $_REQUEST['prj_id'];
	
	try {
		// Finally Delete the project.
		$sql_del_mat = "DELETE FROM material WHERE uuid='". $uuid."'";
		$res_del_mat = pg_query($conn, $sql_del_mat);
	} catch (Exception $e) {
		// Get error message
		$err = "Caught exception: ".$e;
		
		// Close the connection to DB.
		pg_close($conn);
		
		// Return to report page.
		header("Location: material.php?uuid=".$con_id."&prj_id=".$prj_id."&err=".$err);
	}
	// close the connection to DB.
	pg_close($conn);
	
	// Return to report page.
	header("Location: material.php?uuid=".$con_id."&prj_id=".$prj_id);
?>