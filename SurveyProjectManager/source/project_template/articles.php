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
	$rep_id= $_REQUEST['uuid'];
	
	// Get a list of registered project.
	// Create a SQL query string.
	$sql_sel_sec = "SELECT  uuid,
							order_number,
							section_name,
							written_by,
							date_created,
							date_modified,
							body
					FROM section
					WHERE rep_id = '".$rep_id."'
					ORDER by order_number";
	
	// Excute the query and get the result of query.
	$sql_res_sec = pg_query($conn, $sql_sel_sec);
	if (!$sql_res_sec) {
		// Print the error messages and exit routine if error occors.
		$err = "An error occurred in DB query.\n";
		
		// Move to Main Page.
		header("Location: archive.php?err=".$err);
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_sec = pg_fetch_all($sql_res_sec);
	
	// Get the total number of the registered project
	$row_sec_cnt = 0 + intval(pg_num_rows($sql_res_sec));
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
        <link href="../../theme.css" rel="stylesheet">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
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
						onclick="window.location.href='report.php';">
					<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> Return to Reports</span>
				</button>
			</div>
			<hr />
			
			<!-- Header message -->
			<h3><?php echo $row_sec_cnt; ?>件のセクションが登録されています。</h3>
			
			<!-- Material list -->
			<hr />
			
			<?php
				foreach ($rows_sec as $row_sec){
					$sec_ord = $row_sec['order_number'] + 1;
					$sec_nam = $row_sec['section_name'];
					$sec_wtr = $row_sec['written_by'];
					$sec_beg = $row_sec['date_created'];
					$sec_end = $row_sec['date_modified'];
					$sec_bdy =  html_entity_decode($row_sec['body']);
					
					echo "<div class='row' style='padding:0px; background-color: #f8f8f8;'>\n";
					echo "\t\t\t\t\t<div class='col-xs-11' style='padding:0px'>\n";
					echo "\t\t\t\t\t\t<h3>".$sec_ord .". ". $sec_nam."</h3>\n";
					echo "\t\t\t\t\t\t<h4>".$sec_wtr."</h4>\n";
					echo "\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t<div class='col-xs-1' style='padding:0px'>\n";
					echo "\t\t\t\t\t\t\t<a style='width: 100%' data-toggle='collapse' href='#".$sec_ord."' class='btn btn-info'>\n";
					echo "\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-circle-arrow-down'></span> Open\n";
					echo "\t\t\t\t\t\t\t</a>\n";
					echo "\t\t\t\t\t</div>\n";
					echo "\t\t\t\t</div>\n";
					
					// Control menue
					echo "\t\t\t\t<div class='row' style='padding:0px'>\n";
					echo "\t\t\t\t\t<div class='col-xs-12' style='padding:0px'>\n";
					echo "\t\t\t\t\t\t\t<div id='".$sec_ord."' class='collapse'>\n";
					echo $sec_bdy."\n";
					echo "\t\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t</div>\n";
					echo "\t\t\t\t</div>\n";
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
