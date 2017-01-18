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
	$returnTo = "main.php";
    
    // Create post data as the array.
	$data = array(
		'mem_id' => $_REQUEST['mem_id'],
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
	$sql_sel_mem = "SELECT 
						A.avatar AS mem_ava,
						A.surname AS mem_snm,
						A.firstname AS mem_fnm,
						A.birthday AS mem_brt,
						A.administrativearea AS mem_adm,
						A.city AS mem_cty,
						A.contact_address AS mem_add,
						A.zipcode AS mem_zip,
						A.email AS mem_eml,
						A.phone AS mem_phn,
						A.mobile_phone AS mem_mbl,
						A.apointment AS mem_apt,
						A.username AS mem_unm,
						A.password AS mem_pwd,
						A.usertype AS mem_uty,
						B.uuid AS org_id,
						B.name AS org_nam,
						B.section AS org_sec,
						B.administrativearea AS org_adm,
						B.city AS org_cty,
						B.contact_address AS org_add,
						B.zipcode AS org_zip,
						B.phone AS org_phn
					FROM member As A LEFT JOIN organization AS B ON A.org_id = B.uuid
					WHERE A.uuid = '" . $_REQUEST["mem_id"] . "'";

    $sql_res_mem = pg_query($conn, $sql_sel_mem);
	if (!$sql_res_mem) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
    while ($mem_row = pg_fetch_assoc($sql_res_mem)) {
		$mem_id = $mem_row['mem_id'];
        $mem_img = $mem_row['mem_ava'];
        $mem_snm = $mem_row['mem_snm'];
		$mem_fnm = $mem_row['mem_fnm'];
		$mem_brt = explode("-", $mem_row['mem_brt']);
		$mem_adm = $mem_row['mem_adm'];
		$mem_cty = $mem_row['mem_cty'];
		$mem_add = $mem_row['mem_add'];
		$mem_zip = $mem_row['mem_zip'];
		$mem_eml = $mem_row['mem_eml'];
		$mem_phn = $mem_row['mem_phn'];
		$mem_mbl = $mem_row['mem_mbl'];
		$mem_apt = $mem_row['mem_apt'];
		$mem_unm = $mem_row['mem_unm'];
		$mem_pwd = $mem_row['mem_pwd'];
		$mem_uty = $mem_row['mem_uty'];
		$org_id =  $mem_row['org_id'];
		$org_nam = $mem_row['org_nam'];
		$org_sec = $mem_row['org_sec'];
		$org_adm = $mem_row['org_adm'];
		$org_cty = $mem_row['org_cty'];
		$org_add = $mem_row['org_add'];
		$org_zip = $mem_row['org_zip'];
		$org_phn = $mem_row['org_phn'];
    }
	
	// Get avatar for the member.
    $mem_img_nam = $_REQUEST['img_fl'];
    if ($mem_img_nam != "") {
        if (file_exists("uploads/".$mem_img_nam)) {
            $mem_img_fl = "uploads/thumbnail_".$mem_img_nam;
            $mem_img_str = fopen($mem_img_fl,'r');
            $mem_img_obj = fread($mem_img_str,filesize($mem_img_fl));
            $mem_img= pg_escape_bytea($mem_img_obj);
        } 
    }
	
	// Parameters for organization.
    $org_nam = str_replace("''", "NULL", "'".$_REQUEST['org_nam']."'");
    $org_sec = str_replace("''", "NULL", "'".$_REQUEST['org_sec']."'");
    $org_zip = str_replace("''", "NULL", "'".$_REQUEST['org_zip']."'");
    $org_adm = str_replace("''", "NULL", "'".$_REQUEST['org_adm']."'");
    $org_cty = str_replace("''", "NULL", "'".$_REQUEST['org_cty']."'");
    $org_add = str_replace("''", "NULL", "'".$_REQUEST['org_add']."'");
    $org_phn = str_replace("''", "NULL", "'".$_REQUEST['org_phn']."'");
	
	// Make the SQL query string.
	$sql_udt_org = "UPDATE organization SET 
						name=$org_nam,
                        section=$org_sec,
                        administrativearea=$org_adm,
                        city=$org_cty,
                        contact_address=$org_add,
                        zipcode=$org_zip,
                        phone=$org_phn
					WHERE uuid='".$org_id."'";
	
	// Parameters for member.
    $mem_id = "'".$_REQUEST["mem_id"]."'";
    $mem_snm = str_replace("''", "NULL", "'".$_REQUEST['mem_snm']."'");
    $mem_fnm = str_replace("''", "NULL", "'".$_REQUEST['mem_fnm']."'");
    $mem_bdy = str_replace("'--'", "NULL", "'".$_REQUEST['mem_bdy']."'");
    $mem_zip = str_replace("''", "NULL", "'".$_REQUEST['mem_zip']."'");
    $mem_cnt = str_replace("''", "NULL", "'日本'");
    $mem_adm = str_replace("''", "NULL", "'".$_REQUEST['mem_adm']."'");
    $mem_cty = str_replace("''", "NULL", "'".$_REQUEST['mem_cty']."'");
    $mem_add = str_replace("''", "NULL", "'".$_REQUEST['mem_add']."'");
    $mem_phn = str_replace("''", "NULL", "'".$_REQUEST['mem_phn']."'");
    $mem_mph = str_replace("''", "NULL", "'".$_REQUEST['mem_mph']."'");
    $mem_eml = str_replace("''", "NULL", "'".$_REQUEST['mem_eml']."'");
    $mem_apt = str_replace("''", "NULL", "'".$_REQUEST['mem_apt']."'");
    $mem_unm = str_replace("''", "NULL", "'".$_REQUEST['mem_unm']."'");
    $mem_typ = str_replace("''", "NULL", "'".$_REQUEST['mem_typ']."'");
	
	$sql_udt_mem = "UPDATE member SET 
						surname=$mem_snm,
                        firstname=$mem_fnm,
						birthday=$mem_bdy,
                        administrativearea=$mem_adm,
                        city=$mem_cty,
                        contact_address=$mem_add,
                        zipcode=$mem_zip,
						email=$mem_eml,
                        phone=$mem_phn,
						mobile_phone=$mem_mph,
						apointment=$mem_apt,
						username=$mem_unm,
						usertype=$mem_typ,
						avatar='{$mem_img}'
					WHERE uuid='".$_REQUEST["mem_id"]."'";
	
	try {
        $sql_res_org = pg_query($conn, $sql_udt_org);
		$sql_res_mem = pg_query($conn, $sql_udt_mem);
		
		// Check the result.
		if (!$sql_res_org || !$sql_res_mem) {
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