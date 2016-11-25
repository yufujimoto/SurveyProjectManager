<?php
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	if ($_SESSION["USERTYPE"] != "Administrator") {
		header("Location: main.php");
	}
	
	require 'lib/guid.php';
    require "lib/config.php";
	
	header("Content-Type: text/html; charset=UTF-8");
	
	$err = $_GET['err'];
?>
<!DOCTYPE html>
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
	    <title>プロジェクトの管理</title>
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
		
		<!-- Main containts -->
		<div class="container" style="padding-top: 30px">
			<!-- Page Header -->
			<div class="row"><table class='table'>
				<thead style="text-align: center">
					<!-- Main Label of CSV uploader -->
					<tr style="background-color:#343399; color:#ffffff;"><td><h2>プロジェクトの管理</h2></td></tr>
				</thead>
			</table></div>
			
			<!-- Main containts -->
			<div id="main" class="row">
				<!-- Operating menues -->
				<table id="operation" class="table" style="padding: 0px; margin: 0px">
					<thead style="text-align: center">
						<tr><td colspan=7 style="text-align: left">
							<button class="btn btn-sm btn-success" type="submit" value="add_new" onclick="addNewProject();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>　プロジェクトの追加</button>
							<button class="btn btn-sm btn-success" type="submit" value="view_selection" onclick="importProjectByCsv();"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> プロジェクトの一括インポート</button>
							<button class="btn btn-sm btn-success" type="submit" value="view_selection" onclick="ExportProjectByCsv();"><span class="glyphicon glyphicon-download" aria-hidden="true"></span> プロジェクトのエクスポート</button>
							<button id="del_row" class="btn btn-sm btn-danger" type="submit" value="delete" onclick="deleteASelectedProject();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 選択されたプロジェクトの削除</button>
						</td></tr>
					</thead>
					<tr><td><p class="blink" style="color: red; text-align: justify; font-size: small;"><?php print($err); ?></p></td></tr>
				</table>
			</div>
			
			<div id="contents" class="row">
			<?php
				// Connect to the DB.
				$dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
				
				// Get a list of registered project.
				// Create a SQL query string.
				$sql_select_projects = "SELECT * FROM project ORDER by id";
				
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
				echo "<h3>" . $row_count ."件のプロジェクトが登録されています。</h3>\n";
				echo "</div>\n";
				
				// A table for showing project list.
				
				echo "\t\t\t\t<div class='row'><table id='project' class='table table-striped' style='text-align:center; vertical-align:middle; padding:0px'>\n";
				echo "\t\t\t\t\t<form id='selection'>\n";
				echo "\t\t\t\t\t\t<thead><tr>\n";
				echo "\t\t\t\t\t\t\t<td style='width: 20px'></td>\n";
				echo "\t\t\t\t\t\t\t<td style='width: auto'>プロジェクト名</td>\n";
				echo "\t\t\t\t\t\t\t<td style='width: 100px'>開始</td>\n";
				echo "\t\t\t\t\t\t\t<td style='width: 100px'>終了</td>\n";
				echo "\t\t\t\t\t\t\t<td style='width: 100px'>次数</td>\n";
				echo "\t\t\t\t\t\t\t<td style='width: 200px'>登録日時</td>\n";
				echo "\t\t\t\t\t\t\t<td style='width: 150px'>登録者</td>\n";
				echo "\t\t\t\t\t\t</tr></thead>\n";
				
				// For each row, HTML list is created and showed on browser.
				foreach ($rows_project as $row_project){
					// Get a value in each field.
					$prj_uuid = $row_project['uuid'];		// Internal identifier of the project
					$prj_name = $row_project['name'];	// Project name
					$prj_begin = $row_project['beginning'];	// The date of the project begining.
					$prj_end = $row_project['ending'];		// The date of the project ending.
					$prj_phase = $row_project['phase'];			// The phase for the continuous project.
					$prj_date = $row_project['created'];			// Description about the project.
					$prj_by = $row_project['created_by'];			// Description about the project.

					
					// Build HTML tag elements using aquired field values.
					echo "\t\t\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t\t\t<td style='width: 20px; text-align: center; vertical-align: middle'><input type='radio' name='project' value='" . $prj_uuid . "' /></td>\n\t\t\t\t\t\t\t";
					echo '<td style="text-align: left"><a style="cursor: pointer;" onclick=moveToProjectView("'.$prj_uuid.'");> '.$prj_name .'</td>';
					echo "\n\t\t\t\t\t\t\t<td style='width: 100px;'>" . $prj_begin ."</td>\n";
					echo "\t\t\t\t\t\t\t<td style='width: 100px;'>" . $prj_end ."</td>\n";
					echo "\t\t\t\t\t\t\t<td style='width: 100px;'>第" . $prj_phase ."次調査</td>\n";
					echo "\t\t\t\t\t\t\t<td style='width: 200px;'>" . $prj_date ."</td>\n";
					echo "\t\t\t\t\t\t\t<td style='width: 150px;'>" . $prj_by ."</td>\n";
					echo "\t\t\t\t\t\t</tr>\n";
				}
				echo "\t\t\t\t\t</form>\n";
				echo "\t\t\t\t</table>\n";
				
				pg_close($dbconn);
			?>
			<div id="contents" class="row">
		</div>
		
		<!-- Javascript -->
		<script type="text/javascript">
			// Moove to other page to show the summary of the project.
			function moveToProjectView(uuid) {
				window.location.href = "edit_project.php?uuid=" + uuid;
				return false;
			}
			
			// Move to the other page for registering a new project.
			function addNewProject() {
				window.location.href = "add_project.php";
				return false;
			}
			
			// Move to other page for importing one or more projects by using CSV file.
			function importProjectByCsv() {
				window.location.href = "add_project_csv.php";
				return false;
			}
			
			// Download the list of registered projects.
			function ExportProjectByCsv() {
				window.location.href = "export_project_csv.php";
				return false;
			}
			
			// Delete checked project from both the list and Database.
			function deleteASelectedProject() {
				var prj_uuid = "";
				var table=document.getElementById("project");
				var selection = document.getElementById('selection');
				
				try{
					for (var i = 0, length = selection.length; i < length; i++) {
						var checked = selection[i].checked;
						
						if (checked) {
							// Get a project id of the selected item.
							prj_uuid = selection[i].value;
							// Get the selected row and delete the row. 
							table.deleteRow(i+1);
							
							// Send the project id to the PHP script to drop selected project from DB. 
							window.location.href = "delete_project.php?uuid=" + prj_uuid;
							
							// only one radio can be logically checked, don't check the rest
							break;
						}
					}
				} catch(e){ alert(e); }
			}
		</script>
	</body>
</html>