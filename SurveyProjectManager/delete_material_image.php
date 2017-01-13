<?php
    session_start();
    session_write_close();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
    
	// Get DB connection information.
	require "lib/config.php";
	
	// Establish the connection to DB.
	$conn = pg_connect("host=".DBHOST."
					   port=".DBPORT."
					   dbname=".DBNAME."
					   user=".DBUSER."
					   password=".DBPASS)
		or die('Connection failed: ' . pg_last_error());
	
	// Get the secdtion id and project id post from previous page.
	$uuid = $_REQUEST['img_id'];
	
	try {
		// Finally Delete the project.
		$sql_del_sec = "DELETE FROM digitized_image WHERE uuid='". $uuid."'";
		$res_del_sec = pg_query($conn, $sql_del_sec);
	} catch (Exception $e) {
		$err = "Caught exception: ".$e;
		
		// Close the connection to DB.
		pg_close($conn);
	}
	// Close the connection to DB.
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