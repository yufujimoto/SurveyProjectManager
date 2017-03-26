<?php
	require "lib/config.php";
	
	header("Content-Type: text/html; charset=UTF-8");
	
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
		header("Location: archive.php?err=".$err);
		exit;
	}
	
	// Get the consolidation id from the post.
	$con_id= $_REQUEST['uuid'];
	
	// Get a list of registered project.
	// Create a SQL query string.
	$sql_sel_mat = "SELECT  uuid,
							material_number,
							estimated_period_beginning,
							estimated_period_ending,
							descriptions
					FROM material
					WHERE con_id = '".$con_id."'
					ORDER by id";
	
	// Excute the query and get the result of query.
	$sql_res_mat = pg_query($conn, $sql_sel_mat);
	if (!$sql_res_mat) {
		// Print the error messages and exit routine if error occors.
		$err = "An error occurred in DB query.\n";
		
		// Move to Main Page.
		header("Location: archive.php?err=".$err);
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_mat = pg_fetch_all($sql_res_mat);
	
	// Get the total number of the registered project
	$row_mat_cnt = 0 + intval(pg_num_rows($sql_res_mat));
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="Yu Fujimoto" content="">
        <link rel="icon" href="../favicon.ico">
        <title>Home</title>
        <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
        <link href="../../theme.css" rel="stylesheet">
        
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
                        </li>
                    </ul>
                </div>
            </div>
        </div>
		
		<!-- Main containts -->
		<div id="container" class="container" style="padding-top: 70px">
			<!-- Control -->
			<div class="btn-group">
				<button id="btn_mv_arc"
						name="btn_mv_arc"
						class="btn btn-md btn-default"
						onclick="window.location.href='archive.php';">
					<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> Return to Archive</span>
				</button>
			</div>
			<hr />
			
			<!-- Header message -->
			<h3><?php echo $row_mat_cnt; ?>件の資料が登録されています。</h3>
			
			<!-- Material list -->
			<hr />
			
			<?php
				foreach ($rows_mat as $row_mat){
					$mat_uid = $row_mat['uuid'];
					$mat_nam = $row_mat['name'];
					$mat_num = $row_mat['material_number'];
					$mat_beg = $row_mat['estimated_period_beginning'];
					$mat_end = $row_mat['estimated_period_ending'];
					$mat_dsc = $row_mat['descriptions'];
					
					$sql_select_images = "SELECT uuid FROM digitized_image WHERE mat_id='" .$mat_uid."'" ;
					$res_select_images = pg_query($sql_select_images);
					$img_count = 0 + intval(pg_num_rows($res_select_images));
					
					echo "<div class='row'>\n";
					
					if($img_count > 0){
						echo "\t\t\t\t\t<div class='col-md-4'>\n";
						echo "\t\t\t\t\t\t<div style='padding: 0px; margin: 0px; width:100%; line-height: 150px; text-align: center; background-color: black;'>\n";
						echo "\t\t\t\t\t\t\t<a href='material_view.php?uuid=".$mat_uid."&con_id=".$con_id."'>\n";
						echo "\t\t\t\t\t\t\t\t<img style='max-width: 100%;' height=150 src='avatar_material.php?uuid=" .$mat_uid."' alt='img'/>\n";
						echo "\t\t\t\t\t\t\t</a>";
						echo "\t\t\t\t\t\t</div>";
						echo "\t\t\t\t\t</div>";
					} else {
						echo "\t\t\t\t\t<div class='col-md-4'>\n";
						echo "\t\t\t\t\t\t<div style='padding: 0px; margin: 0px; width:100%; line-height: 150px; text-align: center; background-color: black;'>\n";
						echo "\t\t\t\t\t\t\t<a href='material_view.php?uuid=".$mat_uid."&con_id=".$con_id."'>\n";
						echo "\t\t\t\t\t\t\t\t<img style='max-width: 100%;' height=150 src='images/noimage.jpg' alt='img'/>\n";
						echo "\t\t\t\t\t\t\t</a>\n";
						echo "\t\t\t\t\t\t</div>";
						echo "\t\t\t\t\t</div>";
					}
					echo "\t\t\t\t\t<div class='col-md-8'>\n";
					
					echo "\t\t\t\t\t\t<div class='row'>\n";
					echo "\t\t\t\t\t\t\t<div class='col-xs-6 col-md-2' style='text-align: center;'>\n";
					echo "\t\t\t\t\t\t\t\t<p style='margin: 10px;'>資料名称</p>\n";
					echo "\t\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t\t\t<div class='col-xs-6 col-md-10'>\n";
					echo "\t\t\t\t\t\t\t\t<p style='margin: 10px;'>".$mat_nam."</p>\n";
					echo "\t\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t\t</div>\n";
					
					echo "\t\t\t\t\t\t<div class='row' style='background-color: #f8f8f8;'>\n";
					echo "\t\t\t\t\t\t\t<div class='col-xs-6 col-md-2' style='text-align: center;'>\n";
					echo "\t\t\t\t\t\t\t\t<p style='margin: 10px;'>資料番号</p>\n";
					echo "\t\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t\t\t<div class='col-xs-6 col-md-10'>\n";
					echo "\t\t\t\t\t\t\t\t<p style='margin: 10px;'>".$mat_num."</p>\n";
					echo "\t\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t\t</div>\n";
					
					echo "\t\t\t\t\t\t<div class='row'>\n";
					echo "\t\t\t\t\t\t\t<div class='col-xs-6 col-md-2' style='text-align: center;'>\n";
					echo "\t\t\t\t\t\t\t\t<p style='margin: 10px;'>存在時期</p>\n";
					echo "\t\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t\t\t<div class='col-xs-6 col-md-10'>\n";
					echo "\t\t\t\t\t\t\t\t<p style='margin: 10px;'>".$mat_beg."〜".$mat_end."</p>\n";
					echo "\t\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t\t</div>\n";
					
					echo "\t\t\t\t\t\t<div class='row' style='background-color: #f8f8f8;'>\n";
					echo "\t\t\t\t\t\t\t<div class='col-xs-6 col-md-2' style='text-align: center;'>\n";
					echo "\t\t\t\t\t\t\t\t<p style='margin: 10px;'>特記事項</p>\n";
					echo "\t\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t\t\t<div class='col-xs-6 col-md-10'>\n";
					echo "\t\t\t\t\t\t\t\t<p style='margin: 10px;'>".$mat_dsc."</p>\n";
					echo "\t\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t\t</div>\n";
					
					echo "\t\t\t\t\t</div>\n";
					echo "\t\t\t\t</div>\n";
					echo "\t\t\t\t<hr style='margin: 0px; height: 0px;' />\n";
				}
				// Close the connection to the database.
				pg_close($conn);
			;?>
		</div>
		
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
