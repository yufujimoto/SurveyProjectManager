<!doctype html>
<?php
    // Start the new session.
    session_start();
    
    // Initialize the error Message.
    $errMsg = $_REQUEST["msg"];
    
    if (isset($_REQUEST["submit"])) {
		$errFlg = "";
		
        // Check the user name.
        if (empty($_REQUEST["dbhost"])) {
            $errMsg = "ホスト名が未入力です。";
			$errFlg = "ERROR";
        } else if (empty($_REQUEST["dbport"])) {
            $errMsg = "ポート番号が未入力です。";
			$errFlg = "ERROR";
        } else if (empty($_REQUEST["dbname"])) {
            $errMsg = "データベース名が未入力です。";
			$errFlg = "ERROR";
        } else if (empty($_REQUEST["dbuser"])) {
            $errMsg = "ユーザー名が未入力です。";
			$errFlg = "ERROR";
        } else if (empty($_REQUEST["dbpass"])) {
            $errMsg = "パスワードが未入力です。";
			$errFlg = "ERROR";
        } else if (empty($_REQUEST["srid"])) {
            $errMsg = "SRIDが未入力です。";
			$errFlg = "ERROR";
        } else if (empty($_REQUEST["fullpath"])) {
			$errMsg = "フルパスが未入力です。";
			$errFlg = "ERROR";
			if (!file_exists($_REQUEST["fullpath"])){
				$errMsg = "フルパスが未入力です。";
				$errFlg = "ERROR";
			}
        }
		
		if ($errFlg != "ERROR") {
			// Authenticate 
			if (!empty($_REQUEST["dbuser"]) && !empty($_REQUEST["dbpass"])) {
				$dbuser = $_REQUEST["dbuser"];
				$dbpass = $_REQUEST["dbpass"];
				$fullpath = $_REQUEST["fullpath"];
				
				if (!empty($_REQUEST["dbhost"]) && !empty($_REQUEST["dbport"]) && !empty($_REQUEST["dbname"])) {
					$dbhost = $_REQUEST["dbhost"];
					$dbport = $_REQUEST["dbport"];
					$dbname = $_REQUEST["dbname"];
					$srid = $_REQUEST["srid"];
					
					$conn = pg_connect("host=".$dbhost." port=".$dbport." dbname=".$dbname." user=".$dbuser." password=".$dbpass);
					
					if (!empty($conn)) {
						$connfigfile = fopen("lib/config.php", "w");
						
						// close the connection to DB.
						pg_close($conn);
						
						fwrite($connfigfile, '<?php'."\n");
						fwrite($connfigfile, "\t".'header("Content-Type: text/html; charset=UTF-8");'."\n");
						fwrite($connfigfile, "\t".'define("DBUSER", "'.$dbuser.'");'."\n");
						fwrite($connfigfile, "\t".'define("DBPASS", "'.$dbpass.'");'."\n");
						fwrite($connfigfile, "\t".'define("DBHOST", "'.$dbhost.'");'."\n");
						fwrite($connfigfile, "\t".'define("DBNAME", "'.$dbname.'");'."\n");
						fwrite($connfigfile, "\t".'define("DBPORT", "'.$dbport.'");'."\n");
						fwrite($connfigfile, "\t".'define("SRID", "'.$srid.'");'."\n");
						fwrite($connfigfile, "\t".'define("FULLPATH", "'.$fullpath.'");'."\n");
						fwrite($connfigfile, '?>');
						fclose($connfigfile);
						
						// Create the temporal first user for setting up this system.
						$_SESSION["USERNAME"] = "FIRSTUSER";
						$_SESSION["USERTYPE"] = "Administrator";
						
						header("Location: add_member.php?err=初期設定用ユーザーの作成");
					} else {
						$errMsg = "データベースに接続できません";
					}
					
				} else {
					$errMsg = "データベースの設定情報を確認してください";
				}
			} else {
				$errMsg = "ユーザー名とパスワードの設定情報を確認してください";
			}
		}
	}
?>
<html lang="ja">
    <head>
		<title>データベース設定</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="Content-Script-Type" content="text/javascript">
		<meta http-equiv="Content-Style-Type" content="text/css">
		<meta name="description" content="">
		<meta name="Yu Fujimoto" content="">
		<link rel="icon" href="../favicon.ico">
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
				</ul>
			  </div><!--/.nav-collapse -->
			</div>
		</div>
		<div class="container" style="padding-top: 70px">
		    <div class="row" align="center">
                <form id="loginForm" name="loginForm" action="" method="POST">
                    <table border="0">
                        <tr><td colspan="2" style="text-align: left; color: red"><p><?php echo $errMsg ?><br /></p></td></tr>
                        <tr>
                            <td style="width: 150px">ホスト名</td>
                            <td><input type="text" name="dbhost" size="50" maxlength="150" value="localhost" class="form-control" value="<?php echo htmlspecialchars($_REQUEST['username'], ENT_QUOTES); ?>"</td>
                        </tr>
                        <tr>
                            <td style="width: 150px">ポート番号</td>
                            <td><input type="text" name="dbport" size="50" maxlength="150" value="5432" class="form-control" value="<?php echo htmlspecialchars($_REQUEST['username'], ENT_QUOTES); ?>"</td>
                        </tr>
                        <tr>
                            <td style="width: 150px">データベース名</td>
                            <td><input type="text" name="dbname" size="50" maxlength="150" value="manager" class="form-control" value="<?php echo htmlspecialchars($_REQUEST['username'], ENT_QUOTES); ?>"</td>
                        </tr>
                        <tr>
                            <td style="width: 150px">ユーザー名</td>
                            <td><input type="text" name="dbuser" size="50" maxlength="150" value="postgres" class="form-control" value="<?php echo htmlspecialchars($_REQUEST['username'], ENT_QUOTES); ?>"</td>
                        </tr>
                        <tr>
                            <td style="width: 150px">パスワード</td>
                            <td><input type="password" name="dbpass" size="50" maxlength="150" class='form-control' value=""/><br /></td>
                        </tr>
						<tr>
                            <td style="width: 150px">SRID（空間参照）</td>
                            <td><input type="text" name="srid" size="50" maxlength="150" class='form-control' value="4612"/><br /></td>
                        </tr>
						<tr>
                            <td style="width: 150px">フルパス</td>
                            <td><input type="text" name="fullpath" size="50" maxlength="150" class='form-control' value="/SurveyProjectManager/SurveyProjectManager"/><br /></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: right"><input type="submit" id="submit" name="submit" value="設定" class='btn btn-default'></td>
                        </tr>
                    </table><br />
                </form>
            </div>
        </div>
    </body>
</html>
