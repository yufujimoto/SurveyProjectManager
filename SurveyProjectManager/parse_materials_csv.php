<html lang="ja">
    <head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
	    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	    <meta http-equiv="Content-Script-Type" content="text/javascript">
	    <meta http-equiv="Content-Style-Type" content="text/css">
	    <meta name="description" content="">
	    <meta name="Yu Fujimoto" content="">
	    <link rel="icon" href="../favicon.ico">
	    <title>プロジェクトの管理</title>
	    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
	    <link href="../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
	    <link href="../theme.css" rel="stylesheet">
    </head>
<?php
	header("Content-Type: text/html; charset=UTF-8");
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
    
    require "lib/guid.php";
    require "lib/config.php";
    
    // Connect to the Database.
    $dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
    
    $uuid = uniqid($_SESSION["USERNAME"]."_");
	$uploadedfile = "uploads/".$uuid.".csv";
	
	// Check uploads and parse the uploaded CSV file.
	if (is_uploaded_file($_FILES["csv_members"]["tmp_name"])) {
		// Check the validity of the uploaded file and move the file to upload directory.
		if (move_uploaded_file($_FILES["csv_members"]["tmp_name"], $uploadedfile)){
			// change the permission of the file.
			chmod($uploadedfile, 0777);
			
			// Initialize the row counter.
			$count_row = 1;
            $length = 0;
            
            $headers = array();
            $rel_from = array();
            $rel_to = array();
            $cstm_name = array();
            $cstm_item = array();
            
			// Open the uploaded file as file stream.
			if (($handle = fopen($uploadedfile, "r")) !== FALSE) {
				// Read a line of the uploaded csv file. Maximum length of the line is limitted to 5,000.
                echo "<table class='table table-striped'>";
				while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
					$line = array();		// Initialyze a array for columns values.

                    
					// Escape the header line (first row).
                    if ($count_row == 1){
                        $length = count($data);	// Get the length of the columns.
                        for ($column=0; $column < $length; $column++) {
							array_push($headers, $data[$column]);
                            
                            if ($data[$column]=="name"){$nam = $column; echo "<th style='text-align:center'>name</th>";}
                            elseif ($data[$column]=="estimated_period_beginning"){$bgn = $column; echo "<th style='text-align:center'>from</th>";}
                            elseif ($data[$column]=="estimated_period_ending"){$end = $column; echo "<th style='text-align:center'>to</th>";}
                            elseif ($data[$column]=="descriptions"){$dsc = $column; echo "<th style='text-align:center'>descriptions</th>";}
                            elseif ($data[$column]=="material_number"){$mid = $column; echo "<th style='text-align:center'>material_number</th>";}
                            elseif ($data[$column]=="material_to_material"){$m2m = $column; echo "<th style='text-align:center'>relation</th>";}
                            else {echo "<th>".$data[$column]."</th>";}
                        }
                    } else {
                        echo "<tr>";
						// Get each column and set the value to the array.
						for ($column=0; $column < $length; $column++){
                            if ($column == $nam){$name = $data[0]; echo "<td style='text-align:center;'>$name</td>";}
                            elseif ($column == $bgn){$est_bgn = $data[$column]; echo "<td style='text-align:center;'>$est_bgn</td>";}
                            elseif ($column == $end){$est_end = $data[$column]; echo "<td style='text-align:center;'>$est_end</td>";}
                            elseif ($column == $dsc){$descriptions = $data[$column]; echo "<td style='text-align:left;'>$descriptions</td>";}
                            elseif ($column == $mid){$material_number = $data[$column]; echo "<td style='text-align:center;'>$material_number</td>";}
                            elseif ($column == $m2m){$material2material = $data[$column]; echo "<td style='text-align:center;'>$material2material</td>";}
                            else {
                                array_push($cstm_name, $headers[$column]);
                                array_push($cstm_item, $data[$column]);
                                echo "<td style='text-align:center;'>".$data[$column]."</td>";
                            }
						}
					}
					echo "</tr>";
                    
                    array_push($rel_from, $material_number);
                    array_push($rel_to, $material2material);
                    
                    $mat_uuid = "'".GUIDv4()."'";
                    $con_id = "'".$_REQUEST["uuid"]."'";
                    $mat_neme = str_replace("''", "NULL", "'".$name."'");
                    $mat_est_bgn = str_replace("''", "NULL", "'".$est_bgn."'");
                    $mat_est_end = str_replace("''", "NULL", "'".$est_end."'");
                    $mat_dsc = str_replace("''", "NULL", "'".$descriptions."'");
                    $mat_num = str_replace("''", "NULL", "'".$material_number."'");
                    
                    if ($lat!="NULL" and $lon!="NULL"){
                        $reppt = "ST_SetSRID(ST_MakePoint($lat, $lon), ".SRID.")";
                    } else {
                        $reppt = "NULL";
                    }
                    
                    try{
                        // Insert new record into the project table.
                        $sql_inssert = "INSERT INTO material (
                                            uuid,
                                            con_id,
                                            name,
                                            estimated_period_beginning,
                                            estimated_period_ending,
                                            material_number,
                                            descriptions
                                        ) VALUES (
                                            $mat_uuid,
                                            $con_id,
                                            $mat_neme,
                                            $mat_est_bgn,
                                            $mat_est_end,
                                            $mat_num,
                                            $mat_dsc
                                        )";
                        $sql_result = pg_query($dbconn, $sql_inssert);
                        
                        // Check the result.
                        if (!$sql_result) {
                            // Get the error message.
                            $err = pg_last_error($dbconn);
                        }
                    } catch (Exception $err) {
                        // Get the error message.
                        $err->getMessage();
                    }
					// Increment the row count.
					$count_row ++;
				}
				echo "<table>";
                
                $rel_length = count($rel_from);
                for ($rel_cnt=0; $rel_cnt < $rel_length; $rel_cnt++) {
                    
                    $mat_query_from = "SELECT uuid FROM material WHERE material_number = '" . $rel_from[$rel_cnt] . "'";
                    $mat_result_from = pg_query($dbconn, $mat_query_from) or die('Query failed: ' . pg_last_error());
                    while ($mat_row_from = pg_fetch_assoc($mat_result_from)) {
                        $rel_from_uuid = $mat_row_from['uuid'];
                    }              
                    
                    $rel_to_split = explode(":", $rel_to[$rel_cnt]);
                    foreach ($rel_to_split as $relto){
                        $mat_query_to = "SELECT uuid FROM material WHERE material_number = '" . $relto . "'";
                        $mat_result_to = pg_query($dbconn, $mat_query_to) or die('Query failed: ' . pg_last_error());
                        while ($mat_row_to = pg_fetch_assoc($mat_result_to)) {
                            $rel_to_uuid = $mat_row_to['uuid'];
                        }
                        
                        $rel_uuid = "'".GUIDv4()."'";
                        $relating_from = str_replace("''", "NULL", "'".$rel_from_uuid."'");
                        $relating_to = str_replace("''", "NULL", "'".$rel_to_uuid."'");
                        
                        try{
                            // Insert new record into the project table.
                            $sql_inssert = "INSERT INTO material_to_material (
                                                uuid,
                                                relating_from,
                                                relating_to
                                            ) VALUES (
                                                $rel_uuid,
                                                $relating_from,
                                                $relating_to
                                            )";
                            $sql_result = pg_query($dbconn, $sql_inssert);
                            
                            // Check the result.
                            if (!$sql_result) {
                                // Get the error message.
                                $err = pg_last_error($dbconn);
                                echo $sql_inssert;
                            }
                        } catch (Exception $err) {
                                // Get the error message.
                                $err->getMessage();
                                echo $sql_inssert;
                        }
                    }
                }
				// Close the CSV file stream.
				fclose($handle);
				
				// Close the connetion to the DB
				pg_close($dbconn);
			}
		}
	} else {
		echo "<p>CSV file is not uploaded...</p>";
	}
?>