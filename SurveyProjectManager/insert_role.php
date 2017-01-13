<?php
	// Start session and unlock the session file.
    session_start();
    session_write_close();
	
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
    
    $rol_pid = "'".$_REQUEST['prj_uuid']."'";
    if(!empty($_REQUEST['prj_mem'])) {
        foreach($_REQUEST['prj_mem'] as $check) {
            $rol_uid = "'".GUIDv4()."'";
            $rol_mid = "'".$check."'";
            $rol_frm = str_replace("''","NULL","'".$_REQUEST['from_'.$check]."'");
            $rol_end = str_replace("''","NULL","'".$_REQUEST['to_'.$check]."'");
            
            try {
                // Insert new record into the organization table
                $sql_inssert_rol = "INSERT INTO role (
                                    uuid,
                                    prj_id,
                                    mem_id,
                                    beginning,
                                    ending
                                ) VALUES (
                                    $rol_uid,
                                    $rol_pid,
                                    $rol_mid,
                                    $rol_frm,
                                    $rol_end
                                )";
                // Get the result of the query.
                $sql_res_rol = pg_query($conn, $sql_inssert_rol);
                
                // Check the result.
                if (!$sql_res_rol) {
					// Get the error message.
                    $err = pg_last_error($conn);
                    pg_close($conn);
                    
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
    }
	// Close the connection.
    pg_close($conn);
    
	// Move to login page.
    header("Location: edit_project.php?uuid=".$_REQUEST['prj_uuid']);
?>