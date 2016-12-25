
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
		$desired_height = $_REQUEST["height"];
		$desired_width = $_REQUEST["width"];
		$trgt = str_replace("'", "", $_REQUEST['target']);
		$imgid = $_REQUEST['img_id'];
				
		// Check the validity of the uploaded file.
		if (is_uploaded_file($_FILES["avatar"]["tmp_name"])) {
			// New filename for uploaded file.
			$uploadedfile = "uploads/".$_REQUEST["id"].".jpg";
			
			// Move the file to upload directory.
			if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $uploadedfile)) {
				// Change the permission of the file.
				chmod($uploadedfile, 0777);
				
				// Create a image stream of the given file.
				$source_image = imagecreatefromjpeg($uploadedfile);
				$destination_image = "uploads/thumbnail_".$_REQUEST["id"].".jpg";
				
				// Get the file size of the image.
				$width = imagesx($source_image);
				$height = imagesy($source_image);
				
				if (empty($desired_height)){
					$desired_height = ($height * $desired_width) / $width;
				} elseif (empty($desired_width)) {
					$desired_width = ($width * $desired_height) / $height;
				} elseif (empty($desired_width) and empty($desired_height)) {
					$desired_height = $height;
					$desired_width = $width;
				}
				
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
			if ($trgt=="project") {
				// Get avatar from the table
				// Add an existing section.
				if($imgid != ""){
					echo "<img id='avatar' width=".$desired_width." height=".$desired_height." style='margin:0px auto;display:block' ";
					echo "src='avatar_project_face.php?uuid=" . $imgid ."' alt='Uploaded image is invalid.'/>";
				} else {
					echo "<img id='avatar' width=".$desired_width." height=".$desired_height."px style='margin:0px auto;display:block' ";
					echo "src='images/noimage.jpg' alt='Uploaded image is invalid.'/>";
				}
			} elseif ($trgt=="member") {
				// Initialize the default image file name.				
				echo "<img id='avatar' width=".$desired_width." height=".$desired_height." style='margin:0px auto;display:block' ";
				echo "src='images/avatar.jpg' alt='Uploaded image is invalid.'/>";
			} elseif ($trgt=="consolidation") {
				// Initialize the default image file name.				
				if($imgid != ""){
					echo "<img id='avatar' height=".$desired_height." style='margin:0px auto;display:block' ";
					echo "src='avatar_project_face.php?uuid=" . $imgid ."' alt='Uploaded image is invalid.'/>";
				} else {
					echo "<img id='avatar' height=400px width=600px style='margin:0px auto;display:block' ";
					echo "src='images/noimage.jpg' alt='Uploaded image is invalid.'/>";
				}
			} else {
				// Initialize the default image file name.
				echo "<img id='avatar' width=".$desired_width." height=".$desired_height." style='margin:0px auto;display:block' ";
				echo "src='images/avatar.jpg' alt='Uploaded image is invalid.'/>";
			}
		}
		// close the connection to DB.
		pg_close($conn);
	?>
	</body>
</html>