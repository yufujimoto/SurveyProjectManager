<?php
	// Start the session and unlock session file.
	session_cache_limiter("private_no_expire");
	session_start();
	session_write_close();
	
	// Check session status.
	if (!isset($_SESSION["USERNAME"])) {
	  header("Location: logout.php");
	  exit;
	}
	
	// Load external libraries.
	require_once "lib/guid.php";
	require_once "lib/config.php";
	require_once "lib/moveTo.php";
	require_once "lib/getExif.php";
	
	// The page return to after the process.
	$returnTo = "section.php";
	
	// Create post data as the array.
	$data = array(
		'prj_id' => $_REQUEST['prj_id'],
		'rep_id' => $_REQUEST['rep_id'],
		'sec_id' => $_REQUEST['sec_id'],
	);  
    
	// Initialyze the error message.
	$err = "";
	
	// Connect to the DB.
	$conn = pg_connect(
				"host=".DBHOST.
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
		//moveToLocal($returnTo, $data);
	}
	
	// Find a uuid of the member who created this entry by username.
	$sql_sel_mem = "SELECT uuid FROM member WHERE username = '" .$_SESSION["USERNAME"]. "'";
	$sql_res_mem = pg_query($conn, $sql_sel_mem);
	
	if (!$sql_res_mem) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
	// Get values.
	$mem_id = pg_fetch_assoc($sql_res_mem)["uuid"];
	
	//========================================
	//    Convert String Text to DOM Object
	//========================================
	// Make a html document.
	$xml_mta = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
	$htm_dec = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">';
    $htm_mta = '<meta http-equiv="content-type" content="text/html; charset=utf-8"/>';
	
	// Replace special characters.
	$htm_bdy = str_replace("\n","",str_replace("&#13;","",$_REQUEST['sec_txt']));
	
	// Add html tag elements o convert DOM document.
    $htm_bdy = $htm_mta."<html><body>".$htm_bdy."</body></html>";
    
	// Convert html document to DOM documnet.
    $dom_bdy = new DOMDocument();
    $dom_bdy->loadHTML($htm_bdy);
    $xml_bdy = $dom_bdy->saveXML();
    
    // Parse dom document.
    $obj_bdy = simplexml_load_string($xml_bdy);
    $obj_imgs = $obj_bdy->xpath('//img');
    
	$prj_id =  "'".$_REQUEST['prj_id']."'";
	$rep_id =  "'".$_REQUEST['rep_id']."'";
	$sec_id =  "'".$_REQUEST['sec_id']."'";
	
    foreach($obj_imgs as $obj_img){
		$img_org = "images/noimage.jpg";
		$img_dsc = "NULL";
		
        foreach($obj_img->attributes() as $key => $val){
            if($key==="src"){
                $img_org = $obj_img->attributes()->src;
            } elseif($key==="alt") {
				$img_dsc= $obj_img->attributes()->alt;
			}
        }
		
		if (dirname($img_org)===FULLPATH."/uploads"){
			//========================================
			//   Create the thumbnail of the image
			//========================================
			// Create a image stream of the given file.
			
			// Initialyze variables.
			$img_esc = "NULL";
			$thm_esc = "NULL";
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
			
			// Define filenames for uploaded file and the new thumbnail file.
			$img_org = "uploads/".basename($img_org);
			$img_thm = "uploads/thumbnail_".basename($img_org);
			
			// Create the image object.
			$img_org_obj = imagecreatefromjpeg($img_org);
			
			// Get the file size of the image.
			$width = imagesx($img_org_obj);
			$height = imagesy($img_org_obj);
			
			// Scaled image size is limited in height 300px.
			$scl_hgh = 300;
			$scl_wid = ($width * $scl_hgh) / $height;
			
			// Find the "desired height" of this thumbnail, relative to the desired width.
			$img_vrt = imagecreatetruecolor($scl_wid, $scl_hgh);
			
			// copy source image at a resized size.
			imagecopyresampled($img_vrt, $img_org_obj, 0, 0, 0, 0, $scl_wid, $scl_hgh, $width, $height);
			
			// create the physical thumbnail image to its destination
			imagejpeg($img_vrt, $img_thm);
			
			//========================================
			//         Insert images into DB
			//========================================
			
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
			$img_str = fopen($img_org,'r');
			$img_dat = fread($img_str,filesize($img_org));
			$img_esc = pg_escape_bytea($img_dat);
			
			$thm_str = fopen($img_thm,'r');
			$thm_dat = fread($thm_str,filesize($img_thm));
			$thm_esc = pg_escape_bytea($thm_dat);
			
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
			
			$sql_ins_img = "INSERT INTO digitized_image (
                        uuid,
                        prj_id,
						rep_id,
						sec_id,
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
						$rep_id,
						$sec_id,
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
			$sql_res_img = pg_query($conn, $sql_ins_img);
			
			// Check the result.
			if (!$sql_res_img) {
				// Get the error message.
				$err = array("err" => "DB Error: ".pg_last_error($conn));
				
				// Close the connection to DB.
				pg_close($conn);
				
				// Delete the uploaded files.
				unlink($img_org);
				unlink($img_thm);
				
				// Return to material page.
				$data = array_merge($data, $err);
				moveToLocal($returnTo, $data);
				
			}
			// Delete the uploaded files.
			unlink($img_org);
			unlink($img_thm);
			
			$obj_img->attributes()->src = "report_image_view.php?uuid=".trim($img_id,"'");
		} else {
			foreach($obj_img->attributes() as $key => $val){
				if($key==="src"){
					$img_id = explode("uuid=", $obj_img->attributes()->src)[1];
					echo $img_url;
				}elseif($key==="alt") {
					$img_dsc= $obj_img->attributes()->alt;
				}
			}
			
			$sql_update_img = "UPDATE digitized_image SET descriptions='$img_dsc' WHERE uuid='$img_id'";
			$sql_res = pg_query($conn, $sql_update_img);
		}
    }
	
    // Remove extra elements from the XML DOM document.
	$txt_bdy = $obj_bdy->saveXML();
	$txt_bdy = str_replace( $htm_mta ,"" , $txt_bdy);
	$txt_bdy = str_replace( $xml_mta, "" , $txt_bdy);
	$txt_bdy = str_replace( $htm_dec, "" , $txt_bdy);
	$txt_bdy = str_replace( "<head>", "" , $txt_bdy);
	$txt_bdy = str_replace( "</head>", "" , $txt_bdy);
	$txt_bdy = str_replace( "<html>", "" , $txt_bdy);
	$txt_bdy = str_replace( "</html>", "" , $txt_bdy);
	$txt_bdy = str_replace( "<body>", "" , $txt_bdy);
	$txt_bdy = str_replace( "</body>", "" , $txt_bdy);
	$txt_bdy = str_replace( "<body/>", "" , $txt_bdy);
	
    // Get arrays of entries.
	$sec_mod = str_replace("''","NULL","'".$mem_id."'");
    $sec_wrt = str_replace("''","'匿名'","'".$_REQUEST["sec_wrt"]."'");
	$today = date("Y-m-d H:i:s");
	$sec_mdt = str_replace("''","NULL","'".$today."'");
	$sec_bdy = str_replace("''","NULL","'".htmlspecialchars($txt_bdy)."'");
	
	htmlspecialchars($txt_bdy);
	
	// Update existing record.
	$sql_udt_sec = "UPDATE section SET 
						written_by=$sec_wrt,
						modified_by=$sec_mod,
						date_modified=$sec_mdt,
						body=$sec_bdy
					WHERE uuid=$sec_id";
	
	$sql_res_sec = pg_query($conn, $sql_udt_sec);
	// Check the result.
	if (!$sql_res_sec) {
		// Get the error message.
		$err = array("err" => "DB Error: ".pg_last_error($conn));
		
		// Close the connection to DB.
		pg_close($conn);
		
		// Return to material page.
		$data = array_merge($data, $err);
		moveToLocal($returnTo, $data);
	}
	
	// Close the connection.
	pg_close($conn);
	
	// Retrun to material page.
	moveToLocal($returnTo, $data);
?>