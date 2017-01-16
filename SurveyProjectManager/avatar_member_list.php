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
	$mem_id = $_REQUEST['uuid'];
	$sql_sel_mem = "SELECT avatar FROM member WHERE uuid='" .$mem_id."'" ;
	$sql_sel_mem = pg_query($sql_sel_mem);
	$sql_obj_mem = pg_fetch_result($sql_sel_mem, 'avatar');
	
	// Close the connection.
	pg_close($conn);
	
	// Display theimage.
	header('Content-type: image/jpeg');
	echo pg_unescape_bytea($sql_obj_mem);
?> 