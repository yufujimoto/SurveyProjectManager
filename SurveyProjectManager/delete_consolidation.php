<?php
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get DB connection information.
	require "lib/config.php";
	
	// Establish the connection to DB.
	$conn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
	if (!$conn) {
		echo "An error occurred in DB connection.\n";
		exit;
	}
	
	// Get the project id post from previous page.
	$uuid = $_REQUEST['uuid'];
	$prj_id = $_REQUEST['prj_id'];
	
	try {
		// Finally Delete the project.
		$sql_del_prj = "DELETE FROM consolidation WHERE uuid='". $uuid."'";
		$res_del_prj = pg_query($conn, $sql_del_prj);
	} catch (Exception $e) {
		$err = 'Caught exception: ';
		header("Location: consolidation.php?uuid=$prj_id");
	}
	// close the connection to DB.
	pg_close($conn);
	
	// Return to home.
	header("Location: consolidation.php?uuid=$prj_id");
?>