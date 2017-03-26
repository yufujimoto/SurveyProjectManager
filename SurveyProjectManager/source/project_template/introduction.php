<?php
    require "lib/config.php";
    
    header("Content-Type: text/html; charset=UTF-8");
    
    // Open the connection to DB
    $conn = pg_connect("host=".DBHOST." port=5432 dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
    
    $query = "SELECT * FROM project WHERE uuid = '" . UUID . "'";
    $result = pg_query($conn, $query) or die('Query failed: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $name = $row['name'];
        $title = $row['title'];
        $intro = $row['introduction'];
        $cause = $row['cause'];
        $desc = $row['description'];
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
        <title>Image View</title>
        <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
        <link href="../../theme.css" rel="stylesheet">
    </head>
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
            <div class="row"><h1 style="text-align: center">ご挨拶</h1><hr />
            <div class="row">
                <h2>はじめに</h2>
                <p><?php echo $intro; ?></p>
            </div>
            <div class="row"><hr /></div>
            <div class="row">
                <h2>プロジェクトの経緯</h2>
                <p><?php echo $cause; ?></p>
            </div>
            <div class="row"><hr /></div>
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
