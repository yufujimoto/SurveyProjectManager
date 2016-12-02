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
	$mem_uuid = $_REQUEST['mem_uuid'];
	$sql_sel_mem = "SELECT avatar FROM member WHERE uuid='" .$mem_uuid."'" ;
	$res_sel_mem = pg_query($sql_sel_mem);
	$ret_sel_mem = pg_fetch_result($res_sel_mem, 'avatar');
	
	header('Content-type: image/jpeg');
	echo pg_unescape_bytea($ret_sel_mem);
	
	// close the connection to DB.
	pg_close($conn);
?> 