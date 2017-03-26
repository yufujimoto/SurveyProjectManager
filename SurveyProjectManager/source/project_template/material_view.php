<?php
	header("Content-Type: text/html; charset=UTF-8");
	
	require_once "lib/config.php";
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	//$prj_id= $_REQUEST['prj_id'];
	$con_id= $_REQUEST['con_id'];
	$mat_id= $_REQUEST['uuid'];
	
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
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
	// Create a SQL query string.
	$sql_sel_mat = "SELECT
		uuid,
		name,
		estimated_period_beginning,
		estimated_period_ending,
		represented_point,
		path,
		area,
		material_number,
		descriptions
		FROM material WHERE uuid = '".$mat_id."'";
	
	// Excute the query and get the result of query.
	$sql_res_mat = pg_query($conn, $sql_sel_mat);
	if (!$sql_res_mat) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_mat = pg_fetch_all($sql_res_mat);
	
	$timezone = "'Asia/Tokyo'";
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Details about Material</title>
		
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta name="Yu Fujimoto" content="" />
		<link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
		<link href="../../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" />
		<link href="../../theme.css" rel="stylesheet" />
		
		<!-- Import external scripts for Bootstrap CSS -->
		<script src="lib/jquery-3.1.1/jquery.min.js"></script>
		<script src="../../bootstrap/js/bootstrap.js"></script>
		<script src="../../bootstrap/js/bootstrap.min.js"></script>
		
		<!-- Import external scripts for generating image -->
		<script type="text/javascript" src="lib/refreshImage.js"></script>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <style>
          .carousel-inner > .item > img,
          .carousel-inner > .item > a > img {
              height: 400px;
			  width: auto;
              margin: auto;
          }
        </style>
        
        <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    </head>
    <body　role="document">
        <!-- Fixed navbar -->
        <div class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="../index.php">Research Page</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav" >
                        <li><a href="index.php">ホーム</a></li>
                        <li><a href="introduction.php">概要</a></li>
                        <li><a href="report.php">プロジェクト報告</a></li>
                        <li><a href="archive.php">アーカイブ</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li>
                            <div id="google_translate_element"></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
		
		<!-- Control Menu -->
		<div class="container" style="padding-top: 50px">
			<div id="main" class="row">
				<table id="operation" class="table" style="padding: 0px; margin: 0px">
					<thead style="text-align: center">
						<!-- Main Label of CSV uploader -->
						<tr>
							<td>
								<h2>資料細目</h2>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
									<button id="btn_bck_mat"
											name="btn_bck_mat"
											class="btn btn-sm btn-default"
											type="submit" value="backToMaterial"
											onclick="window.location.href='material.php?uuid=<?php echo $con_id; ?>';">
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> 資料管理に戻る</span>
									</button>
							</td>
						</tr>
						<!-- Display Errors -->
						<tr>
							<td>
								<p style="color: red; text-align: left"><?php echo $err; ?></p>
							</td>
						</tr>
					</thead>
				</table>
			</div>
			
			<!-- Contents -->
			<!-- Consolidation list -->
			<div class="row">
		        <div class="col-sm-6">
		            <h4>画像資料</h4>
		            <div id="myCarousel" class="carousel slide" data-ride="carousel">
			            <?php
				            // Get user name by uuid. 
				            $sql_sel_fig = "SELECT * FROM digitized_image WHERE mat_id = '" .$mat_id. "' ORDER BY id";
				            $sql_res_fig = pg_query($conn, $sql_sel_fig) or die('Query failed: ' . pg_last_error());
				            $rows_fig = pg_fetch_all($sql_res_fig);
				            
				            echo "<ol class='carousel-indicators'>\n";
				            for ($i = 0; $i <= count($rows_fig)-1; $i++) {
				                if($i=="0"){
                                    echo "\t\t\t\t\t\t\t<li data-target='#myCarousel' data-slide-to='".$i."' class='active'></li>\n";
                                } else {
                                    echo "\t\t\t\t\t\t\t<li data-target='#myCarousel' data-slide-to='".$i."'></li>\n";
                                }
                            }
                            echo "\t\t\t\t\t\t</ol>\n";
                            
                            $img_cnt=0;
                            echo "\t\t\t\t\t\t<div class='carousel-inner' role='listbox'>\n";
				            foreach ($rows_fig as $row_fig){
					            $img_id = $row_fig['uuid'];
					            $img_dtc = $row_fig['exif_datetime'];
					            $img_dsc = $row_fig['descriptions'];
					            $src_img = "material_image_view.php?uuid=".$img_id;
					            
					            if ($img_cnt=="0") {
					                echo "\t\t\t\t\t\t\t<div class='item active'>\n";
					                echo "\t\t\t\t\t\t\t\t<img height='400px' src='".$src_img."' alt='".$src_img."'/>\n";
					                echo "\t\t\t\t\t\t\t\t<div class='caption'>\n";
					                echo "\t\t\t\t\t\t\t\t\t<p>".$img_dsc."</p>\n";
					                echo "\t\t\t\t\t\t\t\t</div>\n";
					                echo "\t\t\t\t\t\t\t</div>\n";
					            } else {
					                echo "\t\t\t\t\t\t\t<div class='item'>\n";
					                echo "\t\t\t\t\t\t\t\t<img height='400px' src='".$src_img."' alt='".$src_img."'/>\n";
					                echo "\t\t\t\t\t\t\t\t<div style='margin-right: 20px; margin-left: 20px'>\n";
					                echo "\t\t\t\t\t\t\t\t\t<p>".$img_dsc."</p>\n";
					                echo "\t\t\t\t\t\t\t\t</div>\n";
					                echo "\t\t\t\t\t\t\t</div>\n";
					            }
					            
					            $img_cnt=$img_cnt+1;
				            }
				            echo "\t\t\t\t\t\t</div>\n";
		                ?>
                        <!-- Left and right controls -->
                        <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
	                </div>
		        </div>
		        <div class="col-sm-6">
				    <?php
					    // For each row, HTML list is created and showed on browser.
					    foreach ($rows_mat as $row_mat){
						    // Get a value in each field.
						    $mat_nam = $row_mat['name'];
						    $mat_est_bgn = $row_mat['estimated_period_beginning'];
						    $mat_est_end = $row_mat['estimated_period_ending'];
						    $mat_pnt = $row_mat['represented_point'];
						    $mat_pth = $row_mat['path'];
						    $mat_are = $row_mat['area'];
						    $mat_mnm = $row_mat['material_number'];
						    $mat_dsc = $row_mat['descriptions'];
						
						    $sql_sel_mat_add = "SELECT
							    uuid,
							    key,
							    value,
							    type
							    FROM additional_information WHERE mat_id = '".$mat_id."' ORDER BY id";
						
						    // Excute the query and get the result of query.
						    $sql_res_mat_add = pg_query($conn, $sql_sel_mat_add);
						    if (!$sql_res_mat_add) {
							    // Get the error message.
							    $err = "DB Error: ".pg_last_error($conn);
							
							    // Move to Main Page.
							    header("Location: main.php?err=".$err);
							    exit;
						    }
						    // Get additional Attributes of the material
						    $rows_mat_add = pg_fetch_all($sql_res_mat_add);
						
						    // Give the table id by using report id.
						    $tbl_mat_id = "tbl_img_".$mat_id;
					    }
				    ?>
					<h4>基本属性</h4>
					<table class="table-striped" style="width: 100%;">
						<tr style="height: 45px">
							<td style="width: 100px; text-align: center;">資料番号</td>
							<td >: <?php echo $mat_mnm; ?></td>
						</tr>
						<tr style="height: 45px">
							<td style="width: 100px; text-align: center">資料名</td>
							<td >: <?php echo $mat_nam; ?></td>
						</tr>
						<tr style="height: 45px">
							<td style="width: 100px; text-align:center">存在期間</td>
							<td>: <?php echo $mat_est_bgn; ?>〜<?php echo $mat_est_end; ?></td>
						</tr>
						<tr style="height: 45px">
							<td style="width: 100px; text-align: center">備考</td>
							<td >: <?php echo $mat_dsc; ?></td>
						</tr>
					<?php
						foreach ($rows_mat_add as $row_mat_add){
							$mat_add_id = $row_mat_add['uuid'];
							$mat_add_key = $row_mat_add['key'];
							$mat_add_val = $row_mat_add['value'];
							$mat_add_typ = $row_mat_add['type'];
						
							if($mat_add_key!="" or $mat_add_val!=""){
								echo "\t\t\t\t\t\t\t\t<tr style='height: 45px'>\n";
								echo "\t\t\t\t\t\t\t\t\t<td style='width: 100px; text-align: center'>".$mat_add_key."</td>\n";
								echo "\t\t\t\t\t\t\t\t\t<td>: ".$mat_add_val."</td>\n";
								echo "\t\t\t\t\t\t\t\t</tr>\n";
							}
						}
					?>
					</table>
					
					<hr />
					
					<h4>関連資料</h4>
					<table class="table-striped" style="width: 100%;text-align: center">
						<tr>
							<td style="text-align: center">資料名</td>
							<td style="text-align: center">資料番号</td>
							<td style="width: 100px;text-align: center">サムネイル</td>
						</tr>
						
					<?php
						$sql_sel_m2m = "SELECT
							    relating_to
							    FROM material_to_material WHERE relating_from = '".$mat_id."' ORDER BY relating_to";
						
						// Excute the query and get the result of query.
						$sql_res_m2m = pg_query($conn, $sql_sel_m2m);
						
						// Get additional Attributes of the material
						$rows_m2m = pg_fetch_all($sql_res_m2m);
						
						foreach ($rows_m2m as $row_m2m){
							echo "<tr>\n";
							$mat_m2m = $row_m2m['relating_to'];
							
							$sql_sel_rel_mat = "SELECT name, material_number FROM material WHERE uuid = '".$mat_m2m."'";
							$sql_res_rel_mat = pg_query($conn, $sql_sel_rel_mat);
							$rows_rel_mat = pg_fetch_all($sql_res_rel_mat);
							
							foreach ($rows_rel_mat as $row_rel_mat){
								$row_rel_mat_nam = $row_rel_mat['name'];
								$row_rel_mat_num = $row_rel_mat['material_number'];
								
								$sql_sel_img_rel = "SELECT uuid FROM digitized_image WHERE mat_id='" .$mat_m2m."'" ;
								$sql_res_img_rel = pg_query($sql_sel_img_rel);
								$img_cnt = 0 + intval(pg_num_rows($sql_res_img_rel));
								
								echo "<td vertical-align: middle;'>".$row_rel_mat_nam."</td>";
								echo "<td vertical-align: middle;'>".$row_rel_mat_num."</td>";
								
								if($img_cnt > 0){
									echo "<td vertical-align: middle;'>";
									echo "<a href='material_view.php?uuid=".$mat_m2m."&con_id=".$con_id."'>";
									echo "<img height=80 src='avatar_material.php?uuid=" .$mat_m2m."' alt='img'/></a></td>";
								} else {
									echo "<td vertical-align: middle;'>";
									echo "<a href='material_view.php?uuid=".$mat_m2m."&con_id=".$con_id."'>";
									echo "<img height=80 src='images/noimage.jpg' alt='img'/></a></td>";
								}
							}
							
							echo "</tr>\n";
						}
						
						// Close the connection to the database.
						pg_close($conn);
					?>
					</table>
				</div>
			</div>
		</div>
		
		<script type="text/javascript">
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'ja',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                    multilanguagePage: true,
                    gaTrack: true,
                    gaId: 'UA-93928611-1'
                    }, 'google_translate_element'
                );
            }
        </script>
		
		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
			
			ga('create', 'UA-93928611-1', 'auto');
			ga('send', 'pageview');
		</script>
	</body>
</html>
