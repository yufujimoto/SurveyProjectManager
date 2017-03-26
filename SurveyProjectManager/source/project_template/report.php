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
	
	// Get a list of registered project.
	// Create a SQL query string.
	$sql_sel_rep = "SELECT
						uuid,
						title,
						volume,
						edition,
						series,
						publisher,
						year,
						descriptions
					FROM report
					WHERE prj_id = '".UUID."'
					ORDER by id";
	
	// Excute the query and get the result of query.
	$sql_res_rep = pg_query($conn, $sql_sel_rep);
	if (!$sql_res_rep) {
		// Print the error messages and exit routine if error occors.
		echo "An error occurred in DB query.\n";
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_rep = pg_fetch_all($sql_res_rep);
	$row_rep_cnt = 0 + intval(pg_num_rows($sql_res_rep));
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
                        </li>
                    </ul>
                </div>
            </div>
        </div>
		
		<!-- Main containts -->
		<div id="container" class="container" style="padding-top: 70px">
			
			<!-- Header message -->
			<hr />
			<h3><?php echo $row_rep_cnt; ?>件の報告書が登録されています。</h3>
			<hr />
			
			<!-- Members list -->
			<?php
				foreach ($rows_rep as $row_rep){
					$rep_uid = $row_rep['uuid'];
					$rep_ttl = $row_rep['title'];
					$rep_vol = $row_rep['volume'];
					$rep_edt = $row_rep['edition'];
					$rep_ser = $row_rep['series'];
					$rep_pub = $row_rep['publisher'];
					$rep_yer = $row_rep['year'];
					$rep_dsc = $row_rep['descriptions'];
					
					echo "<div class='row' style='padding:0px; background-color: #f8f8f8;'>\n";
					echo "\t\t\t\t\t<div class='col-xs-10' style='padding:0px'>\n";
					echo "\t\t\t\t\t\t<h3>".$rep_ttl."</h3>\n";
					echo "\t\t\t\t\t</div>\n";
					
					// Control menue
					echo "\t\t\t\t\t<div class='col-xs-2' style='text-align:right; line-height: 60px; padding:0px'>\n";
					echo "\t\t\t\t\t\t<div class='btn-group-vertical'>\n";
					echo "\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
					echo "\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
					echo "\t\t\t\t\t\t\t\t\t"."class='btn btn-lg btn-primary'\n";
					echo "\t\t\t\t\t\t\t\t\t"."type='submit'\n";
					echo "\t\t\t\t\t\t\t\t\t"."style='vertical-align: middle;'\n";
					echo "\t\t\t\t\t\t\t\t\t"."onclick='window.location.href=".'"articles.php?uuid='.$rep_uid.'"'."';>\n";
					echo "\t\t\t\t\t\t\t\t<span>報告書の閲覧</span>\n";
					echo "\t\t\t\t\t\t\t</button>\n";
					echo "\t\t\t\t\t\t</div>\n";
					echo "\t\t\t\t\t</div>\n";
					echo "\t\t\t\t</div>\n";
					
					echo "\t\t\t\t<div class='row' style='padding:0px'>\n";
					echo "\t\t\t\t\t<div class='col-xs-12' style='padding:0px'>\n";
					echo "\t\t\t\t\t\t<ul>\n";
					echo "\t\t\t\t\t\t\t<li><h4>巻号：". $rep_vol."(".$rep_edt.")</h4></li>\n";
					echo "\t\t\t\t\t\t\t<li><h4>シリーズ名：". $rep_ser. "</h4></li>\n";
					echo "\t\t\t\t\t\t\t<li><h4>出版者：". $rep_pub. "</h4></li>\n";
					echo "\t\t\t\t\t\t\t<li><h4>出版年：". $rep_yer. "</h4></li>\n";
					echo "\t\t\t\t\t\t\t<li style='text-align:justify;'><h4>概　要：</h4><p>". $rep_dsc. "</p></li>\n";
					echo "\t\t\t\t\t\t</ul>\n";
					echo "\t\t\t\t\t</div>\n";
					echo "\t\t\t\t</div>\n";
				}
				// Close the connection to the database.
				pg_close($conn);
			;?>
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
