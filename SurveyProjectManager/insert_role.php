<?php
    require 'lib/guid.php';
    require "lib/password.php";
    require "lib/config.php";
    
    // Connect to the Database.
    $dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
    
    $rol_pid = "'".$_REQUEST['prj_uuid']."'";
    if(!empty($_POST['prj_mem'])) {
        foreach($_POST['prj_mem'] as $check) {
            $rol_uid = "'".GUIDv4()."'";
            $rol_mid = "'".$check."'";
            $rol_frm = str_replace("''","NULL","'".$_POST['from_'.$check]."'");
            $rol_end = str_replace("''","NULL","'".$_POST['to_'.$check]."'");
            
            try {
                // Insert new record into the organization table
                $sql_rol_inssert = "INSERT INTO role (
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
                
                $sql_rol_result = pg_query($dbconn, $sql_rol_inssert);
                
                // Check the result.
                if (!$sql_rol_result) {
                    $err = pg_last_error($dbconn);
                    pg_close($dbconn);
                    
                    // Back to member add page.
                    header("Location: edit_project.php?uuid=".$_REQUEST['prj_uuid']);
                }
            } catch (Exception $err) {
                $err->getMessage();
                pg_close($dbconn);
                
                // Back to member add page.
                header("Location: edit_project.php?uuid=".$_REQUEST['prj_uuid']);
            }
        }
    }
    pg_close($dbconn);
    
	// Move to login page.
    header("Location: edit_project.php?uuid=".$_REQUEST['prj_uuid']);
?>