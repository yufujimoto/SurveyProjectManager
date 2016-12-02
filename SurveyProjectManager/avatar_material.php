<?php
    session_start();
    require "lib/config.php";
	
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	if ($_SESSION["USERTYPE"] != "Administrator") {
		header("Location: main.php");
	}
	
	$conn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
	if (!$conn) {
		echo "An error occurred in DB connection.\n";
		exit;
	}
	$mat_uuid = $_REQUEST["uuid"];
	$sql_sel_prj = "SELECT thumbnail FROM digitized_image WHERE mat_id='" .$mat_uuid."'" ;
	$res_sel_prj = pg_query($sql_sel_prj);
	$ret_sel_prj = pg_fetch_row($res_sel_prj, "thumbnail");
	header("Content-type: image/jpeg");
	echo pg_unescape_bytea($ret_sel_prj[0]);
	
	// close the connection to DB.
	pg_close($conn);
?>