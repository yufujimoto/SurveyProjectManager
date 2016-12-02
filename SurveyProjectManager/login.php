<?php  
    // Start the session.
    session_start();
    
    // Initialize the error Message.
    $err = "";
	
	if (!file_exists("lib/config.php")) {
		$err = "データベースの設定ファイルがありません。";
		header("Location: index.php?msg=".$err);
	}
    
    // Load the library for password check.
    require "lib/password.php";
    require "lib/config.php";
	
    if (isset($_REQUEST["login"])) {
        // Check the user name.
        if (empty($_REQUEST["username"])) {
        $err = "ユーザー名が未入力です。";
        } else if (empty($_REQUEST["password"])) {
        $err = "パスワードが未入力です。";
        } 
        
        // Authenticate 
        if (!empty($_REQUEST["username"]) && !empty($_REQUEST["password"])) {
			$username = $_REQUEST["username"];
            
			$conn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS);
			
			if (!empty($conn)) {				
				// Check the number of user
				$sql_select_mem_all = "SELECT * FROM member";
				$sql_result_mem_all = pg_fetch_all($sql_select_mem_all);
				$row_cnt = 0 + intval(pg_num_rows($sql_result_mem_all));
				
				// Move to user registration page if there is no members.
				//　Generate the error message.
				$err="ログインできるユーザーがありあません。ユーザーを作成してください。";
				if ($row_cnt == 0){
					header("Location: add_member.php?err='".$err."'");
				}
				
				// Find the login user.
				$sql_select_mem = "SELECT * FROM member WHERE username = '" . $username . "'";
				$sql_result_mem = pg_query($conn, $sql_select_mem) or die('Query failed: ' . pg_last_error());
				while ($row = pg_fetch_assoc($sql_result_mem)) {
					$password = $row['password'];
					$username = $row['username'];
					$usertype = $row['usertype'];
				}
				
				// Check the password, and move to main page if successfully authorized.
				if (password_verify($_REQUEST["password"], $password)) {
					session_regenerate_id(true);
					
					// Set session information.
					$_SESSION["USERNAME"] = $username;
					$_SESSION["USERTYPE"] = $usertype;
					
					// Move to main page.
					header("Location: main.php");
					exit;
				} else {
					$err = "メールアドレスあるいはパスワードが間違っています";
				}
			} else {
				$err = "データベースの設定ができていません。";
				header("Location: index.php?msg=".$err);
			}
        }
    
    }
	// close the connection to DB.
	pg_close($conn);
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Login</title>
		
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
		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
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
                            <td>
								<input id="username" 
									   name="username"
									   class="form-control"
									   type="text"
									   size="50"
									   maxlength="150"
									   placeholder="ユーザー名"
									   value="<?php echo htmlspecialchars($_REQUEST['username'], ENT_QUOTES); ?>"/>
							</td>
                        </tr>
                        <tr>
                            <td>
								<input id="password"
									   name="password"
									   class='form-control'
									   type="password"
									   size="50"
									   maxlength="150"
									   placeholder="パスワード"
									   value=""/>
								<br />
							</td>
                        </tr>
                        <tr>
                            <td style="text-align: right">
								<input id="login"
									   name="login"　
									   class='btn btn-default'
									   type="submit"
									   value="ログイン">
							</td>
                        </tr>
                    </table><br /><p><?php echo $err ?></p>
                </form>
            </div>
        </div>
    </body>
</html>
