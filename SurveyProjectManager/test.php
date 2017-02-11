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
	
	// Get arrays of entries.
	$prj_id = $_REQUEST['prj_id'];
	$rep_id = $_REQUEST['rep_id'];
	$sec_ids = explode(",",$_REQUEST['sec_id']);
	$sec_nams = explode(",",$_REQUEST['sec_nam']);
	$sec_cdts = explode(",",$_REQUEST['sec_cdt']);
	$sec_mdts = explode(",",$_REQUEST['sec_mdt']);
	$sec_mems = explode(",",$_REQUEST['sec_mem']);
	$sec_ords = explode(",",$_REQUEST['sec_ord']);
	
	// Count the number of sections.
	$cnt_sec = count($sec_ids);
	
	for ($i = 0; $i <= $cnt_sec-1; $i++) {
		// Find a uuid of the member who created this entry by username.
		$sql_sel_mem = "SELECT uuid FROM member WHERE username = '" .$sec_mems[$i]. "'";
		$sql_res_mem = pg_query($conn, $sql_sel_mem);
		if (!$sql_res_mem) {
			// Get the error message.
			$err = "DB Error: ".pg_last_error($conn);
			
			// Move to Main Page.
			header("Location: main.php?err=".$err);
			exit;
		}
		while ($row = pg_fetch_assoc($sql_res_mem)) {
			$mem_id = $row['uuid'];
		}
		
		// Insert as new entry.
		$sec_id = str_replace("''","NULL","'".$sec_ids[$i]."'");
		$rep_id = str_replace("''","NULL","'".$_REQUEST['rep_id']."'");
		$mem_id = str_replace("''","NULL","'".$mem_id."'");
		$sec_ord = str_replace("","NULL",$sec_ords[$i]);
		$sec_nam = str_replace("''","NULL","'".$sec_nams[$i]."'");
		$sec_cdt = str_replace("''","NULL","'".$sec_cdts[$i]."'");
		$sec_mdt = str_replace("''","NULL","'".$sec_mdts[$i]."'");
		
		$sql_sel_sec = "SELECT EXISTS(SELECT uuid FROM section WHERE uuid=".$sec_id.")";
		$sql_res_sec = pg_query($conn, $sql_sel_sec);
		if (!$sql_res_sec) {
			// Get the error message.
			$err = "DB Error: ".pg_last_error($conn);
			
			// Move to Main Page.
			header("Location: main.php?err=".$err);
			exit;
		}
		
		$sec_ext = pg_fetch_row($sql_res_sec)[0];
		
		if ($sec_ext=="f"){
			// Insert new record into the project table.
			$sql_ins_sec = "INSERT INTO section (
								uuid,
								rep_id,
								created_by,
								modified_by,
								order_number,
								section_name,
								date_created,
								date_modified
							) VALUES (
								$sec_id,
								$rep_id,
								$mem_id,
								$mem_id,
								$sec_ord,
								$sec_nam,
								$sec_cdt,
								$sec_mdt
							)";
			try{
				$sql_res_sec = pg_query($conn, $sql_ins_sec);
				
				// Check the result.
				if (!$sql_res_sec) {
					// Get the error message.
					$err = array("err" => "DB Error: ".pg_last_error($conn));
					
					// Close the connection to DB.
					pg_close($conn);
					
					// Return to material page.
					$data = array_merge($data, $err);
					moveToLocal($returnTo, $data);
				}
			} catch (Exception $e) {
				// Get error message
				$err = array("err" => "Caught exception: ".$e);
				
				// Close the connection to DB.
				pg_close($conn);
				
				// Return to material page.
				$data = array_merge($data, $err);
				moveToLocal($returnTo, $data);
			}
		} else {
			// Update existng entry.
			$today = date("Y-m-d H:i:s");
			$sec_mdt = str_replace("''","NULL","'".$today."'");
			$sec_ord = str_replace("","NULL",$i);
			
			try{
				// Update existing record.
				$sql_udt_sec = "UPDATE section SET 
									rep_id=$rep_id,
									order_number=$sec_ord,
									section_name=$sec_nam,
									modified_by=$mem_id,
									date_created=$sec_cdt,
									date_modified=$sec_mdt
								WHERE uuid=$sec_id";
								
				$sql_res_sec = pg_query($conn, $sql_udt_sec);
				
				// Check the result.
				if (!$sql_res_sec) {
					// Get the error message.
					$err = array("err" => "DB Error: ".pg_last_error($conn));
					
					// Close the connection to DB.
					pg_close($conn);
					
					// Return to material page.
					$data = array_merge($data, $err);
					moveToLocal($returnTo, $data);
				}
			} catch (Exception $e) {
				// Get error message
				$err = array("err" => "Caught exception: ".$e);
				
				// Close the connection to DB.
				pg_close($conn);
				
				// Return to material page.
				$data = array_merge($data, $err);
				moveToLocal($returnTo, $data);
			}
		}
	}
	// Close the connection.
	pg_close($conn);
	
	// Return to report page.
	moveToLocal($returnTo, $data);
?>