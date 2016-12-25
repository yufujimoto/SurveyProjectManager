<?php
    require "lib/guid.php";
    require "lib/password.php";
    require "lib/config.php";
    
	// Initialyze the error message.
	$err = "";
	
    // Connect to the Database.
    $conn = pg_connect(
				"host=".DBHOST.
				" port=".DBPORT.
				" dbname=".DBNAME.
				" user=".DBUSER.
				" password=".DBPASS
			) or die('Connection failed: ' . pg_last_error());
    
    $rep_pid = "'".$_REQUEST['prj_uuid']."'";
    if(!empty($rep_pid)) {
		$rep_uid = "'".GUIDv4()."'";
		$rep_ttl = str_replace("''","NULL","'".$_REQUEST['rep_nam'.$check]."'");
		$rep_vol = str_replace("''","NULL","'".$_REQUEST['rep_vol'.$check]."'");
		$rep_num = str_replace("''","NULL","'".$_REQUEST['rep_num'.$check]."'");
		$rep_srs = str_replace("''","NULL","'".$_REQUEST['rep_srs'.$check]."'");
		$rep_pub = str_replace("''","NULL","'".$_REQUEST['rep_pub'.$check]."'");
		$rep_yer = str_replace("''","NULL","'".$_REQUEST['rep_yer'.$check]."'");
		$rep_dsc = str_replace("''","NULL","'".$_REQUEST['rep_dsc'.$check]."'");
		
		try {
			// Insert new record into the organization table
			$sql_inssert_rep = "INSERT INTO report (
								uuid,
								prj_id,
								title,
								volume,
								edition,
								series,
								publisher,
								year,
								descriptions
							) VALUES (
								$rep_uid,
								$rep_pid,
								$rep_ttl,
								$rep_vol,
								$rep_num,
								$rep_srs,
								$rep_pub,
								$rep_yer,
								$rep_dsc
							)";
			// Get the result of the query.
			$sql_res_rep = pg_query($conn, $sql_inssert_rep);
			
			// Check the result.
			if (!$sql_res_rep) {
				// Get the error message.
				$err = pg_last_error($conn);
				pg_close($conn);
				
				echo $sql_inssert_rep;
				
				// Back to member add page.
				header("Location: edit_project.php?uuid=".$_REQUEST['prj_uuid']."&err=".$err);
			}
		} catch (Exception $err) {
			// Get the error message.
			$err -> getMessage();
			pg_close($conn);
			
			// Back to member add page.
			header("Location: edit_project.php?uuid=".$_REQUEST['prj_uuid']."&err=".$err);
		}
    }
	// Close the connection.
    pg_close($conn);
    
	// Move to login page.
    header("Location: edit_project.php?uuid=".$_REQUEST['prj_uuid']);
?>