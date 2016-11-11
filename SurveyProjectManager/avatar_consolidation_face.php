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
	
	$dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
	if (!$dbconn) {
		echo "An error occurred in DB connection.\n";
		exit;
	}
	$con_uuid = $_GET["uuid"];
	$sql_sel_prj = "SELECT faceimage FROM consolidation WHERE uuid='" .$con_uuid."'" ;
	$res_sel_prj = pg_query($sql_sel_prj);	
	$ret_sel_prj = pg_fetch_result($res_sel_prj, "faceimage");
	
	header("Content-type: image/jpeg");
	echo pg_unescape_bytea($ret_sel_prj);
?>