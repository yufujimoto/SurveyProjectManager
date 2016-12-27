<!DOCTYPE html>
<html>
	<head>
		<title>SimpleImageViewer</title>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta name="Yu Fujimoto" content="" />
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
		<link href="../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" />
		<link href="../theme.css" rel="stylesheet" />
		
		<!-- Import external scripts for Bootstrap CSS -->
		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/bootstrap.min.js"></script>
	</head>
	<body>
        <?php
            require "config.php";
            
            // Establish the connection.
            $conn = pg_connect("host=".DBHOST.
                               " port=".DBPORT.
                               " dbname=".DBNAME.
                               " user=".DBUSER.
                               " password=".DBPASS);
            
            // Check connection status.
            if (!$conn) {
                echo "An error occurred in DB connection.\n";
                exit;
            }
            
            // Query image with specific uuid.  
            $img_id = $_REQUEST["uuid"];
            $ratio = $_REQUEST["ratio"];
            
            $path = "../material_image_view.php?uuid=".$img_id."&type=original";
            
            $sql_sel_img = "SELECT image FROM digitized_image WHERE uuid='".$img_id."'" ;
            $sql_res_img = pg_query($sql_sel_img);	
            $sql_obj_img = pg_fetch_result($sql_res_img, "image");
            $sql_obj_img = base64_encode(pg_unescape_bytea($sql_obj_img));
            
            $scheme = 'data:application/octet-stream;base64,';
            $size = getimagesize($scheme.$sql_obj_img);
            
            //print_r($size);
            $width = $size[0] * $ratio;
            $height = $size[1] * $ratio;
            
            //echo $path;
            echo "<img id='avatar' width='".$width."' height='".$height."'px style='margin:0px auto;display:block' ";
            echo "src='".$path."' alt='Uploaded image is invalid.'/>";
        ?>
    </body>
</html>