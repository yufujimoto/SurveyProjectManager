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
	$returnTo = "consolidation.php";
    
    // Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id']
	);
    
	// Initialyze the error message.
	$err = "";
	
    // Connect to the Database.
    $conn = pg_connect("host=".DBHOST.
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
    
    // Create a SQL query string.
	$sql_sel_con = "SELECT
        prj_id,
		name,
		faceimage,
		geographic_name,
		geographic_extent,
		represented_point,
		estimated_area,
		estimated_period_beginning,
		estimated_period_ending,
		descriptions
		FROM consolidation WHERE uuid = '".$_REQUEST['con_id']."'";
	
	// Excute the query and get the result of query.
	$sql_res_con = pg_query($conn, $sql_sel_con);
	if (!$sql_res_con) {
		// Print the error messages and exit routine if error occors.
		echo "An error occurred in DB query.\n";
		exit;
	}
    
	while ($con_row = pg_fetch_assoc($sql_res_con)) {
        $prj_id = "'".$con_row['prj_id']."'";
		$con_nam = "'".$con_row['name']."'";
		$con_img = $con_row['faceimage'];
		$con_add = str_replace("''", "NULL", "'".$con_row['geographic_name']."'");
		$con_ext = str_replace("''", "NULL", "'".$con_row['geographic_extent']."'");
		$con_rep = str_replace("''", "NULL", "'".$con_row['represented_point']."'");
		$con_est = str_replace("''", "NULL", "'".$con_row['estimated_area']."'");
		$con_bgn = str_replace("''", "NULL", "'".$con_row['estimated_period_beginning']."'");
		$con_end = str_replace("''", "NULL", "'".$con_row['estimated_period_ending']."'");
		$con_dsc = str_replace("''", "NULL", "'".$con_row['descriptions']."'");
    }
    
    // Get avatar for the consolidation.
    $con_img_nam = $_REQUEST['img_fl'];
    if ($con_img_nam != "") {
        if (file_exists("uploads/".$con_img_nam)) {
            $con_img_fl = "uploads/thumbnail_".$con_img_nam;
            $con_img_str = fopen($con_img_fl,'r');
            $con_img_obj = fread($con_img_str,filesize($con_img_fl));
            $con_img= pg_escape_bytea($con_img_obj);
        } 
    }
    
    // Get geographic extent for the consolidation.
    $con_ext_nam=$_REQUEST['con_ext'];
    if ($con_ext_nam != "") {
        if (file_exists("uploads/".$con_ext_nam)) {
            $con_ext_fl = "uploads/".$con_ext_nam;
            $con_ext = file_get_contents($con_ext_fl);
            $con_ext = "ST_GeomFromEWKT('SRID=".SRID.";".$con_ext."')";
        }
    }
    
    // Get estimated geographic extent for the consolidation.
    $con_est_nam=$_REQUEST['con_est'];
    if ($con_est_nam != "") {
        if (file_exists("uploads/".$con_est_nam)) {
            $con_est_fl = "uploads/".$con_est_nam;
            $con_est = file_get_contents($con_est_fl);
            $con_est = "ST_GeomFromEWKT('SRID=".SRID.";".$con_est."')";
        }
    }
    
    $con_lat = $_REQUEST['con_lat'];
    $con_lon = $_REQUEST['con_lon'];
    if ($con_lat!=""){
        if ($con_lon!=""){
            $con_rep = "ST_SetSRID(ST_MakePoint($con_lon, $con_lat), ".SRID.")";
        }
    }
    
    // Initialyze the error message.
    $prj_id = "'".$_REQUEST['prj_id']."'";
    $con_id = "'".$_REQUEST['con_id']."'";
    $con_nam = "'".$_REQUEST['con_nam']."'";
    $con_bgn = str_replace("''", "NULL", "'".$_REQUEST['con_bgn']."'");
    $con_end = str_replace("''", "NULL", "'".$_REQUEST['con_end']."'");
    $con_dsc = str_replace("''", "NULL", "'".nl2br(htmlspecialchars($_REQUEST['con_dsc']))."'");
    $con_add = str_replace("''", "NULL", "'".$_REQUEST['con_add']."'");
    
	// Update existing record.
	$sql_udt_con = "UPDATE consolidation SET 
						prj_id=$prj_id,
						name=$con_nam,
						faceimage='{$con_img}',
						geographic_name=$con_add,
						geographic_extent=$con_ext,
						represented_point=$con_rep,
						estimated_area=$con_est,
						estimated_period_beginning=$con_bgn,
						estimated_period_ending=$con_end,
						descriptions=$con_dsc 
					WHERE uuid='".$_REQUEST['con_id']."'";
	
    try {
		$sql_res_con = pg_query($conn, $sql_udt_con);
		
		// Check the result.
		if (!$sql_res_con) {
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