
<!DOCTYPE html>
<html lang="ja" style="width: 100%; height: 100%">
	<head>
		<title>SurveyProjectManager</title>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta name="Yu Fujimoto" content="" />
	</head>
	<body style="margin: 0 auto;">
	<?php
		$desired_height = $_GET["height"];
		$desired_width = $_GET["width"];
		
		// Check the validity of the uploaded file.
		if (is_uploaded_file($_FILES["avatar"]["tmp_name"])) {
			// New filename for uploaded file.
			$uploadedfile = "uploads/".$_GET["path"].".jpg";
			
			// Move the file to upload directory.
			if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $uploadedfile)) {
				// Change the permission of the file.
				chmod($uploadedfile, 0777);
				// Create a image stream of the given file.
				$source_image = imagecreatefromjpeg($uploadedfile);
				$destination_image = "uploads/thumbnail_".$_GET["path"].".jpg";
				
				// Get the file size of the image.
				$width = imagesx($source_image);
				$height = imagesy($source_image);
				
				// Find the "desired height" of this thumbnail, relative to the desired width.
				$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
				
				$avatar_msg = $width;
				// copy source image at a resized size.
				imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
				
				// create the physical thumbnail image to its destination
				imagejpeg($virtual_image, $destination_image);
				echo "<img id='avatar' width=".$desired_width." height=".$desired_height."px style='margin:0px auto;display:block' ";
				echo "src='" . $destination_image . "' alt='Uploaded image is invalid.'/>";
			} else {
				echo "Cannot Upload Avatar";
				$avatar_msg = "Cannot Upload Avatar";
			}
		} else {
			if ($_REQUEST['mem_id']) {
				// Get avatar from the table
				// Add an existing section.
				
				$mem = getMembersById($_REQUEST['mem_id'], $dbuser, $dbpass, $dbname);
				
				if($mem['avatar'] != ""){
					echo "<img id='avatar' width=".$desired_width." height=".$desired_height."px style='margin:0px auto;display:block' ";
					echo "src='project_members_list_avatar.php?mem_id=" . $mem['id'] ."' alt='Uploaded image is invalid.'/>";
				} else {
					echo "<img id='avatar' width=".$desired_width." height=".$desired_height."px style='margin:0px auto;display:block' ";
					echo "src='images/avatar.png' alt='Uploaded image is invalid.'/>";
				}
			} else {
				// Initialize the default image file name.
				echo "<img id='avatar' width=".$desired_width." height=".$desired_height."px style='margin:0px auto;display:block' ";
				echo "src='images/avatar.jpg' alt='Uploaded image is invalid.'/>";
			}
		}
		
		pg_close($dbconn); 
	?>
	</body>
</html>