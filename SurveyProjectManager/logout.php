<?php
  // Start the session.
	session_start();
	
	// Check the current condition.
	if (isset($_SESSION["USERNAME"])) {
		$err = "ログアウトしました。";
			
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
		$err = "セッションがタイムアウトしました。";
	}
	
	// Clear session information.
	$_SESSION = array();
	@session_destroy();
?>
 
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Logout</title>
		
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
		<script src="lib/jquery-3.1.1/jquery.min.js"></script>
		\n
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/bootstrap.min.js"></script>
		
		<!-- Import external scripts for generating image -->
		<script type="text/javascript" src="lib/refreshImage.js"></script>
		
		<!-- Import external scripts for calendar control -->
		<link rel="stylesheet" type="text/css" href="lib/calendar/codebase/dhtmlxcalendar.css"/>
		<script src="lib/calendar/codebase/dhtmlxcalendar.js"></script>
		<script type="text/javascript" src="lib/calendar.js"></script>
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
					<h1><?php echo $err; ?></h1>
			</div>
		</div>
    </body>
</html>