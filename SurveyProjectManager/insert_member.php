<?php
    require 'lib/guid.php';
    require "lib/password.php";
    require "lib/config.php";
    
    // Connect to the Database.
    $dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
    
    $mem_bdy_y = "1900";
    $mem_bdy_m = "01";
    $mem_bdy_d = "01";
    
    if (!$_POST['mem_unm']) {
        $err = "ユーザー名が空白です。";
        header("Location: add_member.php?err=".$err);
        exit ;
    }
    
    if (!$_POST['mem_pwd']) {
        $err = "パスワードが空白です。";
        header("Location: add_member.php?err=".$err);
        exit ;
    }
    
    if (!$_POST['mem_eml']) {
        $err = "メールアドレスは必須です。";
        header("Location: add_member.php?err=".$err);
        exit ;
    }
    
    if (!$_POST['org_nam']) {
        $err = "組織名は必須です。";
        header("Location: add_member.php?err=".$err);
        exit ;
    }
    
    // Get avatar.
    $mem_avatar = $_POST['mem_avt'];
	if ($mem_avatar != "") {
		if (file_exists("uploads/".$mem_avatar)) {
			$filename = "uploads/thumbnail_".$mem_avatar;
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
    
    // Parameters for organization.
    $org_uid = "'".GUIDv4()."'";
    $org_nam = str_replace("''", "NULL", "'".$_POST['org_nam']."'");
    $org_sec = str_replace("''", "NULL", "'".$_POST['org_sec']."'");
    $org_zip = str_replace("''", "NULL", "'".$_POST['org_zip']."'");
    $org_cnt = "'日本'";
    $org_adm = str_replace("''", "NULL", "'".$_POST['org_adm']."'");
    $org_cty = str_replace("''", "NULL", "'".$_POST['org_cty']."'");
    $org_add = str_replace("''", "NULL", "'".$_POST['org_add']."'");
    $org_phn = str_replace("''", "NULL", "'".$_POST['org_phn']."'");
    "NULL";
    // Parameters for member.
    $mem_uid = "'".GUIDv4()."'";
    $mem_avt = $escaped;
    $mem_snm = str_replace("''", "NULL", "'".$_POST['mem_snm']."'");
    $mem_fnm = str_replace("''", "NULL", "'".$_POST['mem_fnm']."'");
    $mem_bdy_y = $_POST['mem_bdy_y'];
    $mem_bdy_m = $_POST['mem_bdy_m'];
    $mem_bdy_d = $_POST['mem_bdy_d'];
    $mem_bdy = str_replace("'--'", "NULL", "'".$mem_bdy_y."-".$mem_bdy_m."-".$mem_bdy_d."'");
    $mem_zip = str_replace("''", "NULL", "'".$_POST['mem_zip']."'");
    $mem_cnt = str_replace("''", "NULL", "'日本'");
    $mem_adm = str_replace("''", "NULL", "'".$_POST['mem_adm']."'");
    $mem_cty = str_replace("''", "NULL", "'".$_POST['mem_cty']."'");
    $mem_add = str_replace("''", "NULL", "'".$_POST['mem_add']."'");
    $mem_phn = str_replace("''", "NULL", "'".$_POST['mem_phn']."'");
    $mem_mph = str_replace("''", "NULL", "'".$_POST['mem_mph']."'");
    $mem_eml = str_replace("''", "NULL", "'".$_POST['mem_eml']."'");
    $mem_apt = str_replace("''", "NULL", "'".$_POST['mem_apt']."'");
    $mem_unm = str_replace("''", "NULL", "'".$_POST['mem_unm']."'");
    $mem_pwd = str_replace("''", "NULL", "'".password_hash($_POST['mem_pwd'], PASSWORD_DEFAULT)."'");
    $mem_typ = str_replace("''", "NULL", "'".$_POST['mem_typ']."'");
	
    try {
        // Insert new record into the organization table
        $sql_org_inssert = "INSERT INTO organization (
                            uuid,
                            name,
                            section,
                            country,
                            administrativearea,
                            city,
                            contact_address,
                            zipcode,
                            phone
                        ) VALUES (
                            $org_uid,
                            $org_nam,
                            $org_sec,
                            $org_cnt,
                            $org_adm,
                            $org_cty,
                            $org_add,
                            $org_zip,
                            $org_phn
                        )";
        
        $sql_org_result = pg_query($dbconn, $sql_org_inssert);
        
        // Check the result.
        if (!$sql_org_result) {
            $err = pg_last_error($dbconn);
            
			// Delete the temporal files.
			unlink("uploads/".$mem_avatar);
			unlink("uploads/thumbnail_".$mem_avatar);
			
            // Back to member add page.
			header("Location: add_member.php?err=".$err);
        }
        
        // Insert new record into the member table
        $sql_mem_inssert = "INSERT INTO member (
                            uuid,
                            org_id,
                            avatar,
                            surname,
                            firstname,
                            apointment,
                            birthday,
                            country,
                            administrativearea,
                            city,
                            contact_address,
                            zipcode,
                            phone,
                            mobile_phone,
                            email,
                            username,
                            password,
                            usertype
                        ) VALUES (
                            $mem_uid,
                            $org_uid,
                            '{$mem_avt}',
                            $mem_snm,
                            $mem_fnm,
                            $mem_apt,
                            $mem_bdy,
                            $mem_cnt,
                            $mem_adm,
                            $mem_cty,
                            $mem_add,
                            $mem_zip,
                            $mem_phn,
                            $mem_mph,
                            $mem_eml,
                            $mem_unm,
                            $mem_pwd,
                            $mem_typ
                        )";
        
        $sql_mem_result = pg_query($dbconn, $sql_mem_inssert);
		
        // Check the result.
        if (!$sql_mem_result) {
			// Get the error messages.
            $err = pg_last_error($dbconn);
			
			// Delete the temporal files.
			unlink("uploads/".$mem_avatar);
			unlink("uploads/thumbnail_".$mem_avatar);
			
			// Back to member add page.
            header("Location: add_member.php?err=".$err);
        }
        
    } catch (Exception $err) {
        $err->getMessage();
		
		// Delete the temporal files.
        unlink("uploads/".$mem_avatar);
		unlink("uploads/thumbnail_".$mem_avatar);
		
		// Back to member add page.
		header("Location: add_member.php?err=".$err);
    }
    
	// Move to login page.
    header("Location: login.php");
?>