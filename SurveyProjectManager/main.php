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
		exit;
	}
	
	// Load external libraries.
	require "lib/guid.php";
    require "lib/config.php";
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
    
    // Connect to DB.
    $username = $_SESSION["USERNAME"];
	
	// Connect to the DB.
	$conn = pg_connect(
				"host=".DBHOST.
				" port=".DBPORT.
				" dbname=".DBNAME.
				" user=".DBUSER.
				" password=".DBPASS);
	
	// Check the connection status.
	if(!$conn){
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
	// Get member information, who is currently logging in.
    $sql_sel_mem = "SELECT * FROM member WHERE username = '" . $username . "'";
    $sql_res_mem = pg_query($conn, $sql_sel_mem);
	if (!$sql_res_mem) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
    while ($row = pg_fetch_assoc($sql_res_mem)) {
        $mem_snm = $row['surname'];
        $mem_fnm = $row['firstname'];
		$mem_uty = $row['usertype'];
		$mem_id = $row['uuid'];
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
					WHERE R.mem_id = '".$mem_id."' ORDER by P.id";
	
	// Excute the query and get the result of query.
	$sql_res_prj = pg_query($conn, $sql_sel_prj);
	if (!$sql_res_prj) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_prj = pg_fetch_all($sql_res_prj);
	$row_cnt = 0 + intval(pg_num_rows($sql_res_prj));
	
	// Close the connection.
	pg_close($conn);
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
		<script src="lib/jquery-3.1.1/jquery.min.js"></script>
		
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
								<h2>ようこそ、<?php echo $mem_snm." ".$mem_fnm; ?>さん</h2>
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
											onclick="editMe('<?php echo $mem_id;?>');">
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
						foreach ($rows_prj as $row_prj){
							// Get a value in each field.
							$prj_id = $row_prj['uuid'];				// Internal identifier of the project
							$prj_name = $row_prj['name'];				// Project name
							$prj_begin = $row_prj['beginning'];			// The date of the project begining.
							$prj_end = $row_prj['ending'];				// The date of the project ending.
							$prj_phase = $row_prj['phase'];				// The phase for the continuous project.
							$prj_fim = $row_prj['faceimage'];			// The phase for the continuous project.
							
							// Build HTML tag elements using aquired field values.
							echo "\t\t\t\t\t\t<tr>\n";
							if($prj_fim != ""){
								echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
								echo "\t\t\t\t\t\t\t\t<a href='project_consolidations_view.php?uuid=" .$prj_id. "'>\n";
								echo "\t\t\t\t\t\t\t\t\t<img height=96 src='avatar_project_face.php?uuid=" .$prj_id."' alt='img'/>\n";
								echo "\t\t\t\t\t\t\t\t</a>\n";
								echo "\t\t\t\t\t\t\t</td>\n";
							} else {
								echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
								echo "\t\t\t\t\t\t\t\t<a href='project_consolidations_view.php?uuid=" .$prj_id. "'>\n";
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
								echo "\t\t\t\t\t\t\t\t\t\tonclick=editProject('".$prj_id."');>\n";
								echo "\t\t\t\t\t\t\t\t\t<span>プロジェクトの編集</span>\n";
								echo "\t\t\t\t\t\t\t\t</button>\n";
							}
							
							// Create a button for moving to consolidation page.
							echo "\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=moveToConsolidation('".$prj_id."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span>資料の編集</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							
							// Create a button for moving to project report page.
							echo "\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=moveToReport('".$prj_id."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span>報告書の編集</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							
							echo "\t\t\t\t\t\t\t\t</div>";
							echo "</td>\n";
							echo "\t\t\t\t\t\t</tr>\n";
						}
					?>
				</table>
			</div>
		</div>
		<!-- Javascript -->
		<script type="text/javascript">
			function editMe(mem_id) {
				var main_form = document.createElement("form");
				document.body.appendChild(main_form);
				
				var inp_mem_id = document.createElement("input");
				inp_mem_id.setAttribute("type", "hidden");
				inp_mem_id.setAttribute("id", "mem_id");
				inp_mem_id.setAttribute("name", "mem_id");
				inp_mem_id.setAttribute("value", mem_id);
				
				main_form.appendChild(inp_mem_id);
				
				main_form.setAttribute("action", "edit_member.php");
				main_form.setAttribute("method", "post");
				main_form.submit();
				
				return false;
			}
			// Move to other page to show the summary of the project.
			function moveToConsolidation(prj_id) {
				var main_form = document.createElement("form");
				document.body.appendChild(main_form);
				
				var inp_prj_id = document.createElement("input");
				inp_prj_id.setAttribute("type", "hidden");
				inp_prj_id.setAttribute("id", "prj_id");
				inp_prj_id.setAttribute("name", "prj_id");
				inp_prj_id.setAttribute("value", prj_id);
				
				main_form.appendChild(inp_prj_id);
				
				main_form.setAttribute("action", "consolidation.php");
				main_form.setAttribute("method", "post");
				main_form.submit();
				
				return false;
			}
			
			// Move to other page to show the summary of the project.
			function moveToReport(prj_id) {
				var main_form = document.createElement("form");
				document.body.appendChild(main_form);
				
				var inp_prj_id = document.createElement("input");
				inp_prj_id.setAttribute("type", "hidden");
				inp_prj_id.setAttribute("id", "prj_id");
				inp_prj_id.setAttribute("name", "prj_id");
				inp_prj_id.setAttribute("value", prj_id);
				
				main_form.appendChild(inp_prj_id);
				
				main_form.setAttribute("action", "report.php");
				main_form.setAttribute("method", "post");
				main_form.submit();
				
				return false;
			}
			
			// Move to other page to show the summary of the project.
			function editProject(prj_id) {
				var main_form = document.createElement("form");
				document.body.appendChild(main_form);
				
				var inp_prj_id = document.createElement("input");
				inp_prj_id.setAttribute("type", "hidden");
				inp_prj_id.setAttribute("id", "prj_id");
				inp_prj_id.setAttribute("name", "prj_id");
				inp_prj_id.setAttribute("value", prj_id);
				
				main_form.appendChild(inp_prj_id);
				
				main_form.setAttribute("action", "edit_project.php");
				main_form.setAttribute("method", "post");
				main_form.submit();
				
				return false;
			}
		</script>
    </body>
</html>
