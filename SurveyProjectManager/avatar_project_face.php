<?php
    session_start();
    require "lib/config.php";
	
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	// Establish the connection.
	$conn = pg_connect("host=".DBHOST.
					   " port=".DBPORT.
					   " dbname=".DBNAME.
					   " user=".DBUSER.
					   " password=".DBPASS);
	
	// Check connection status.
	if (!$conn) {
		echo "An error occurred in DB connection.\n";
		exit;
	}
	
	// Query face image with specific uuid. 
	$prj_uuid = $_REQUEST["uuid"];
	$sql_sel_prj = "SELECT faceimage FROM project WHERE uuid='" .$prj_uuid."'" ;
	$sql_res_prj = pg_query($sql_sel_prj);	
	$obj_sel_prj = pg_fetch_result($sql_res_prj, "faceimage");
	
	// Close the connection.
	pg_close($conn);
	
	// Display theimage.
	header("Content-type: image/jpeg");
	echo pg_unescape_bytea($obj_sel_prj);
?>