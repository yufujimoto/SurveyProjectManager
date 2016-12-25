<?php
	// Start the session.
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	if ($_SESSION["USERTYPE"] != "Administrator") {
		header("Location: main.php");
	}
	
	// Load external libraries.
	require "lib/guid.php";
    require "lib/config.php";
	
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
    
    // Connect to DB.
    $username = $_SESSION["USERNAME"];
	// Connect to the DB.
	$conn = pg_connect(
				"host=".DBHOST."
				port=".DBPORT."
				dbname=".DBNAME."
				user=".DBUSER."
				password=".DBPASS
			) or die('Connection failed: ' . pg_last_error());
	
	// Get member information, who is currently logging in.
    $sql_sel_mem = "SELECT * FROM member WHERE username = '" . $username . "'";
    $sql_res_mem = pg_query($conn, $sql_sel_mem) or die('Query failed: ' . pg_last_error());
	
    while ($row = pg_fetch_assoc($sql_res_mem)) {
        $surname = $row['surname'];
        $firstname = $row['firstname'];
		$usertype = $row['usertype'];
		$userid = $row['uuid'];
    }
	
	// Get a list of registered project.
	// Create a SQL query string.
	$sql_sel_prj = "SELECT	P.uuid,
									P.name,
									P.beginning,
									P.ending,
									P.phase,
									P.created,
									P.created_by,
									P.faceimage
							FROM project AS P
							INNER JOIN role AS R ON R.prj_id = P.uuid
							WHERE R.mem_id = '".$userid."' ORDER by P.id";
	
	// Excute the query and get the result of query.
	$sql_res_prj = pg_query($conn, $sql_sel_prj);
	if (!$sql_res_prj) {
		// Print the error messages and exit routine if error occors.
		echo "An error occurred in DB query.\n";
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_prj = pg_fetch_all($sql_res_prj);
	$row_cnt = 0 + intval(pg_num_rows($sql_res_prj));
?>

<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>My Page</title>
		
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
			<!-- Page Header -->
			<div class="row">
				<table class="table">
					<thead style="text-align: center">
						<!-- Main Label of CSV uploader -->
						<tr>
							<td>
								<h2>ようこそ、<?php echo $surname." ".$firstname; ?>さん</h2>
							</td>
						</tr>
						<!-- Display Errors -->
						<tr>
							<td style="text-align: left">
								<div class="btn-group">
									<button id="btn_add_prj"
											name="btn_add_prj"
											class="btn btn-sm btn-default"
											type="submit"
											onclick="editMe('<?php echo $_SESSION["USERNAME"];?>');">
										<span class="glyphicon glyphicon-user" aria-hidden="true">ユーザーの編集</span>
									</button>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<p style="color: red; text-align: left"><?php echo $err; ?></p>
							</td>
						</tr>
					</thead>
				</table>
			</div>
					<!-- Main containts -->
			<div id="main" class="row">
				<h3><?php echo $row_cnt?>件のプロジェクトに参加しています。</h3>
			</div>
			<div class="row">
				<table id="project" class="table table-striped" style="text-align:center; vertical-align:middle; padding:0px">
					<thead>
						<tr>
							<td colspan="2" style="width: auto">プロジェクト名</td>
							<td style="width: 100px">開始</td>
							<td style="width: 100px">終了</td>
							<td style="width: 100px">次数</td>
							<td style="width: 160px">操作パネル</td>
						</tr>
					</thead>
					<?php
						// For each row, HTML list is created and showed on browser.
						foreach ($rows_prj as $row_project){
							// Get a value in each field.
							$prj_uuid = $row_project['uuid'];				// Internal identifier of the project
							$prj_name = $row_project['name'];				// Project name
							$prj_begin = $row_project['beginning'];			// The date of the project begining.
							$prj_end = $row_project['ending'];				// The date of the project ending.
							$prj_phase = $row_project['phase'];				// The phase for the continuous project.
							$prj_fim = $row_project['faceimage'];			// The phase for the continuous project.
							
							// Build HTML tag elements using aquired field values.
							echo "\t\t\t\t\t\t<tr>\n";
							if($prj_fim != ""){
								echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
								echo "\t\t\t\t\t\t\t\t<a href='project_consolidations_view.php?uuid=" .$prj_uuid. "'>\n";
								echo "\t\t\t\t\t\t\t\t\t<img height=96 src='avatar_project_face.php?uuid=" .$prj_uuid."' alt='img'/>\n";
								echo "\t\t\t\t\t\t\t\t</a>\n";
								echo "\t\t\t\t\t\t\t</td>\n";
							} else {
								echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
								echo "\t\t\t\t\t\t\t\t<a href='project_consolidations_view.php?uuid=" .$prj_uuid. "'>\n";
								echo "\t\t\t\t\t\t\t\t\t<img height=96 src='images/noimage.jpg' alt='img'/>\n";
								echo "\t\t\t\t\t\t\t\t</a>\n";
								echo "\t\t\t\t\t\t\t</td>\n";
							}
							echo "\t\t\t\t\t\t\t<td style='text-align: left; vertical-align: middle;'>".$prj_name."</td>\n";
							echo "\t\t\t\t\t\t\t<td style='width: 100px; vertical-align: middle;'>".$prj_begin."</td>\n";
							echo "\t\t\t\t\t\t\t<td style='width: 100px; vertical-align: middle;'>".$prj_end."</td>\n";
							echo "\t\t\t\t\t\t\t<td style='width: 100px; vertical-align: middle;'>第".$prj_phase."次調査</td>\n";
							
							echo "\t\t\t\t\t\t\t<td style='width: 200px; vertical-align: top;'>\n";
							echo "\t\t\t\t\t\t\t\t<div class='btn-group-vertical'>";
							
							// Create a button for operation.
							if ($_SESSION["USERTYPE"] == "Administrator") {
								// Create a button for moving to project information page.
								// This operation can be conducted only by Administrators.
								echo "\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-success'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
								echo "\t\t\t\t\t\t\t\t\t\tonclick=editProject('".$prj_uuid."');>\n";
								echo "\t\t\t\t\t\t\t\t\t<span>プロジェクトの編集</span>\n";
								echo "\t\t\t\t\t\t\t\t</button>\n";
							}
							// Create a button for moving to consolidation page.
							echo "\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=moveToConsolidation('".$prj_uuid."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span>資料の編集</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							
							// Create a button for moving to project report page.
							echo "\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=moveToReport('".$prj_uuid."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span>報告書の編集</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							
							echo "\t\t\t\t\t\t\t\t</div>";
							echo "</td>\n";
							echo "\t\t\t\t\t\t</tr>\n";
						}
						pg_close($conn);
					?>
				</table>
			</div>
		</div>
		<!-- Javascript -->
		<script type="text/javascript">
			function editMe(uuid) {
				//window.location.href = "consolidation.php?uuid=" + uuid;
				window.location.href = "edit_member.php?user=" + uuid;
			}
			// Move to other page to show the summary of the project.
			function moveToConsolidation(uuid) {
				//window.location.href = "consolidation.php?uuid=" + uuid;
				window.location.href = "consolidation.php?uuid=" + uuid;
				return false;
			}
			
			// Move to other page to show the summary of the project.
			function moveToReport(uuid) {
				window.location.href = "report.php?uuid=" + uuid;
				return false;
			}
			// Move to other page to show the summary of the project.
			function editProject(uuid) {
				window.location.href = "edit_project.php?uuid=" + uuid;
				return false;
			}
		</script>
    </body>
</html>
