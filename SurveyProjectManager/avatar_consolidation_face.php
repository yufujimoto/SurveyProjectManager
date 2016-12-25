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
	$con_id = $_REQUEST["uuid"];
	$sql_sel_con = "SELECT faceimage FROM consolidation WHERE uuid='" .$con_id."'" ;
	$sql_res_con = pg_query($sql_sel_con);	
	$sql_obj_con = pg_fetch_result($sql_res_con, "faceimage");
	
	// Close the connection.
	pg_close($conn);
	
	// Display theimage.
	header("Content-type: image/jpeg");
	echo pg_unescape_bytea($sql_obj_con);
?>