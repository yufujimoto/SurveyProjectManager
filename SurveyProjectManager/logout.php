<?php
session_start();
 
if (isset($_SESSION["USERNAME"])) {
  $errorMessage = "ログアウトしました。";
	
	// Delete temporal files updated by this user.
	$dir =  getcwd()."/uploads";
	$files = scandir($dir);
			foreach($files as $file){
				$findme   = $_SESSION["USERNAME"];
				$pos = strpos($file, $findme);
				
				if ($pos === false) {
						echo "";
				} else {
						unlink($dir."/".$file);
				}
			}
}
else {
  $errorMessage = "セッションがタイムアウトしました。";
}

// セッション変数のクリア
$_SESSION = array();
@session_destroy();
?>
 
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
	    <title>マイページ</title>
	    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
	    <link href="../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
	    <link href="../theme.css" rel="stylesheet">
    </head>
    <body>
	    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
		    <div class="container">
		      <div class="navbar-header">
			    <a class="navbar-brand">SurveyProjectManager</a>
		      </div>
		      <div class="collapse navbar-collapse">
			    <ul class="nav navbar-nav">
			      <li><a href="../index.php">Home</a></li>
			      <li><a href="login.php">ログイン</a></li>
			    </ul>
		      </div><!--/.nav-collapse -->
		    </div>
	    </div>
	    <div class="container" style="padding-top: 70px">
	        <div class="row" align="center">
                <h1><?php echo $errorMessage; ?></h1>
        </div>
    </div>
    </body>
