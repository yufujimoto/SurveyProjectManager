<?php
	// Start the session.
    session_start();
	
	// Load external libraries.
	require "lib/guid.php";
    require "lib/config.php";
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	$prj_id = $_REQUEST['uuid'];
	
	// Generate unique ID for saving temporal files.
	$tmp_name = uniqid($_SESSION["USERNAME"]."_");
    $csv_file = "uploads/".$tmp_name.".csv";
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta name="Yu Fujimoto" content="" />
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
		<link href="../theme.css" rel="stylesheet" />
		
		<!-- Import external scripts for Bootstrap CSS -->
		<script src="lib/jquery-3.1.1/jquery.min.js"></script>
		
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/bootstrap.min.js"></script>
	</head>
    
    <?php
        // Connect to the DB.
        $conn = pg_connect(
                    "host=".DBHOST.
                    " port=".DBPORT.
                    " dbname=".DBNAME.
                    " user=".DBUSER.
                    " password=".DBPASS)
                or die('Connection failed: ' . pg_last_error());
        
        // Check uploads and parse the uploaded CSV file.
        if (is_uploaded_file($_FILES["input_csv"]["tmp_name"])) {
            // Check the validity of the uploaded file and move the file to upload directory.
            if (move_uploaded_file($_FILES["input_csv"]["tmp_name"], $csv_file)){
                // change the permission of the file.
                chmod($csv_file, 0777);
                echo "<p>CSV file is now uploading...</p>";
                
                // Initialize variables.
                $cnt_rows = 1;
                $len_cols = 0;
                $headers = array();
                $rel_from = array();
                $rel_to = array();
                
                // Open the uploaded file as file stream.
                if (($handle = fopen($csv_file, "r")) !== FALSE) {
                    // Reload parent frame.
                    echo "<p>Now Uploading... Please wait for a minutes. It may take long time.</p>";
                
                    // Read a line of the uploaded csv file. Maximum length of the line is limitted to 10,000.
                    echo "<table class='table table-striped'>";
                    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                        // Initialyze a array for columns values.
                        $line = array();
                        $cstm_name = array();
                        $cstm_item = array();
                
                        // Escape the header line (first row).
                        if ($cnt_rows == 1){
                            $len_cols = count($data);    // Get the length of the columns.
                            for ($col=0; $col < $len_cols; $col++) {
                                array_push($headers, $data[$col]);
                                
                                if ($data[$col]=="name"){$nam = $col; echo "<th style='text-align:center'>name</th>";}
                                elseif ($data[$col]=="estimated_period_beginning"){$bgn = $col; echo "<th style='text-align:center'>from</th>";}
                                elseif ($data[$col]=="estimated_period_ending"){$end = $col; echo "<th style='text-align:center'>to</th>";}
                                elseif ($data[$col]=="descriptions"){$dsc = $col; echo "<th style='text-align:center'>descriptions</th>";}
                                elseif ($data[$col]=="material_number"){$mid = $col; echo "<th style='text-align:center'>material_number</th>";}
                                elseif ($data[$col]=="material_to_material"){$m2m = $col; echo "<th style='text-align:center'>relation</th>";}
                                elseif ($data[$col]=="latitude"){$lat = $col; echo "<th style='text-align:center'>latitude</th>";}
                                elseif ($data[$col]=="longitude"){$lon = $col; echo "<th style='text-align:center'>longitude</th>";}
                                else {echo "<th>".$data[$col]."</th>";}
                            }
                        } else {
                            echo "<tr>";
                            // Get each column and set the value to the array.
                            for ($col=0; $col < $len_cols; $col++){
                                if ($col == $nam){$name = $data[0]; echo "<td style='text-align:center;'>$name</td>";}
                                elseif ($col == $bgn){$est_bgn = $data[$col]; echo "<td style='text-align:center;'>$est_bgn</td>";}
                                elseif ($col == $end){$est_end = $data[$col]; echo "<td style='text-align:center;'>$est_end</td>";}
                                elseif ($col == $dsc){$descriptions = $data[$col]; echo "<td style='text-align:left;'>$descriptions</td>";}
                                elseif ($col == $mid){$material_number = $data[$col]; echo "<td style='text-align:center;'>$material_number</td>";}
                                elseif ($col == $m2m){$material2material = $data[$col]; echo "<td style='text-align:center;'>$material2material</td>";}
                                elseif ($col == $lat){$latitude = $data[$col]; echo "<td style='text-align:center;'>$latitude</td>";}
                                elseif ($col == $lon){$longitude = $data[$col]; echo "<td style='text-align:center;'>$longitude</td>";}
                                
                                else {
                                    array_push($cstm_name, $headers[$col]);
                                    array_push($cstm_item, $data[$col]);
                                    echo "<td style='text-align:center;'>".$data[$col]."</td>";
                                }
                            }
                        }
                        echo "</tr>";
                        // Add relationships to the list.
                        array_push($rel_from, $material_number);
                        array_push($rel_to, $material2material);
                        
                        
                        
                        
                        //==========================================
                        //            Insert Materials
                        //==========================================
                        
                        // Define the entry to insert.
                        $mat_uuid = "'".GUIDv4()."'";
                        $conn_id = "'".$_REQUEST["uuid"]."'";
                        $mat_neme = str_replace("''", "NULL", "'".$name."'");
                        $mat_est_bgn = str_replace("''", "NULL", "'".$est_bgn."'");
                        $mat_est_end = str_replace("''", "NULL", "'".$est_end."'");
                        $mat_dsc = str_replace("''", "NULL", "'".$descriptions."'");
                        $mat_num = str_replace("''", "NULL", "'".$material_number."'");
                        if ($latitude!=""){
                            if ($longitude!=""){
                                $mat_rep = "ST_SetSRID(ST_MakePoint($longitude, $latitude), ".SRID.")";
                            } else {
                                $mat_rep = "NULL";
                            }
                        } else {
                            $mat_rep = "NULL";
                        }
                        
                        // Parse locational information if exists.
                        if ($lat!="NULL" and $lon!="NULL"){
                            $rep_pt = "ST_SetSRID(ST_MakePoint($lat, $lon), ".SRID.")";
                        } else {
                            $rep_pt = "NULL";
                        }
                        
                        // Insert a material to the DBMS.
                        try{
                            // Insert new record into the project table.
                            $sql_inssert_mat = "INSERT INTO material (
                                                uuid,
                                                con_id,
                                                name,
                                                estimated_period_beginning,
                                                estimated_period_ending,
                                                represented_point,
                                                material_number,
                                                descriptions
                                            ) VALUES (
                                                $mat_uuid,
                                                $conn_id,
                                                $mat_neme,
                                                $mat_est_bgn,
                                                $mat_est_end,
                                                $mat_rep,
                                                $mat_num,
                                                $mat_dsc
                                            )";
                            $sql_res_mat = pg_query($conn, $sql_inssert_mat);
                            // Check the result.
                            if (!$sql_res_mat) {
                                // Get the error message.
                                $err = pg_last_error($conn);
                            }
                            
                            //==========================================
                            //          Additional Information
                            //==========================================
                            // Insert additional attributes into the table.
                            $cstm_length = count($cstm_name);
                            for ($cstm_cnt=0; $cstm_cnt < $cstm_length; $cstm_cnt++) {
                                $add_uuid = "'".GUIDv4()."'";
                                $add_key = str_replace("''", "NULL", "'".$cstm_name[$cstm_cnt]."'");
                                $add_val = str_replace("''", "NULL", "'".$cstm_item[$cstm_cnt]."'");
                                $add_typ = "'"."character varying(255)"."'";
                                
                                // Insert new record into the project table.
                                $sql_inssert_add = "INSERT INTO additional_information (
                                                    uuid,
                                                    mat_id,
                                                    key,
                                                    value,
                                                    type
                                                ) VALUES (
                                                    $add_uuid,
                                                    $mat_uuid,
                                                    $add_key,
                                                    $add_val,
                                                    $add_typ
                                                )";
                                $sql_res_add = pg_query($conn, $sql_inssert_add);
                                
                                // Check the result.
                                if (!$sql_res_add) {
                                    // Get the error message.
                                    $err = pg_last_error($conn);
                                    echo "ERROR:".$sql_inssert_add."</br>";
                                }
                            }
                        } catch (Exception $err) {
                            // Get the error message.
                            $err->getMessage();
                        }
                        // Increment the row count.
                        $cnt_rows ++;
                    }
                    echo "<table>";
                    
                    //==========================================
                    //   Insert Material to Material Relation
                    //==========================================
                    
                    // Get the number of relationships.
                    $rel_length = count($rel_from);
                    
                    for ($rel_cnt=0; $rel_cnt < $rel_length; $rel_cnt++) {
                        // Search a material uuid that is related from.
                        $sql_sel_mat_from = "SELECT uuid FROM material WHERE material_number = '" . $rel_from[$rel_cnt] . "'";
                        $sql_res_mat_from = pg_query($conn, $sql_sel_mat_from) or die('Query failed: ' . pg_last_error());
                        
                        // Get the material relating from.
                        while ($mat_row_from = pg_fetch_assoc($sql_res_mat_from)) {
                            $rel_from_uuid = $mat_row_from['uuid'];
                        }              
                        
                        // Get the material related to.
                        $rel_dest = explode(":", $rel_to[$rel_cnt]);
                        foreach ($rel_dest as $relto){
                            $sql_sel_mat_to = "SELECT uuid FROM material WHERE material_number = '" . $relto . "'";
                            $sql_res_mat_to = pg_query($conn, $sql_sel_mat_to) or die('Query failed: ' . pg_last_error());
                            while ($mat_row_to = pg_fetch_assoc($sql_res_mat_to)) {
                                $rel_to_uuid = $mat_row_to['uuid'];
                            }
                            
                            // Define the entry to insert.
                            $rel_uuid = "'".GUIDv4()."'";
                            $relating_from = str_replace("''", "NULL", "'".$rel_from_uuid."'");
                            $relating_to = str_replace("''", "NULL", "'".$rel_to_uuid."'");
                            
                            try{
                                // Insert new record into the project table.
                                $sql_inssert_mat2mat = "INSERT INTO material_to_material (
                                                    uuid,
                                                    relating_from,
                                                    relating_to
                                                ) VALUES (
                                                    $rel_uuid,
                                                    $relating_from,
                                                    $relating_to
                                                )";
                                $sql_res_mat2mat = pg_query($conn, $sql_inssert_mat2mat);
                                
                                // Check the result.
                                if (!$sql_res_mat2mat) {
                                    // Get the error message.
                                    $err = pg_last_error($conn);
                                    echo "ERROR in REL:".$rel_from_uuid."|".$rel_to_uuid."|".$err."</br>";
                                }
                            } catch (Exception $err) {
                                    // Get the error message.
                                    $err->getMessage();
                                    echo "ERROR:".$err;
                            }
                        }
                    }
                        
                    // Close the CSV file stream.
                    fclose($handle);
                    
                    // Close the connetion to the DB
                    pg_close($conn);
                }
            }
        }
    ?>