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
    
    // Get avatar for the consolidation.
    $con_img_nam = $_REQUEST['img_fl'];
    if ($con_img_nam != "") {
        if (file_exists("uploads/".$con_img_nam)) {
            $con_img_fl = "uploads/thumbnail_".$con_img_nam;
            $con_img_str = fopen($con_img_fl,'r');
            $con_img_obj = fread($con_img_str,filesize($con_img_fl));
            $con_img= pg_escape_bytea($con_img_obj);
        } else {
            $con_img_fl = "images/noimage.jpg";
            $con_img_str = fopen($con_img_fl,'r');
            $con_img_obj = fread($con_img_str,filesize($con_img_fl));
            $con_img= pg_escape_bytea($con_img_obj);
        }
    } else {
        $con_img_fl = "images/noimage.jpg";
        $con_img_str = fopen($con_img_fl,'r');
        $con_img_obj = fread($con_img_str,filesize($con_img_fl));
        $con_img= pg_escape_bytea($con_img_obj);
    }
    
    // Get geographic extent for the consolidation.
    $con_ext="NULL";
    $con_ext_nam=$_REQUEST['con_ext'];
    if ($con_ext_nam != "") {
        if (file_exists("uploads/".$con_ext_nam)) {
            $con_ext_fl = "uploads/".$con_ext_nam;
            $con_ext = file_get_contents($con_ext_fl);
            $con_ext = "ST_GeomFromEWKT('SRID=".SRID.";".$con_ext."')";
        }
    }
    
    // Get estimated geographic extent for the consolidation.
    $con_est="NULL";
    $con_est_nam=$_REQUEST['con_est'];
    if ($con_est_nam != "") {
        if (file_exists("uploads/".$con_est_nam)) {
            $con_est_fl = "uploads/".$con_est_nam;
            $con_est = file_get_contents($con_est_fl);
            $con_est = "ST_GeomFromEWKT('SRID=".SRID.";".$con_est."')";
        }
    }
    
    $con_rep = "NULL";
    $con_lat = $_REQUEST['con_lat'];
    $con_lon = $_REQUEST['con_lon'];
    if ($con_lat!=""){
        if ($con_lon!=""){
            $con_rep = "ST_SetSRID(ST_MakePoint($con_lon, $con_lat), ".SRID.")";
        }
    }
    
    // Initialyze the error message.
	$err = "";
    $prj_id = "'".$_REQUEST['prj_id']."'";
    $con_id = "'".GUIDv4()."'";
    $con_nam = "'".$_REQUEST['con_nam']."'";
    $con_bgn = str_replace("''", "NULL", "'".$_REQUEST['con_bgn']."'");
    $con_end = str_replace("''", "NULL", "'".$_REQUEST['con_end']."'");
    $con_dsc = str_replace("''", "NULL", "'".$_REQUEST['con_dsc']."'");
    $con_add = str_replace("''", "NULL", "'".$_REQUEST['con_add']."'");
    
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
    
	$sql_ins_con = "INSERT INTO consolidation (
						uuid,
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
					) VALUES (
						$con_id,
						$prj_id,
						$con_nam,
						'{$con_img}',
						$con_add,
						$con_ext,
						$con_rep,
						$con_est,
						$con_bgn,
						$con_end,
						$con_dsc
					)";
	
    try {
        $sql_res_con = pg_query($conn, $sql_ins_con);
        
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
		// Close the connection to DB.
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