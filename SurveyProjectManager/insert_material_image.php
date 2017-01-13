<?php
	// Start the session and unlock session file.
    session_start();
    session_write_close();
	
	// The page return to after the process.
	$returnTo = "edit_material.php";
	
	// Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id'],
		'con_id' => $_REQUEST['con_id'],
		'mat_id' => $_REQUEST['mat_id']
	);
    
	// Require the external scripts.
    require_once "lib/guid.php";
    require_once "lib/config.php";
	require_once "lib/moveTo.php";
	require_once "lib/getExif.php";
	
    // Get image.
    $mat_img = $_REQUEST['img_id'];
	
    if ($mat_img != "") {
        if (file_exists("uploads/".$mat_img.".jpg")) {
			$img_org = "uploads/".$mat_img.".jpg";
			$img_org_str = fopen($img_org,'r');
            $img_org_dat = fread($img_org_str,filesize($img_org));
            $img_esc= pg_escape_bytea($img_org_dat);
			
			$img_thm = "uploads/thumbnail_".$mat_img.".jpg";
			$img_thm_str = fopen($img_thm,'r');
            $img_thm_dat = fread($img_thm_str,filesize($img_thm));
            $thm_esc= pg_escape_bytea($img_thm_dat);
		} else {
			// Get the error message.
			$err = array("err" => "Image file is not uploaded.");
			
			// Return to material page.
			$data = array_merge($data, $err);
			moveToLocal($returnTo, $data);
		}
	} else {
		// Get the error message.
		$err = array("err" => "The image file is invalid.");
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
	// Initialyze variables.
	$exf_ori = "NULL";
	$exf_ver = "NULL";
	$exf_wid = "NULL";
	$exf_hgh = "NULL";
	$exf_odt = "NULL";
	$exf_ddt = "NULL";
	$exf_dt = "NULL";
	$exf_mak = "NULL";
	$exf_mdl = "NULL";
	$exf_fnm = "NULL";
	$exf_fln = "NULL";
	$exf_iso = "NULL";
	$exf_xpt = "NULL";
	$exf_mxa = "NULL";
	$exf_fls = "NULL";
	$exf_mtr = "NULL";
	$exf_lgh = "NULL";
	$exf_xpp = "NULL";
	$exf_col = "NULL";
	$exf_ycb = "NULL";
	$exf_bpp = "NULL";
	$exf_xrs = "NULL";
	$exf_yrs = "NULL";
	$exf_rsu = "NULL";
	
	// Get the series of EXIF information.
	$src_exf = exif_read_data($img_org, 0, true);
	foreach ($src_exf as $exf_key => $exf_tag) {
		foreach ($exf_tag as $exf_nam => $exf_val) {
			if($exf_nam==="Orientation"){$exf_ori=$exf_val;}
			elseif($exf_nam==="Width"){$exf_wid=$exf_val;}
			elseif($exf_nam==="Height"){$exf_hgh=$exf_val;}
			elseif($exf_nam==="DateTimeOriginal"){$exf_odt = getExifDateTime($exf_val);}
			elseif($exf_nam==="DateTimeDigitized"){$exf_ddt = getExifDateTime($exf_val);}
			elseif($exf_nam==="FileDateTime"){$exf_dt=date("Y-m-d H:i:s",$exf_val);}
			elseif($exf_nam==="Make"){$exf_mak=$exf_val;}
			elseif($exf_nam==="Model"){$exf_mdl=$exf_val;}
			elseif($exf_nam==="FNumber"){$exf_fnm = getFNumber($exf_val);}
			elseif($exf_nam==="FocalLength"){$exf_fln = getFocalLength($exf_val);}
			elseif($exf_nam==="ISOSpeedRatings"){$exf_iso=$exf_val;}
			elseif($exf_nam==="ExposureTime"){$exf_xpt=$exf_val;}
			elseif($exf_nam==="MaxApertureValue"){$exf_mxa = getMaxAperture($exf_val);}
			elseif($exf_nam==="Flash"){$exf_fls = getFlash($exf_val);}
			elseif($exf_nam==="MeteringMode"){$exf_mtr = getMeteringMode($exf_val);}
			elseif($exf_nam==="LightSource"){$exf_lgh = getLightSource($exf_val);}
			elseif($exf_nam==="ExposureProgram"){$exf_xpp = getExposureProgram($exf_val);}
			elseif($exf_nam==="ColorSpace"){$exf_col = getColorSpace($exf_val);}
			elseif($exf_nam==="YCbCrPositioning"){$exf_ycb = getYCbCr($exf_val);}
			elseif($exf_nam==="CompressedBitsPerPixel"){$exf_bpp = getCompressedBitsPerPixel($exf_val);}
			elseif($exf_nam==="XResolution"){$exf_xrs = getResolution($exf_val);}
			elseif($exf_nam==="YResolution"){$exf_yrs = getResolution($exf_val);}
			elseif($exf_nam==="ResolutionUnit"){$exf_rsu = getResolutionUnit($exf_val);}
		}
	}
	
	// Initialyze the error message.
	$img_id = "'".GUIDv4()."'";
	$prj_id = "'".$_REQUEST['prj_id']."'";
	$con_id = "'".$_REQUEST['con_id']."'";
	$mat_id = "'".$_REQUEST["mat_id"]."'";
	$img_dsc = str_replace("'NULL'", "NULL", "'".$exf_ori."'");
	$exf_ori = str_replace("'NULL'", "NULL", "'".$exf_ori."'");
	$exf_ver = str_replace("'NULL'", "NULL", "'".$exf_ver."'");
	$exf_wid = $exf_wid;
	$exf_hgh = $exf_hgh;
	$exf_odt = str_replace("'NULL'", "NULL", "'".$exf_odt."'");
	$exf_ddt = str_replace("'NULL'", "NULL", "'".$exf_ddt."'");
	$exf_dt  = str_replace("'NULL'", "NULL", "'".$exf_dt."'");
	$exf_mak = str_replace("'NULL'", "NULL", "'".$exf_mak."'");
	$exf_mdl = str_replace("'NULL'", "NULL", "'".$exf_mdl."'");
	$exf_fnm = $exf_fnm;
	$exf_fln = $exf_fln;
	$exf_iso = $exf_iso;
	$exf_xpt = str_replace("'NULL'", "NULL", "'".$exf_xpt."'");
	$exf_mxa = $exf_mxa;
	$exf_fls = str_replace("'NULL'", "NULL", "'".$exf_fls."'");
	$exf_mtr = str_replace("'NULL'", "NULL", "'".$exf_mtr."'");
	$exf_lgh = str_replace("'NULL'", "NULL", "'".$exf_lgh."'");
	$exf_xpp = str_replace("'NULL'", "NULL", "'".$exf_xpp."'");
	$exf_col = str_replace("'NULL'", "NULL", "'".$exf_col."'");
	$exf_ycb = str_replace("'NULL'", "NULL", "'".$exf_ycb."'");
	$exf_bpp = $exf_bpp;
	$exf_xrs = $exf_xrs;
	$exf_yrs = $exf_yrs;
	$exf_rsu = str_replace("'NULL'", "NULL", "'".$exf_rsu."'");
	
    // Connect to the Database.
    $conn = pg_connect("host=".DBHOST.
                       " port=".DBPORT.
                       " dbname=".DBNAME.
                       " user=".DBUSER.
                       " password=".DBPASS);
	
    // Check the connection status.
	if(!$conn){
		// Get the error message.
		$err = array("err" => "DB Error:".pg_last_error());
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
	// Make a SQL query.
	try {
		$sql_ins_img = "INSERT INTO digitized_image (
			uuid,
			prj_id,
			con_id,
			mat_id,
			image,
			thumbnail,
			descriptions,
			exif_orientation,
			exif_version,
			exif_imagewidth,
			exif_imageheight,
			exif_datetimeoriginal,
			exif_datetimedigitized,
			exif_datetime,
			exif_make,
			exif_model,
			exif_fnumber,
			exif_focallength,
			exif_isospeedratings,
			exif_exposuretime,
			exif_maxaperturevalue,
			exif_flash,
			exif_meteringmode,
			exif_lightsource,
			exif_exposureprogram,
			exif_colorspace,
			exif_ycbcrpositioning,
			exif_compesedbitsperpixel,
			exif_xresolution,
			exif_yresolution,
			exif_resolutionunit
		) VALUES (
			$img_id,
			$prj_id,
			$con_id,
			$mat_id,
			'{$img_esc}',
			'{$thm_esc}',
			$img_dsc,
			$exf_ori,
			$exf_ver,
			$exf_wid,
			$exf_hgh,
			$exf_odt,
			$exf_ddt,
			$exf_dt,
			$exf_mak,
			$exf_mdl,
			$exf_fnm,
			$exf_fln,
			$exf_iso,
			$exf_xpt,
			$exf_mxa,
			$exf_fls,
			$exf_mtr,
			$exf_lgh,
			$exf_xpp,
			$exf_col,
			$exf_ycb,
			$exf_bpp,
			$exf_xrs,
			$exf_yrs,
			$exf_rsu
		)";
		// Excute the SQL query.
		$sql_res_img = pg_query($conn, $sql_ins_img);
		
		// Check the result.
		if (!$sql_res_img) {
			// Get the error message.
			$err = array("err" => "DB Error: ".pg_last_error($conn));
			
			// Delete the uploaded files.
			unlink($img_org);
			unlink($img_thm);
			
			// Close the connection to DB.
			pg_close($conn);
			
			// Return to material page.
			$data = array_merge($data, $err);
			moveToLocal($returnTo, $data);
		} else {
			// Delete the uploaded files.
			unlink($img_org);
			unlink($img_thm);
		}
	} catch (Exception $e) {
		// Get error message
		$err = array("err" => "Caught exception: ".$e);
		
		// Close the connection to DB.
		pg_close($conn);
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
	// Retrun to material page.
	moveToLocal($returnTo, $data);
?>