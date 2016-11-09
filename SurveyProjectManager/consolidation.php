<?php
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	require "lib/guid.php";
    require "lib/config.php";
	
	header("Content-Type: text/html; charset=UTF-8");
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
		<div id="container" class="container" style="padding-top: 30px">
			<!-- Page Header -->
			<div class="row"><table class='table'>
				<thead style="text-align: center">
					<!-- Main Label of CSV uploader -->
					<tr style="background-color:#343399; color:#ffffff;"><td colspan=2><h2>メンバー管理</h2></td></tr>
					<!-- Operating menues -->
					<tr><td colspan=7 style="text-align: left">
						<button class="btn btn-sm btn-success" type="submit" value="add_consolidation" onclick="addNewMember();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 新規ユーザーの追加</button>
						<button class="btn btn-sm btn-success" type="submit" value="view_selection" onclick="importMembersByCsv();"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> ユーザーのインポート</button>
						<button class="btn btn-sm btn-success" type="submit" value="view_selection" onclick="ExportMembersByCsv();"><span class="glyphicon glyphicon-download" aria-hidden="true"></span> ユーザーのエクスポート</button>
						<button id="del_row" class="btn btn-sm btn-danger" type="submit" value="delete" onclick="deleteASelectedMember();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> 選択したユーザーの削除</button>
					</td></tr>
				</thead>
			</table></div>
			
			<?php
				// Connect to the DB.
				$dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
				
				// Get a list of registered project.
				// Create a SQL query string.
				$sql_select_consolidation = "SELECT * FROM member ORDER by id";
				
				// Excute the query and get the result of query.
				$result_select_consolidation = pg_query($dbconn, $sql_select_consolidation);
				if (!$result_select_consolidation) {
					// Print the error messages and exit routine if error occors.
					echo "An error occurred in DB query.\n";
					exit;
				}
				
				// Fetch rows of projects. 
				$rows_consolidation = pg_fetch_all($result_select_consolidation);
				$row_count = 0 + intval(pg_num_rows($result_select_consolidation));
			?>
			<!-- Members list -->
			<div class="row"><table id="members" class='table table-striped'>
				<thead style="text-align: center">
					<tr><td></td><td>Avatar</td><td style="width: 200px">名称</td><td>場所</td><td>開始時期</td><td>終了時期</td></tr>
				</thead>
				<?php
					echo "<form id='selection'>\n";
					foreach ($rows_consolidation as $row){
						$con_uuid = $row['uuid'];
						$con_nam = $row['name'];
						$con_fim = $row['faceimage'];
						$con_gnm = $row['geographic_name'];
						$con_beg = $row['estimated_period_beginning'];
						$con_end = $row['estimated_period_ending'];
						$con_dsc = $row['descriptions'];
						
						echo "\t\t\t\t\t<tr style='text-align: center;'><td style='vertical-align: middle;'><input type='radio' name='member' value='" .$con_uuid. "' /></td>";
						if($con_ava != ""){
							echo "<td style='vertical-align: middle;'><a href='project_consolidations_view.php?con_uuid=" .$con_uuid. "'><img height=64 width=64 src='avatar_consolidation_list.php?con_uuid=" .$con_uuid."' alt='img'/></a></td>";
						} else {
							echo "<td style='vertical-align: middle;'><a href='project_consolidations_view.php?con_uuid=" .$con_uuid. "'><img height=64 width=64  src='images/noimage.jpg' alt='img'/></a></td>";
						}
						echo "<td style='vertical-align: middle;'>". $con_nam. "</td>";
						echo "<td style='vertical-align: middle;'>". $con_gnm. "</td>";
						echo "<td style='vertical-align: middle;'>". $con_beg. "</td>";
						echo "<td style='vertical-align: middle;'>". $con_end. "</td>";
						echo "<td style='vertical-align: middle;'>". $con_dsc. "</td></tr>\n";
					}
					echo "\t\t\t\t</form>\n";
					
					// Close the connection to the database.
					pg_close($dbconn);
				;?>
			</table></div>
		</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function addNewMember() {
				window.location.href = "add_consolidation.php";
				return false;
			}
			
			function importMembersByCsv() {
				window.location.href = "project_consolidations_add_csv.php";
				
				return false;
			}
			
			function ExportMembersByCsv() {
				window.location.href = "project_consolidations_export_csv.php";
				return false;
			}
			
			function deleteASelectedMember() {
				var table=document.getElementById("members");
				var selection = document.getElementById('selection');
				var rowCount=table.rows.length;
				
				if (rowCount == 2) {
					alert("Cannot delete this member!! Number of member in a project should be more than one member!!");
				} else {
					try{
						for (var i = 0, length = selection.length; i < length; i++) {
							var checked = selection[i].checked;
							
							if (checked) {
								// Get a project id of the selected item.
								con_uuid = selection[i].value;
								
								// Get the selected row and delete the row. 
								table.deleteRow(i+1);
								
								// Send the member id to the PHP script to drop selected project from DB.
								window.location.href = "delete_consolidation.php?uuid=" + con_uuid;
								
								// only one radio can be logically checked, don't check the rest
								break;
							}
						}
					} catch(e){ alert(e); }
				}
			}
			
			function handleSelectedMember() {
				var selection = document.getElementById('selection');
				
				try{
					for (var i = 0, length = selection.length; i < length; i++) {
						var checked = selection[i].checked;
						
						if (checked) {
							// Get a project id of the selected item.
							con_uuid = selection[i].value;
							
							// Send the project id to the PHP script to drop selected project from DB.
							return con_uuid;
						}
					}
				} catch(e){ alert(e); }
			}
		</script>
    </body>
</html>