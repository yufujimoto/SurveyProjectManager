<?php
	// Start the session and unlock session file.
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
	$returnTo = "material.php";
    
    // Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id'],
		'con_id' => $_REQUEST['con_id'],
		'mat_id' => $_REQUEST['mat_id']
	);
	
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
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
    
	// Initialyze the error message.
	$err = "";
	$prj_id = "'".$_REQUEST['prj_id']."'";
	$con_id = "'".$_REQUEST['con_id']."'";
	$mat_id = "'".$_REQUEST['mat_id']."'";
	$mat_bsc_inf_key = explode(",",$_REQUEST['bsc_mat_keys']);
	$mat_bsc_inf_val = explode(",",$_REQUEST['bsc_mat_vals']);
	$mat_add_inf_ids = explode(",",$_REQUEST['add_inf_ids']);
	$mat_add_inf_key = explode(",",$_REQUEST['add_inf_keys']);
	$mat_add_inf_val = explode(",",$_REQUEST['add_inf_vals']);
	
	try {
		// Get basic information about the material.
		for($i=0; $i < count($mat_bsc_inf_key);$i++){
			if($mat_bsc_inf_key[$i]==="mat_num"){
				$mat_num = str_replace("''", "NULL", "'".$mat_bsc_inf_val[$i]."'");
			} elseif($mat_bsc_inf_key[$i]==="mat_nam"){
				$mat_nam = str_replace("''", "NULL", "'".$mat_bsc_inf_val[$i]."'");
			} elseif($mat_bsc_inf_key[$i]==="mat_bgn"){
				$mat_bgn = str_replace("''", "NULL", "'".$mat_bsc_inf_val[$i]."'");
			} elseif($mat_bsc_inf_key[$i]==="mat_end"){
				$mat_end = str_replace("''", "NULL", "'".$mat_bsc_inf_val[$i]."'");
			} elseif($mat_bsc_inf_key[$i]==="mat_dsc"){
				$mat_dsc = str_replace("''", "NULL", "'".$mat_bsc_inf_val[$i]."'");
			}
		}
		
		// Get additional user specified information about the material.
		for($i=0; $i < count($mat_add_inf_ids);$i++){
			$add_id = str_replace("''", "NULL", "'".$mat_add_inf_ids[$i]."'");
			$add_key = str_replace("''", "NULL", "'".$mat_add_inf_key[$i]."'");
			$add_val = str_replace("''", "NULL", "'".$mat_add_inf_val[$i]."'");
			
			
			// Make the SQL query.
			$sql_udt_add= "UPDATE additional_information SET 
							key=$add_key,
							value=$add_val
						WHERE uuid=$add_id";
			$sql_res_add = pg_query($conn, $sql_udt_add);
			
			// Check the result.
			if (!$sql_res_add) {
				// Get the error message.
				$err = array("err" => "DB Error: ".pg_last_error($conn));
				
				// Close the connection to DB.
				pg_close($conn);
				
				// Return to material page.
				$data = array_merge($data, $err);
				moveToLocal($returnTo, $data);
			}
		}
		
		// Update existing record.
		$sql_udt_mat = "UPDATE material SET 
							name=$mat_nam,
							estimated_period_beginning=$mat_bgn,
							estimated_period_ending=$mat_end,
							material_number=$mat_num,
							descriptions=$mat_dsc
						WHERE uuid=$mat_id";
		$sql_res_mat = pg_query($conn, $sql_udt_mat);
		
		// Check the result.
		if (!$sql_res_mat) {
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