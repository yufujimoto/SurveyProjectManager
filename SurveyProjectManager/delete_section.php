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
	
	// Get the secdtion id and project id post from previous page.
	$uuid = $_REQUEST['uuid'];
	$prj_id= $_REQUEST['prj_id'];
	
	try {
		// Finally Delete the project.
		$sql_del_sec = "DELETE FROM section WHERE uuid='". $uuid."'";
		$res_del_sec = pg_query($conn, $sql_del_sec);
	} catch (Exception $e) {
		$err = "Caught exception: ".$e;
		
		// Close the connection to DB.
		pg_close($conn);
		
		// Return to report page.
		header("Location: report.php?uuid=".$prj_id);
	}
	// Close the connection to DB.
	pg_close($conn);
	
	// Return to report page.
	header("Location: report.php?uuid=".$prj_id);
?>