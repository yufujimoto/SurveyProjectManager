<!doctype html>
<?php
    // Start the new session.
    session_start();
    
    // Initialize the error Message.
    $errMsg = $_GET["msg"];
    
    if (isset($_POST["submit"])) {
        // Check the user name.
        if (empty($_POST["dbhost"])) {
            $errMsg = "ホスト名が未入力です。";
        } else if (empty($_POST["dbport"])) {
            $errMsg = "ポート番号が未入力です。";
        } else if (empty($_POST["dbname"])) {
            $errMsg = "データベース名が未入力です。";
        } else if (empty($_POST["dbuser"])) {
            $errMsg = "ユーザー名が未入力です。";
        } else if (empty($_POST["dbpass"])) {
            $errMsg = "パスワードが未入力です。";
        }
		} else if (empty($_POST["srid"])) {
            $errMsg = "SRIDが未入力です。";
        } 
        
        // Authenticate 
        if (!empty($_POST["dbuser"]) && !empty($_POST["dbpass"])) {
			$dbuser = $_POST["dbuser"];
            $dbpass = $_POST["dbpass"];
            
            if (!empty($_POST["dbhost"]) && !empty($_POST["dbport"]) && !empty($_POST["dbname"])) {
                $dbhost = $_POST["dbhost"];
                $dbport = $_POST["dbport"];
                $dbname = $_POST["dbname"];
				$srid = $_POST["srid"];
                
                $dbconn = pg_connect("host=".$dbhost." port=".$dbport." dbname=".$dbname." user=".$dbuser." password=".$dbpass);
                
                if (!empty($dbconn)) {
                    $configfile = fopen("lib/config.php", "w");
                    
                    fwrite($configfile, '<?php'."\n");
                    fwrite($configfile, "\t".'header("Content-Type: text/html; charset=UTF-8");'."\n");
                    fwrite($configfile, "\t".'define("DBUSER", "'.$dbuser.'");'."\n");
                    fwrite($configfile, "\t".'define("DBPASS", "'.$dbpass.'");'."\n");
                    fwrite($configfile, "\t".'define("DBHOST", "'.$dbhost.'");'."\n");
                    fwrite($configfile, "\t".'define("DBNAME", "'.$dbname.'");'."\n");
                    fwrite($configfile, "\t".'define("DBPORT", "'.$dbport.'");'."\n");
					fwrite($configfile, "\t".'define("SRID", "'.$srid.'");'."\n");
                    fwrite($configfile, '?>');
                    
                    fclose($configfile);
                    header("Location: login.php");
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
?>
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
                            <td><input type="text" name="dbhost" size="50" maxlength="150" value="localhost" class="form-control" value="<?php echo htmlspecialchars($_POST['username'], ENT_QUOTES); ?>"</td>
                        </tr>
                        <tr>
                            <td style="width: 150px">ポート番号</td>
                            <td><input type="text" name="dbport" size="50" maxlength="150" value="5432" class="form-control" value="<?php echo htmlspecialchars($_POST['username'], ENT_QUOTES); ?>"</td>
                        </tr>
                        <tr>
                            <td style="width: 150px">データベース名</td>
                            <td><input type="text" name="dbname" size="50" maxlength="150" value="manager" class="form-control" value="<?php echo htmlspecialchars($_POST['username'], ENT_QUOTES); ?>"</td>
                        </tr>
                        <tr>
                            <td style="width: 150px">ユーザー名</td>
                            <td><input type="text" name="dbuser" size="50" maxlength="150" value="postgres" class="form-control" value="<?php echo htmlspecialchars($_POST['username'], ENT_QUOTES); ?>"</td>
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
                            <td colspan="2" style="text-align: right"><input type="submit" id="submit" name="submit" value="設定" class='btn btn-default'></td>
                        </tr>
                    </table><br />
                </form>
            </div>
        </div>
    </body>
</html>
