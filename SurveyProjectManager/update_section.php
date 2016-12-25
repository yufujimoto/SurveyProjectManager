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
	
	
	// Count the number of sections.
	$cnt_sec = count(explode(",",$_REQUEST['sec_uid']));
	
	// Get arrays of entries.
	$sec_uids = explode(",",$_REQUEST['sec_uid']);
	$sec_nams = explode(",",$_REQUEST['sec_nam']);
	$sec_cdts = explode(",",$_REQUEST['sec_cdt']);
	$sec_mdts = explode(",",$_REQUEST['sec_mdt']);
	$sec_mems = explode(",",$_REQUEST['sec_mem']);
	$sec_ords = explode(",",$_REQUEST['sec_ord']);
	
	if(!empty($_REQUEST['rep_id'])) {
		for ($i = 0; $i <= $cnt_sec; $i++) {
			echo $i;
			// Find a uuid of the member who created this entry by username.
			$sql_sel_mem = "SELECT uuid FROM member WHERE username = '" .$sec_mems[$i]. "'";
			$sql_res_mem = pg_query($conn, $sql_sel_mem) or die('Query failed: ' . pg_last_error());
			while ($row = pg_fetch_assoc($sql_res_mem)) {
				$mem_id = $row['uuid'];
			}
			
			// Insert as new entry.
			$uuid = str_replace("''","NULL","'".$sec_uids[$i]."'");
			$rep_id = str_replace("''","NULL","'".$_REQUEST['rep_id']."'");
			$mem = str_replace("''","NULL","'".$mem_id."'");
			$ord = str_replace("","NULL",$sec_ords[$i]);
			$nam = str_replace("''","NULL","'".$sec_nams[$i]."'");
			$cdt = str_replace("''","NULL","'".$sec_cdts[$i]."'");
			$mdt = str_replace("''","NULL","'".$sec_mdts[$i]."'");
			
			// Select the section.
			$sql_sel_sec = "SELECT * FROM section where uuid='".$sec_uids[$i]."'";
			$sql_res_sec = pg_query($conn, $sql_sel_sec);
			
			if (!$sql_res_sec) {
				// Fail to get the result.
				
			} else {
				// Fetch rows of projects. 
				$rows_sec = pg_fetch_all($sql_res_sec);
				$row_cnt_sec = 0 + intval(pg_num_rows($sql_res_sec));
				
				if ($row_cnt_sec == 0){
					// Insert a new entry.
					try{
						// Insert new record into the project table.
						$sql_inssert = "INSERT INTO section (
											uuid,
											rep_id,
											created_by,
											modified_by,
											order_number,
											section_name,
											date_created,
											date_modified
										) VALUES (
											$uuid,
											$rep_id,
											$mem,
											$mem,
											$ord,
											$nam,
											$cdt,
											$mdt
										)";
						
						$sql_res = pg_query($conn, $sql_inssert);
						// Check the result.
						if (!$sql_res) {
							// Get the error message.
							$err = pg_last_error($conn);
							
							// close the connection to DB.
							pg_close($conn);
							
							// Back to report page.
							header("Location: report.php?uuid=".$_REQUEST['prj_id']."&err=".$err);
						}
					} catch (Exception $err) {
						// Get the error message.
						$err->getMessage();
						
						// close the connection to DB.
						pg_close($conn);
						
						// Back to projects page.
						header("Location: report.php?uuid=".$_REQUEST['prj_id']."&err=".$err);
					}
				} else {
					// Update existng entry.
					$ord = str_replace("","NULL",$i);
					$today = date("Y-m-d H:i:s");
					$mdt = str_replace("''","NULL","'".$today."'");
					
					try{
						// Update existing record.
						$sql_update = "UPDATE section SET 
											rep_id=$rep_id,
											order_number=$ord,
											section_name=$nam,
											created_by=$mem,
											modified_by=$mem,
											date_created=$cdt,
											date_modified=$mdt,
										WHERE uuid=$uuid";
										
						$sql_res = pg_query($conn, $sql_update);
						
						// Check the result.
						if (!$sql_res) {
							// Get the error message.
							$err = pg_last_error($conn);
							
							// close the connection to DB.
							pg_close($conn);
							
							// Back to projects page.
							header("Location: report.php?uuid=".$_REQUEST['prj_id']."&err=".$err);
						}
					} catch (Exception $err) {
						// Get the error message.
						$err->getMessage();
						
						// close the connection to DB.
						pg_close($conn);
						
						// Back to projects page.
						header("Location: report.php?uuid=".$_REQUEST['prj_id']."&err=".$err);
					}
				}
			}
		}
		pg_close($conn);
		
		// Back to report page without error messages.
		header("Location: report.php?uuid=".$_REQUEST['prj_id']);
	}
?>