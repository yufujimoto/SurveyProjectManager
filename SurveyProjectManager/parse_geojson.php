<?php
	// Start session and unlock the session file.
    session_start();
    session_write_close();
	
	// Load external libraries.
	require "lib/guid.php";
    require "lib/config.php";
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	$prj_id = $_REQUEST['prj_id'];
	$tmp_nam = $_REQUEST['tmp_nam'];
	$jsn_str = $_REQUEST['jsn_str'];
    $jsn_file = "uploads/".$tmp_nam.".geojson";
	$wkt_file = "uploads/".$tmp_nam.".wkt";
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta name="Yu Fujimoto" content="" />
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
		<link href="../theme.css" rel="stylesheet" />
		
		<link href="lib/openlayers/ol.css" rel="stylesheet" type="text/css"/>
		<script src="lib/openlayers/ol.js"></script>
		
		<!-- Import external scripts for Bootstrap CSS -->
		<script src="lib/jquery-3.1.1/jquery.min.js"></script>
		
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/bootstrap.min.js"></script>
	</head>
	<body>
    <?php
        // Check uploads and parse the uploaded CSV file.
        if (is_uploaded_file($_FILES["upfile"]["tmp_name"])) {
            // Check the validity of the uploaded file and move the file to upload directory.
            if (move_uploaded_file($_FILES["upfile"]["tmp_name"], $jsn_file)){
				// change the permission of the file.
				chmod($jsn_file, 0777);
				
				$string = file_get_contents($jsn_file);
				$json = json_decode($string, true);
				
				foreach($json["features"] as $features){
					echo "<h4>FeatureType</h4>";
					echo "<p>・".$features["type"]."</p><hr />";
					echo "<h4>Attributes</h4>";
					foreach($features["properties"] as $key => $val){
						echo "<p>・".$key.":".$val."</p>";
					}
					echo "<hr /><h4>Geometry</h4>";
					if($features["geometry"]["type"]==="MultiPolygon"){
						// Open the file for writing.
						$wkt_obj = fopen($wkt_file,"w");
						
						$wkt = "MULTIPOLYGON(";
						$polys = "";
						foreach($features["geometry"]["coordinates"] as $polygons){
							$polys = $polys."(";
							$poly = "";
							foreach($polygons as $polygon){
								$poly = $poly."(";
								$coods = "";
								foreach($polygon as $coordinates){
									// The set of coordinates.
									$coods = $coods.$coordinates[0]." ".$coordinates[1].",";
								}
								$poly = $poly.trim($coods,",").")";
							}
							$polys = $polys.$poly."),";
						}
						$wkt = $wkt.trim($polys,",").")";
						echo $wkt;
						
						// Write WKT formatted text.
						echo fwrite($wkt_obj, $wkt);
						fclose($wkt_obj);
					} elseif($features["geometry"]["type"]==="Polygon"){
						// Open the file for writing.
						$wkt_obj = fopen($wkt_file,"w");
						
						$wkt = "MULTIPOLYGON(";
						$polys = "";
						foreach($features["geometry"]["coordinates"] as $polygons){
							$polys = $polys."(";
							$poly = "";
							foreach($polygons as $polygon){
								$poly = $poly."(";
								$coods = "";
								foreach($polygon as $coordinates){
									// The set of coordinates.
									$coods = $coods.$coordinates[0]." ".$coordinates[1].",";
								}
								$poly = $poly.trim($coods,",").")";
							}
							$polys = $polys.$poly."),";
						}
						$wkt = $wkt.trim($polys,",").")";
						echo $wkt;
						
						// Write WKT formatted text.
						echo fwrite($wkt_obj, $wkt);
						fclose($wkt_obj);
					}
				}
            }
        }
    ?>
	</body>
</html>