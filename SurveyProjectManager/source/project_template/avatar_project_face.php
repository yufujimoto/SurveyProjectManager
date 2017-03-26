<?php
    require "lib/config.php";
	
	$conn = pg_connect("host=".DBHOST." port=5432 dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
	if (!$conn) {
		echo "An error occurred in DB connection.\n";
		exit;
	}
	$prj_uuid = $_GET['uuid'];
	$sql_sel_prj = "SELECT faceimage FROM project WHERE uuid='" .$prj_uuid."'" ;
	$res_sel_prj = pg_query($sql_sel_prj);	
	$ret_sel_prj = pg_fetch_result($res_sel_prj, 'faceimage');
	
	header('Content-type: image/jpeg');
	echo pg_unescape_bytea($ret_sel_prj);
	
	// Close the connection to the database.
	pg_close($conn);
?>