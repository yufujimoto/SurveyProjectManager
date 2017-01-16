<?php
	// Start the session.
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	// Only the administrator can access to this page.
	if ($_SESSION["USERTYPE"] != "Administrator") {
		header("Location: main.php");
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
	
	// Get a list of registered project.
	// Create a SQL query string.
	$sql_sel_prj = "SELECT * FROM project ORDER by id";
	
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
		<title>Project</title>
		
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
		
		<!-- Main containts -->
		<div class="container" style="padding-top: 30px">
			<!-- Control Menu -->
			<div id="main" class="row">
				<!-- Operating menues -->
				<table id="operation" class="table" style="padding: 0px; margin: 0px">
					<thead style="text-align: center">
						<tr>
							<td>
								<h2>プロジェクト登録フォーム</h2>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
								<div class="btn-group">
									<button id="btn_add_prj"
											name="btn_add_prj"
											class="btn btn-sm btn-success"
											type="submit"
											onclick="addNewProject();">
										<span class="glyphicon glyphicon-plus" aria-hidden="true"> プロジェクトの追加</span>
									</button>
									<!-- 
									<button id="btn_imp_prj"
											name="btn_imp_prj"
											class="btn btn-sm btn-success"
											type="submit"
											onclick="importProjectByCsv();">
										<span class="glyphicon glyphicon-upload" aria-hidden="true">プロジェクトの一括インポート</span>
									</button>
									<button　id="btn_exp_prj"
											name="btn_exp_prj"
											class="btn btn-sm btn-success"
											type="submit" value="view_selection"
											onclick="ExportProjectByCsv();">
										<span class="glyphicon glyphicon-download" aria-hidden="true">プロジェクトのエクスポート</span>
									</button>
									-->
									<button id="btn_del_prj"
											name="btn_del_prj"
											class="btn btn-sm btn-danger"
											type="submit"
											value="delete"
											onclick="deleteSelectedProject();">
										<span class="glyphicon glyphicon-plus" aria-hidden="true"> 選択されたプロジェクトの削除</span>
									</button>
								</div>
							</td>
						</tr>
						<!-- Display Errors -->
						<tr>
							<td>
								<p style="color: red; text-align: left"><?php echo $err; ?></p>
							</td>
						</tr>
					</thead>
				</table>
			</div>
			
			<!-- Contents -->
			<div id="contents" class="row">
				<h3><?php echo $row_cnt?>件のプロジェクトが登録されています。</h3>
			</div>
			<div class="row">
				<table id="project" class="table table-striped" style="text-align:center; vertical-align:middle; padding:0px">
					<form id="selection">
						<thead>
							<tr>
								<td style="width: 20px"></td>
								<td style="width: auto">プロジェクト名</td>
								<td style="width: 100px">開始</td>
								<td style="width: 100px">終了</td>
								<td style="width: 100px">次数</td>
								<td style="width: 200px">登録日時</td>
								<td style="width: 150px">登録者</td>
							</tr>
						</thead>
						<?php
							// For each row, HTML list is created and showed on browser.
							foreach ($rows_prj as $row_project){
								// Get a value in each field.
								$prj_uuid = $row_project["uuid"];		// Internal identifier of the project
								$prj_name = $row_project["name"];		// Project name
								$prj_begin = $row_project["beginning"];	// The date of the project begining.
								$prj_end = $row_project["ending"];		// The date of the project ending.
								$prj_phase = $row_project["phase"];		// The phase for the continuous project.
								$prj_date = $row_project["created"];	// Description about the project.
								$prj_by = $row_project["created_by"];	// Description about the project.
								
								// Make HTML tag elements using aquired field values.
								echo "\t\t\t\t\t\t<tr>\n";
								echo "\t\t\t\t\t\t\t<td style='width: 20px; text-align: center; vertical-align: middle'>";
								echo "\t\t\t\t\t\t\t\t<input type='radio' name='project' value='" . $prj_uuid . "' />";
								echo "\t\t\t\t\t\t\t</td>";
								echo "\n\t\t\t\t\t\t\t<td style='text-align: left'>";
								echo "\n\t\t\t\t\t\t\t\t".'<a style="cursor: pointer;" onclick=editProject("'.$prj_uuid.'");> '.$prj_name .'</td>';
								echo "\n\t\t\t\t\t\t\t<td style='width: 100px;'>" . $prj_begin ."</td>";
								echo "\n\t\t\t\t\t\t\t<td style='width: 100px;'>" . $prj_end ."</td>";
								echo "\n\t\t\t\t\t\t\t<td style='width: 100px;'>第" . $prj_phase ."次調査</td>";
								echo "\n\t\t\t\t\t\t\t<td style='width: 200px;'>" . $prj_date ."</td>";
								echo "\n\t\t\t\t\t\t\t<td style='width: 150px;'>" . $prj_by ."</td>";
								echo "\n\t\t\t\t\t\t</tr>\n";
							}
						?>
					</form>
				</table>
			<div id="contents" class="row">
		</div>
		
		<!-- Javascript -->
		<script type="text/javascript">
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
			
			// Move to the other page for registering a new project.
			function addNewProject() {
				window.location.href = "add_project.php";
				return false;
			}
			
			// Delete checked project from both the list and Database.
			function deleteSelectedProject() {
				var prj_id = "";
				var table=document.getElementById("project");
				var selection = document.getElementById("selection");
				
				try{
					for (var i = 0, length = selection.length; i < length; i++) {
						var checked = selection[i].checked;
						
						if (checked) {
							// Get a project id of the selected item.
							prj_id = selection[i].value;
							// Get the selected row and delete the row. 
							table.deleteRow(i+1);
							
							// Send the project id to the PHP script to drop selected project from DB.
							var main_form = document.createElement("form");
							document.body.appendChild(main_form);
							
							var inp_prj_id = document.createElement("input");
							inp_prj_id.setAttribute("type", "hidden");
							inp_prj_id.setAttribute("id", "prj_id");
							inp_prj_id.setAttribute("name", "prj_id");
							inp_prj_id.setAttribute("value", prj_id);
							
							main_form.appendChild(inp_prj_id);
							
							main_form.setAttribute("action", "delete_project.php");
							main_form.setAttribute("method", "post");
							main_form.submit();
							
							// only one radio can be logically checked, don"t check the rest
							break;
						}
					}
				} catch(e){ alert(e); }
			}
		</script>
	</body>
</html>