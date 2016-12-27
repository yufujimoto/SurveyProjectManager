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
	
    // Insert as new entry.
    $prj_id = $_REQUEST['prj_id'];
    $con_id = $_REQUEST['con_id'];
    $mat_id = $_REQUEST['mat_id'];
    $dmg_uid = str_replace("''","NULL","'".$_REQUEST['uuid']."'");
    $dmg_dsc = str_replace("''","NULL","'".$_REQUEST['dsc']."'");
    
    try{
        // Update existing record.
        $sql_update_dmg = "UPDATE digitized_image SET 
                            descriptions=$dmg_dsc
                        WHERE uuid=$dmg_uid";
           
        $sql_result_dmg = pg_query($conn, $sql_update_dmg);
        
        // Check the result.
        if (!$sql_result_dmg) {
            // Get the error message.
            $err = pg_last_error($conn);
            
            // close the connection to DB.
            pg_close($conn);
            
            // Back to projects page.
            echo "a";
            //header("Location: update_material.php?uuid=".$mat_id."&prj_id=".$prj_id."&con_id=".$con_id."&err=".$err);
        }
    } catch (Exception $err) {
        // Get the error message.
        $err->getMessage();
        
        // close the connection to DB.
        pg_close($conn);
        
        // Back to projects page.
        //header("Location: update_material.php?uuid=".$mat_id."&prj_id=".$prj_id."&con_id=".$con_id."&err=".$err);
        echo "b";
    }

    pg_close($conn);
    
    // Back to report page without error messages.
    header("Location: edit_material.php?uuid=".$mat_id."&prj_id=".$prj_id."&con_id=".$con_id."&err=".$err);
?>