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
	
	// Query thumbnail with specific uuid.  
	$mat_id = $_REQUEST["uuid"];
	$sql_sel_prj = "SELECT thumbnail FROM digitized_image WHERE mat_id='" .$mat_id."'" ;
	$sql_res_prj = pg_query($sql_sel_prj);
	$sql_obj_prj = pg_fetch_result($sql_res_prj, "thumbnail");
	
	// Close the connection.
	pg_close($conn);
	
	// Display theimage.
	header("Content-type: image/jpeg");
	echo pg_unescape_bytea($sql_obj_prj);
?>