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
	
	// Query image with specific uuid.  
	$img_id = $_REQUEST["uuid"];
	$img_typ = $_REQUEST["type"];
	if ($img_typ==="original"){
		$sql_sel_img = "SELECT image FROM digitized_image WHERE uuid='".$img_id."'" ;
		$sql_res_img = pg_query($sql_sel_img);	
		$sql_obj_img = pg_fetch_result($sql_res_img, "image");
	} else {
		$sql_sel_img = "SELECT thumbnail FROM digitized_image WHERE uuid='".$img_id."'" ;
		$sql_res_img = pg_query($sql_sel_img);	
		$sql_obj_img = pg_fetch_result($sql_res_img, "thumbnail");
	}
	
	
	// Close the connection.
	pg_close($conn);
	
	// Display theimage.
	header("Content-type: image/jpeg");
	echo pg_unescape_bytea($sql_obj_img);
?>