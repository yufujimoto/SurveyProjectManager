<?php
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
    
    require "lib/guid.php";
    require "lib/config.php";
    
    // Connect to the Database.
    $conn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
    
    if (!$_REQUEST['name']) {
        $err = "統合体名が空白です。";
        header("Location: add_project.php?err=".$err);
        exit ;
    }
    
    // Get avatar.
    $prj_faceimage = $_REQUEST['con_fimg'];
    if ($prj_faceimage != "") {
        if (file_exists("uploads/".$prj_faceimage)) {
            $filename = "uploads/thumbnail_".$prj_faceimage;
            $filestream = fopen($filename,'r');
            $data = fread($filestream,filesize($filename));
            $escaped= pg_escape_bytea($data);
            
            
            
        } else {
            $filename = "images/noimage.jpg";
            $filestream = fopen($filename,'r');
            $data = fread($filestream,filesize($filename));
            $escaped= pg_escape_bytea($data);
        }
    } else {
        $filename = "images/noimage.jpg";
        $filestream = fopen($filename,'r');
        $data = fread($filestream,filesize($filename));
        $escaped= pg_escape_bytea($data); 
    }
        
    $uuid = "'".GUIDv4()."'";
    $prj_id = "'".$_REQUEST['prj_id']."'";
    $name = "'".$_REQUEST['name']."'";
    $faceimage = $escaped;
    $gnam = str_replace("''", "NULL", "'".$_REQUEST['gnam']."'");
    $extnt = str_replace("''", "NULL", "'".$_REQUEST['cur_extent']."'");
    $lat = str_replace("''", "NULL", "'".$_REQUEST['lat']."'");
    $lon = str_replace("''", "NULL", "'".$_REQUEST['lon']."'");
    $esta = str_replace("''", "NULL", "'".$_REQUEST['est_extent']."'");
    $bgn = str_replace("''", "NULL", "'".$_REQUEST['bgn']."'");
    $end = str_replace("''", "NULL", "'".$_REQUEST['end']."'");
    $desc = str_replace("''", "NULL", "'".$_REQUEST['desc']."'");
    
    if ($extnt!="NULL") {
        $extent = "ST_MakePolygon(ST_GeomFromText('".$extnt."'),".SRID.")";
    } else {
        $extent = "NULL";
    }
    
    if ($lat!="NULL" and $lon!="NULL"){
        $reppt = "ST_SetSRID(ST_MakePoint($lat, $lon), ".SRID.")";
    } else {
        $reppt = "NULL";
    }
    
    if ($esta!="NULL") {
        $estimatedarea = "ST_MakePolygon(ST_GeomFromText('".$esta."'),".SRID.")";
    } else {
        $estimatedarea = "NULL";
    }
        
    try{
        // Insert new record into the project table.
        $sql_inssert = "INSERT INTO consolidation (
                            uuid,
                            prj_id,
                            name,
                            faceimage,
                            geographic_name,
                            geographic_extent,
                            represented_point,
                            estimated_area,
                            estimated_period_beginning,
                            estimated_period_ending,
                            descriptions
                        ) VALUES (
                            $uuid,
                            $prj_id,
                            $name,
                            '{$faceimage}',
                            $gnam,
                            $extent,
                            $reppt,
                            $estimatedarea,
                            $bgn,
                            $end,
                            $desc
                        )";
        $sql_result = pg_query($conn, $sql_inssert);
        
        // Check the result.
        if (!$sql_result) {
            // Get the error message.
            $err = pg_last_error($conn);
            
            // close the connection to DB.
            pg_close($conn);
            
            // Back to projects page.
            header("Location: consolidation.php?uuid=".$_REQUEST['prj_id']."&err=".$err);
        }
    } catch (Exception $err) {
        // Get the error message.
        $err->getMessage();
        
        // close the connection to DB.
        pg_close($conn);
        
        // Back to projects page.
        header("Location: consolidation.php?uuid=".$_REQUEST['prj_id']."&err=".$err);
    }
    // close the connection to DB.
	pg_close($conn);
    
    // Back to projects page without error messages.
    header("Location: consolidation.php?uuid=".$_REQUEST['prj_id']."&err=".$err);
?>
