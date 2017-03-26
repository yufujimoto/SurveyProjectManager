<?php
    require "lib/config.php";
	
	$conn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
	if (!$conn) {
		echo "An error occurred in DB connection.\n";
		exit;
	}
	$mat_uuid = $_GET["uuid"];
	$sql_sel_prj = "SELECT thumbnail FROM digitized_image WHERE mat_id='" .$mat_uuid."'" ;
	$res_sel_prj = pg_query($sql_sel_prj);
	$ret_sel_prj = pg_fetch_row($res_sel_prj, "thumbnail");
	header("Content-type: image/jpeg");
	echo pg_unescape_bytea($ret_sel_prj[0]);
	
	// Close the connection to the database.
	pg_close($conn);
?>