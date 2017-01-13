<?php
	// Start the session.
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
	
    // Insert as new entry.
    $prj_id = $_REQUEST['prj_id'];
    $con_id = $_REQUEST['con_id'];
    $mat_id = $_REQUEST['mat_id'];
    $dmg_uid = str_replace("''","NULL","'".$_REQUEST['img_id']."'");
    $dmg_dsc = str_replace("''","NULL","'".$_REQUEST['img_dsc']."'");
    
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
	
	// Close the connection
    pg_close($conn);
	
    // Get the URL with protocol return to. 
	$url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].FULLPATH."/edit_material.php";
	
	// Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id'],
		'con_id' => $_REQUEST['con_id'],
		'mat_id' => $_REQUEST['mat_id']
	);
	
	// Use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header'=> 'Cookie: '.$_SERVER['HTTP_COOKIE']."\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);
	
	// Convert array to stream_context. 
	$context  = stream_context_create($options);
	
	// Open the URL with file get contents function.
	$result = file_get_contents($url, false, $context);
	echo $result;
?>