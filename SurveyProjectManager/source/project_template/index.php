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
    
    $query = "SELECT * FROM project WHERE uuid = '" . UUID . "'";
    $result = pg_query($conn, $query) or die('Query failed: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $name = $row['name'];
        $title = $row['title'];
    }
    
    // Close DB Connection.
    pg_close($conn);
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
        <div id="container" class="container" style="padding-top: 70px">
            <div class="jumbotron">
                <h3><?php echo $title; ?></h3>
                <h1><?php echo $name; ?></h1>
            </div>
      
            <div class="row" style='text-align:center'>
                <div class="col-lg-8">
                    <p class="lead">
                        <img width='800px' src='avatar_project_face.php?uuid=<?php echo UUID; ?>' alt='img' />
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="row" style='text-align:center'><p>
                        <form method="get" action="introduction.php">
                            <button style="width:300px;height:150px" type="submit" class="btn btn-lg btn-primary">プロジェクトの概要</button>
                        </form>
                    </p></div>
                    <div class="row" style='text-align:center'><p>
                        <form method="get" action="report.php">
                            <button style="width:300px;height:150px" type="submit" class="btn btn-lg btn-success">成果報告</button>
                        </form>
                    </p></div>
                    <div class="row" style='text-align:center'><p>
                        <form method="get" action="archive.php">
                            <button style="width:300px;height:150px" type="submit" class="btn btn-lg btn-warning">アーカイブの閲覧</button>
                        </form>
                    </p></div>
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
