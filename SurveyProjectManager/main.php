<?php
    // Load the DB settings.
    require "lib/config.php";
    
    // Start the session.
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
    
    // Connect to DB.
    $username = $_SESSION["USERNAME"];
    $dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
    $query = "SELECT * FROM member WHERE username = '" . $username . "'";
    $result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $surname = $row['surname'];
        $firstname = $row['firstname'];
		$usertype = $row['usertype'];
		$userid = $row['uuid'];
    }
    pg_close();
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
			      <li><a href="main.php">マイページ</a></li>
				  <?php
						if ($_SESSION["USERTYPE"] == "Administrator") {
							echo "<li><a href='project.php'>プロジェクトの管理</a></li>";
							echo "<li><a href='member.php'>メンバーの管理</a></li>";
						}
				  ?>
			      <li><a href="logout.php">ログアウト</a></li>
			    </ul>
		      </div><!--/.nav-collapse -->
		    </div>
	    </div>
	    <div class="container" style="padding-top: 30px">
	        <div class="row" align="center">
                <h1>ようこそ、<?php echo $surname." ".$firstname; ?>さん</h1>
				<hr />
			</div>
					<!-- Main containts -->
			<div id="main" class="row">
				<?php
					// Connect to the DB.
					$dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
					
					// Get a list of registered project.
					// Create a SQL query string.
					$sql_select_projects = "SELECT P.uuid, P.name, P.beginning, P.ending, P.phase, P.created, P.created_by FROM project AS P INNER JOIN role AS R ON R.prj_id = P.uuid WHERE R.mem_id = '".$userid."' ORDER by P.id";
					
					// Excute the query and get the result of query.
					$result_select_projects = pg_query($dbconn, $sql_select_projects);
					if (!$result_select_projects) {
						// Print the error messages and exit routine if error occors.
						echo "An error occurred in DB query.\n";
						exit;
					}
					
					// Fetch rows of projects. 
					$rows_project = pg_fetch_all($result_select_projects);
					$row_count = 0 + intval(pg_num_rows($result_select_projects));
					
					// Create section Label and show the total number of the registered project
					echo "<h3>" . $row_count ."件のプロジェクトに参加しています。</h3>\n";
					
					// A table for showing project list.
					echo "\t\t\t\t<table id='project' class='table table-striped' style='text-align:center; vertical-align:middle; padding:0px'>\n";
					echo "\t\t\t\t\t<form id='selection'>\n";
					echo "\t\t\t\t\t\t<thead><tr>\n";
					echo "\t\t\t\t\t\t\t<td style='width: auto'>プロジェクト名</td>\n";
					echo "\t\t\t\t\t\t\t<td style='width: 100px'>開始</td>\n";
					echo "\t\t\t\t\t\t\t<td style='width: 100px'>終了</td>\n";
					echo "\t\t\t\t\t\t\t<td style='width: 100px'>次数</td>\n";
					echo "\t\t\t\t\t\t\t<td>操作パネル</td>\n";
					echo "\t\t\t\t\t\t</tr></thead>\n";
					
					// For each row, HTML list is created and showed on browser.
					foreach ($rows_project as $row_project){
						// Get a value in each field.
						$prj_uuid = $row_project['uuid'];		// Internal identifier of the project
						$prj_name = $row_project['name'];	// Project name
						$prj_begin = $row_project['beginning'];	// The date of the project begining.
						$prj_end = $row_project['ending'];		// The date of the project ending.
						$prj_phase = $row_project['phase'];			// The phase for the continuous project.
						
						// Build HTML tag elements using aquired field values.
						echo "\t\t\t\t\t\t<tr>\n";
						echo "\t\t\t\t\t\t\t";
						echo '<td style="text-align: left"><a style="cursor: pointer;" onclick=moveToProjectView("'.$prj_uuid.'");> '.$prj_name .'</td>';
						echo "\n\t\t\t\t\t\t\t<td style='width: 100px;'>" . $prj_begin ."</td>\n";
						echo "\t\t\t\t\t\t\t<td style='width: 100px;'>" . $prj_end ."</td>\n";
						echo "\t\t\t\t\t\t\t<td style='width: 100px;'>第" . $prj_phase ."次調査</td>\n";
						echo "\t\t\t\t\t\t\t<td'>" . $prj_date ."</td>\n";
						echo "\t\t\t\t\t\t\t<td>";
						echo '<button class="btn btn-md btn-success" type="submit" id="deleteOne">資料編集</button> ';
						echo '<button class="btn btn-md btn-success" type="submit" id="deleteOne">報告書執筆</button>';
						echo "</td>\n";
						echo "\t\t\t\t\t\t</tr>\n";
					}
					echo "\t\t\t\t\t</form>\n";
					echo "\t\t\t\t</table>\n";
					
					pg_close($dbconn);
				?>
			</div>
		</div>
		<!-- Javascript -->
		<script type="text/javascript">
			// Moove to other page to show the summary of the project.
			function moveToProjectView(uuid) {
				window.location.href = "edit_project.php?uuid=" + uuid;
				return false;
			}
		</script>
    </body>
</html>
