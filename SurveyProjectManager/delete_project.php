<?php
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get DB connection information.
	require "lib/config.php";
	
	// Establish the connection to DB.
	$dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
	if (!$dbconn) {
		echo "An error occurred in DB connection.\n";
		exit;
	}
	
	// Get the project id post from previous page.
	$prj_id = $_REQUEST['uuid'];
	try {
		/*
		// Delete denoted subjects registered in the project.
		$sql_del_dts = "DELETE FROM denoted_subject WHERE prj_id=" . $prj_id;
		$res_del_dts = pg_query($dbconn, $sql_del_dts);
		
		// Delete denoted subjects registered in the project.
		$sql_del_dim = "DELETE FROM digitized_image WHERE prj_id=" . $prj_id;
		$res_del_dim = pg_query($dbconn, $sql_del_dim);
		
		// Delete denoted subjects registered in the project.
		$sql_del_rel_mat = "DELETE FROM rel_material_material WHERE prj_id=" . $prj_id;
		$res_del_rel_mat = pg_query($dbconn, $sql_del_rel_mat);
		
		// Delete materials registered in the project.
		$sql_del_srf = "DELETE FROM surface WHERE prj_id=" . $prj_id;
		$res_del_srf = pg_query($dbconn, $sql_del_srf);
		
		// Delete materials registered in the project.
		$sql_del_mat = "DELETE FROM material WHERE prj_id=" . $prj_id;
		$res_del_mat = pg_query($dbconn, $sql_del_mat);
		
		// Delete consolidations registered in the project.
		$sql_del_con = "DELETE FROM consolidation WHERE prj_id=" . $prj_id;
		$res_del_con = pg_query($dbconn, $sql_del_con);
		
		// Delete equipment registered in the project.
		$sql_del_rel_equ = "DELETE FROM rel_equpment_equipment WHERE prj_id=" . $prj_id;
		$res_del_rel_equ = pg_query($dbconn, $sql_del_rel_equ);
		
		// Delete equipment registered in the project.
		$sql_del_equ = "DELETE FROM equipment WHERE prj_id=" . $prj_id;
		$res_del_equ = pg_query($dbconn, $sql_del_equ);
		
		// Delete files registered in the project.
		$sql_del_fil = "DELETE FROM file WHERE prj_id=" . $prj_id;
		$res_del_fil = pg_query($dbconn, $sql_del_fil);
		
		// Delete files registered in the project.
		$sql_del_mat = "DELETE FROM material WHERE prj_id=" . $prj_id;
		$res_del_mat = pg_query($dbconn, $sql_del_mat);
		
		// Delete surveydiarys registered in the project.
		$sql_del_dry = "DELETE FROM surveydiary WHERE prj_id=" . $prj_id;
		$res_del_dry = pg_query($dbconn, $sql_del_dry);
		
		// Delete relationships between users and the project.
		$sql_del_rel_prj_mem = "DELETE FROM rel_project_member WHERE prj_id=" . $prj_id;
		$res_del_rel_prj_mem = pg_query($dbconn, $sql_del_rel_prj_mem);
		
		// Delete survey report of the project.
		$sql_del_rpt_cpt = "DELETE FROM chapter WHERE prj_id=" . $prj_id;
		$res_del_rpt_cpt = pg_query($dbconn, $sql_del_rpt_cpt);
		
		// Delete survey report of the project.
		$sql_del_rpt = "DELETE FROM report WHERE prj_id=" . $prj_id;
		$res_del_rpt = pg_query($dbconn, $sql_del_rpt);
		*/
		// Finally Delete the project.
		$sql_del_prj = "DELETE FROM project WHERE uuid='". $prj_id."'";
		$res_del_prj = pg_query($dbconn, $sql_del_prj);
	} catch (Exception $e) {
		$err = 'Caught exception: ';
		require_once('index.php');
		exit ;
	}
	// close the connection to DB.
	pg_close($dbconn);
	
	// Return to home.
	header('Location: project.php');
?>
