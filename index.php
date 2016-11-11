<!doctype html>
<html lang="ja">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="Content-Script-Type" content="text/javascript">
		<meta http-equiv="Content-Style-Type" content="text/css">
		<meta name="description" content="">
		<meta name="Yu Fujimoto" content="">
		<link rel="icon" href="../favicon.ico">
		<title>メンバーログイン</title>
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
		<link href="theme.css" rel="stylesheet">
    </head>
    <body>
		<div class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="container">
                <div class="navbar-header">
                <a class="navbar-brand">SurveyProjectManager</a>
                </div>
			</div>
		</div>
		<div class="container" style="padding-top: 30px">
		    <div class="row" align="left">
                <h2><a href="SurveyProjectManager/login.php">Login to SurveyProjectManager.</a></h2>
                
                <?php
                    $dir = getcwd();
                    $files = scandir($dir);
                        foreach($files as $file){
                        if($file!="." and $file!=".." and $file!="SurveyProjectManager" and $file!="bootstrap" and $file!="index.php" and $file!="theme.css" and $file!="config.php"){
                            echo "<h2><a href=".$file.">Move to '".$file."' Project</a></h2>";
                        }
                    }
                ?>
            </div>
        </div>
    </body>
</html>
