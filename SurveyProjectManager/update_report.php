<?php
	// Start the session and unlock session file.
	session_cache_limiter("private_no_expire");
    session_start();
    session_write_close();
    
	// Check session status.
	if (!isset($_SESSION["USERNAME"])) {
	  header("Location: logout.php");
	  exit;
	}
	
	// Load external libraries.
	require_once "lib/guid.php";
	require_once "lib/config.php";
    require_once "lib/moveTo.php";
    
    // The page return to after the process.
	$returnTo = "report.php";
    
    // Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id'],
	);
	
    // Initialyze the error message.
	$err = "";
    
	// Connect to the DB.
	$conn = pg_connect(
				"host=".DBHOST.
				" port=".DBPORT.
				" dbname=".DBNAME.
				" user=".DBUSER.
				" password=".DBPASS);
	
	// Check the connection status.
	if(!$conn){
		// Get the error message.
		$err = array("err" => "DB Error:".pg_last_error());
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
	// Get current values.
	// Create a SQL query string.
	$sql_sel_rep = "SELECT * FROM report WHERE uuid = '".$_REQUEST["rep_id"]."'";
	
	// Excute the query and get the result of query.
	$sql_res_rep = pg_query($conn, $sql_sel_rep);
	if (!$sql_res_rep) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
	// Fetch rows of the report.
	while ($rep_row = pg_fetch_assoc($sql_res_rep)) {
		$rep_ttl = $rep_row['title'];
        $rep_vol = $rep_row['volume'];
        $rep_num = $rep_row['edition'];
		$rep_srs = $rep_row['series'];
		$rep_pub = $rep_row['publisher'];
		$rep_yer = $rep_row['year'];
		$rep_dsc = $rep_row['descriptions'];
    }
	
	$rep_ttl = str_replace("''", "NULL", "'".$_REQUEST['rep_ttl']."'");
    $rep_vol = str_replace("''", "NULL", "'".$_REQUEST['rep_vol']."'");
    $rep_num = str_replace("''", "NULL", "'".$_REQUEST['rep_num']."'");
    $rep_srs = str_replace("''", "NULL", "'".$_REQUEST['rep_srs']."'");
    $rep_pub = str_replace("''", "NULL", "'".$_REQUEST['rep_pub']."'");
    $rep_dsc = str_replace("''", "NULL", "'".$_REQUEST['rep_dsc']."'");
	
	// Check the format of the string.
	$regex = '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/';
	if (preg_match($regex, $_REQUEST['rep_yer'])) {
		$rep_yer = "'".$_REQUEST['rep_yer']."'";
	} else {
		$rep_yer = str_replace("''", "NULL", "'".$rep_yer."'");
		$err = array("err" => "The format of the date is not valid: YYYY-MM-DD");
		$data = array_merge($data, $err);
	}
	
	$sql_udt_rep = "UPDATE report SET 
						title=$rep_ttl,
                        volume=$rep_vol,
						edition=$rep_num,
                        series=$rep_srs,
                        publisher=$rep_pub,
                        year=$rep_yer,
                        descriptions=$rep_dsc
					WHERE uuid='".$_REQUEST["rep_id"]."'";
	
	try {
        $sql_res_rep = pg_query($conn, $sql_udt_rep);
		
		// Check the result.
		if (!$sql_res_rep) {
			// Get the error message.
			$err = array("err" => "DB Error: ".pg_last_error($conn));
			
			// Close the connection to DB.
			pg_close($conn);
			
			// Return to material page.
			$data = array_merge($data, $err);
			moveToLocal($returnTo, $data);
		}
		
		// Close the connection.
		pg_close($conn);
        
    } catch (Exception $e) {
        
		// Get error message
		$err = array("err" => "Caught exception: ".$e);
		
		// Close the connection to DB.
		pg_close($conn);
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
	// Return to material page.
	moveToLocal($returnTo, $data);
?>