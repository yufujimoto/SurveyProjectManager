<!doctype html>
<?php
    // Load the library for password check.
    require "lib/password.php";
    require "lib/config.php";
    
    // Start the new session.
    session_start();
    
    // Initialize the error Message.
    $errMsg = "";
    
    if (isset($_POST["login"])) {
        // Check the user name.
        if (empty($_POST["username"])) {
        $errMsg = "ユーザー名が未入力です。";
        } else if (empty($_POST["password"])) {
        $errMsg = "パスワードが未入力です。";
        } 
        
        // Authenticate 
        if (!empty($_POST["username"]) && !empty($_POST["password"])) {
			$username = $_POST["username"];
            
			$dbconn = pg_connect("host=".DBHOST." port=5432 dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
			
            // Run SQL query.
            $query = "SELECT * FROM member WHERE username = '" . $username . "'";
            $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());
            while ($row = pg_fetch_assoc($result)) {
                $db_hashed_pwd = $row['password'];
				$username = $row['username'];
				$usertype = $row['usertype'];
            }
            
            // Close the connection.
            pg_close();
            
            // Check the password.
            if (password_verify($_POST["password"], $db_hashed_pwd)) {
				session_regenerate_id(true);
				$_SESSION["USERNAME"] = $username;
				$_SESSION["USERTYPE"] = $usertype;
				header("Location: main.php");
				exit;
            } else {
				$errMsg = "メールアドレスあるいはパスワードが間違っています";
            }
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
                        <tr>
                            <th align="left" style="font-size: large">ログイン</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="username" size="50" maxlength="150" placeholder="ユーザー名" class="form-control" value="<?php echo htmlspecialchars($_POST['username'], ENT_QUOTES); ?>"</td>
                        </tr>
                        <tr>
                            <td><input type="password" name="password" size="50" maxlength="150"  placeholder="パスワード" class='form-control' value=""/><br /></td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><input type="submit" id="login" name="login" value="ログイン" class='btn btn-default'></td>
                        </tr>
                    </table><br /><p><?php echo $errMsg ?></p>
                </form>
            </div>
        </div>
    </body>
</html>
