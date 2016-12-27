<?php
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get DB connection information.
	require "lib/config.php";
	
	// Establish the connection to DB.
	$conn = pg_connect("host=".DBHOST.
					   " port=".DBPORT.
					   " dbname=".DBNAME.
					   " user=".DBUSER.
					   " password=".DBPASS)
			or die('Connection failed: ' . pg_last_error());
	
	if (!$conn) {
		echo "An error occurred in DB connection.\n";
		exit;
	}
	
	// Get the project id post from previous page.
	$prj_id = $_REQUEST['prj_id'];
	$uuid = $_REQUEST['uuid'];
	
	try {
		// Finally Delete the report.
		$sql_delete_report = "DELETE FROM report WHERE uuid='". $uuid."'";
		$sql_res_report = pg_query($conn, $sql_delete_report);
	} catch (Exception $e) {
		$err = "Caught exception: ";
		header("Location: edit_project.php?uuid=".$prj_id."&err=".$err);
	}
	// close the connection to DB.
	pg_close($conn);
	
	// Return to home.
	header("Location: edit_project.php?uuid=".$prj_id);
?>