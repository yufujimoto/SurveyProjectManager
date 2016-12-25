<?php
	// Start the session.
	session_start();
	
	// Check session status.
	if (!isset($_SESSION["USERNAME"])) {
	  header("Location: logout.php");
	  exit;
	}
	
	// Load external libraries.
	require "lib/guid.php";
	require "lib/password.php";
	require "lib/config.php";    
    
	// Initialyze the error message.
	$err = "";
	$prj_id = "'".$_REQUEST['prj_id']."'";
	$rep_id = "'".$_REQUEST['rep_id']."'";
	$sec_id = "'".$_REQUEST["uuid"]."'";
	
	// Connect to the Database.
	$conn = pg_connect(
				"host=".DBHOST.
				" port=".DBPORT.
				" dbname=".DBNAME.
				" user=".DBUSER.
				" password=".DBPASS
			) or die('Connection failed: ' . pg_last_error());
    
	// Find a uuid of the member who created this entry by username.
	$sql_sel_mem = "SELECT uuid FROM member WHERE username = '" .$_SESSION["USERNAME"]. "'";
	$sql_res_mem = pg_query($conn, $sql_sel_mem) or die('Query failed: ' . pg_last_error());
	
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
	$htm_bdy = str_replace("\n","",str_replace("&#13;","",$_REQUEST['intro']));
	
	// Add html tag elements o convert DOM document.
    $htm_bdy = $htm_mta."<html><body>".$htm_bdy."</body></html>";
    
	// Convert html document to DOM documnet.
    $dom_bdy = new DOMDocument();
    $dom_bdy->loadHTML($htm_bdy);
    $xml_bdy = $dom_bdy->saveXML();
    
    // Parse dom document.
    $obj_bdy = simplexml_load_string($xml_bdy);
    $obj_imgs = $obj_bdy->xpath('//img');
    
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
					elseif($exf_nam==="DateTimeOriginal"){
						$exf_odt_lst = explode(" ",$exf_val);
						$exf_odt_dt = str_replace(":", "-", $exf_odt_lst[0]);
						$exf_odt=$exf_odt_dt." ".$exf_odt_lst[1];
					}
					elseif($exf_nam==="DateTimeDigitized"){
						$exf_ddt_lst = explode(" ",$exf_val);
						$exf_ddt_dt = str_replace(":", "-", $exf_ddt_lst[0]);
						$exf_ddt=$exf_ddt_dt." ".$exf_ddt_lst[1];
					}
					elseif($exf_nam==="FileDateTime"){$exf_dt=date("Y-m-d H:i:s",$exf_val);}
					elseif($exf_nam==="Make"){$exf_mak=$exf_val;}
					elseif($exf_nam==="Model"){$exf_mdl=$exf_val;}
					elseif($exf_nam==="FNumber"){
						$exf_val = explode("/", $exf_val);
						if(count($exf_val)===2){
							$exf_val = $exf_val[0]/$exf_val[1];
							$exf_fnm = $exf_val;
						}
					}
					elseif($exf_nam==="FocalLength"){
						$exf_val = explode("/", $exf_val);
						if(count($exf_val)===2){
							$exf_val = $exf_val[0]/$exf_val[1];
							$exf_fln = $exf_val;
						}
					}
					elseif($exf_nam==="ISOSpeedRatings"){$exf_iso=$exf_val;}
					elseif($exf_nam==="ExposureTime"){$exf_xpt=$exf_val;}
					elseif($exf_nam==="MaxApertureValue"){
						$exf_val = explode("/", $exf_val);
						if(count($exf_val)===2){
							$exf_val = round($exf_val[0]/$exf_val[1],2);
							$exf_mxa = $exf_val;
						}
					}
					elseif($exf_nam==="Flash"){
						$exf_val = str_pad(strtoupper(dechex($exf_val)), 4, '0', STR_PAD_LEFT);
						if($exf_val==="0000") {$exf_fls = "Flash did not fire";}
						elseif($exf_val==="0001") {$exf_fls = "Flash fired";}
						elseif($exf_val==="0005") {$exf_fls = "Strobe return light not detected";}
						elseif($exf_val==="0007") {$exf_fls = "Strobe return light detected";}
						elseif($exf_val==="0009") {$exf_fls = "Flash fired, compulsory flash mode";}
						elseif($exf_val==="000D") {$exf_fls = "Flash fired, compulsory flash mode, return light not detected";}
						elseif($exf_val==="000F") {$exf_fls = "Flash fired, compulsory flash mode, return light detected";}
						elseif($exf_val==="0010") {$exf_fls = "Flash did not fire, compulsory flash mode";}
						elseif($exf_val==="0018") {$exf_fls = "Flash did not fire, auto mode";}
						elseif($exf_val==="0019") {$exf_fls = "Flash fired, auto mode";}
						elseif($exf_val==="001D") {$exf_fls = "Flash fired, auto mode, return light not detected";}
						elseif($exf_val==="001F") {$exf_fls = "Flash fired, auto mode, return light detected";}
						elseif($exf_val==="0020") {$exf_fls = "No flash function";}
						elseif($exf_val==="0041") {$exf_fls = "Flash fired, red-eye reduction mode";}
						elseif($exf_val==="0045") {$exf_fls = "Flash fired, red-eye reduction mode, return light not detected";}
						elseif($exf_val==="0047") {$exf_fls = "Flash fired, red-eye reduction mode, return light detected";}
						elseif($exf_val==="0049") {$exf_fls = "Flash fired, compulsory flash mode, red-eye reduction mode";}
						elseif($exf_val==="004D") {$exf_fls = "Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected";}
						elseif($exf_val==="004F") {$exf_fls = "Flash fired, compulsory flash mode, red-eye reduction mode, return light detected";}
						elseif($exf_val==="0059") {$exf_fls = "Flash fired, auto mode, red-eye reduction mode";}
						elseif($exf_val==="005D") {$exf_fls = "Flash fired, auto mode, return light not detected, red-eye reduction mode";}
						elseif($exf_val==="005F") {$exf_fls = "Flash fired, auto mode, return light detected, red-eye reduction mode";}
						else{$exf_fls = "Unknown Flash";}
					}
					elseif($exf_nam==="MeteringMode"){
						if($exf_val===0) { $exf_mtr = "Unknown";}
						elseif($exf_val===1) { $exf_mtr = "Average";}
						elseif($exf_val===2) { $exf_mtr = "CenterWeightedAverage";}
						elseif($exf_val===3) { $exf_mtr = "Spot";}
						elseif($exf_val===4) { $exf_mtr = "MultiSpot";}
						elseif($exf_val===5) { $exf_mtr = "Pattern";}
						elseif($exf_val===6) { $exf_mtr = "Partial";}
						elseif($exf_val===255) { $exf_mtr = "Other";}
						else{ $exf_lgh = "Unknown Metering Mode";}
					}
					elseif($exf_nam==="LightSource"){
						if($exf_val===0) { $exf_lgh = "Unknown";}
						elseif($exf_val===1) { $exf_lgh = "Daylight";}
						elseif($exf_val===2) { $exf_lgh = "Fluorescent";}
						elseif($exf_val===3) { $exf_lgh = "Tungsten (incandescent light)";}
						elseif($exf_val===4) { $exf_lgh = "Flash";}
						elseif($exf_val===9) { $exf_lgh = "Fine weather";}
						elseif($exf_val===10) { $exf_lgh = "Cloudy weather";}
						elseif($exf_val===11) { $exf_lgh = "Shade";}
						elseif($exf_val===12) { $exf_lgh = "Daylight fluorescent (D 5700 - 7100K)";}
						elseif($exf_val===13) { $exf_lgh = "Day white fluorescent (N 4600 - 5400K)";}
						elseif($exf_val===14) { $exf_lgh = "Cool white fluorescent (W 3900 - 4500K)";}
						elseif($exf_val===15) { $exf_lgh = "White fluorescent (WW 3200 - 3700K)";}
						elseif($exf_val===17) { $exf_lgh = "Standard light A";}
						elseif($exf_val===18) { $exf_lgh = "Standard light B";}
						elseif($exf_val===19) { $exf_lgh = "Standard light C";}
						elseif($exf_val===20) { $exf_lgh = "D55";}
						elseif($exf_val===21) { $exf_lgh = "D65";}
						elseif($exf_val===22) { $exf_lgh = "D75";}
						elseif($exf_val===23) { $exf_lgh = "D50";}
						elseif($exf_val===24) { $exf_lgh = "ISO studio tungsten";}
						elseif($exf_val===255) { $exf_lgh = "Other light source";}
						else{ $exf_lgh = "Unknown light source";}
					}
					elseif($exf_nam==="ExposureProgram"){
						if($exf_val===0) { $exf_xpp = "Not defined";}
						elseif($exf_val===1) { $exf_xpp = "Manual";}
						elseif($exf_val===2) { $exf_xpp = "Normal program";}
						elseif($exf_val===3) { $exf_xpp = "Aperture priority";}
						elseif($exf_val===4) { $exf_xpp = "Shutter priority";}
						elseif($exf_val===5) { $exf_xpp = "Creative program (biased toward depth of field)";}
						elseif($exf_val===6) { $exf_xpp = "Action program (biased toward fast shutter speed)";}
						elseif($exf_val===7) { $exf_xpp = "Portrait mode (for closeup photos with the background out of focus)";}
						elseif($exf_val===8) { $exf_xpp = "Landscape mode (for landscape photos with the background in focus)";}
						else{ $exf_xpp = "Unknown";}
					}
					elseif($exf_nam==="ColorSpace"){
						if($exf_val===1) { $exf_col = "sRGB";}
						elseif($exf_val===65535) { $exf_col = "Uncalibrated";}
						else{ $exf_col = "Unknown Color Space";}
					}
					elseif($exf_nam==="YCbCrPositioning"){
						if($exf_val===1) { $exf_ycb = "Centered";}
						elseif($exf_val===2) { $exf_ycb = "Cosited";}
						else{ $exf_ycb = "Unknown YCbCr Positioning";}
					}
					elseif($exf_nam==="CompressedBitsPerPixel"){
						$exf_val = explode("/", $exf_val);
						if(count($exf_val)===2){
							$exf_val = round($exf_val[0]/$exf_val[1],2);
							$exf_bpp = $exf_val;
						}
					}
					elseif($exf_nam==="XResolution"){
						$exf_val = explode("/", $exf_val);
						if(count($exf_val)===2){
							$exf_val = round($exf_val[0]/$exf_val[1],2);
							$exf_xrs = $exf_val;
						}
					}
					elseif($exf_nam==="YResolution"){
						$exf_val = explode("/", $exf_val);
						if(count($exf_val)===2){
							$exf_val = round($exf_val[0]/$exf_val[1],2);
							$exf_yrs = $exf_val;
						}
					}
					elseif($exf_nam==="ResolutionUnit"){
						if($exf_val===1) { $exf_rsu = "No absolute unit of measurement";}
						elseif($exf_val===2) { $exf_rsu = "Inch";}
						elseif($exf_val===3) { $exf_rsu = "Centimeter";}
						else{ $exf_rsu = "Unknown";}
					}
				}
			}
			
			$img_str = fopen($img_org,'r');
			$img_dat = fread($img_str,filesize($img_org));
			$img_esc = pg_escape_bytea($img_dat);
			
			$thm_str = fopen($img_thm,'r');
			$thm_dat = fread($thm_str,filesize($img_thm));
			$thm_esc = pg_escape_bytea($thm_dat);
			
			$img_uuid = "'".GUIDv4()."'";
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
			
			$sql_insert_img = "INSERT INTO digitized_image (
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
                        $img_uuid,
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
			$sql_res_img = pg_query($conn, $sql_insert_img);
			// Check the result.
			if (!$sql_res_img) {
				// Get the error message.
				$err = pg_last_error($conn);
			}
			unlink($img_org);
			unlink($img_thm);
			
			$obj_img->attributes()->src = "report_image_view.php?uuid=".trim($img_uuid,"'");
		} else {
			foreach($obj_img->attributes() as $key => $val){
				if($key==="src"){
					$img_uuid = explode("uuid=", $obj_img->attributes()->src)[1];
					echo $img_url;
				}elseif($key==="alt") {
					$img_dsc= $obj_img->attributes()->alt;
				}
			}
			
			$sql_update_img = "UPDATE digitized_image SET descriptions='$img_dsc' WHERE uuid='$img_uuid'";
			$sql_res = pg_query($conn, $sql_update_img);
			
			echo $sql_update_img;
		}
    }
    // Remove extra elements from the XML DOM document.
    echo "<hr/>";
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
    $sec_wrt = str_replace("''","'匿名'","'".$_REQUEST["wrtr"]."'");
	$today = date("Y-m-d H:i:s");
	$sec_mdt = str_replace("''","NULL","'".$today."'");
	$sec_bdy = str_replace("''","NULL","'".htmlspecialchars($txt_bdy)."'");
	
	htmlspecialchars($txt_bdy);
	
	// Update existing record.
	$sql_update_sec = "UPDATE section SET 
						written_by=$sec_wrt,
						modified_by=$sec_mod,
						date_modified=$sec_mdt,
						body=$sec_bdy
					WHERE uuid=$sec_id";
	
	$sql_res = pg_query($conn, $sql_update_sec);
	
	// Close the connection.
	pg_close($conn);
	
    // Back to report page without error messages.
	header("Location: section.php?uuid=".$_REQUEST["uuid"]."&prj_id=".$_REQUEST['prj_id']."&rep_id=".$_REQUEST['rep_id']."&err=".$err);
?>