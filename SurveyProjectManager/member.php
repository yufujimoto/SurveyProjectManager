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
	
	require "lib/guid.php";
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
					<tr style="background-color:#343399; color:#ffffff;"><td><h2>メンバー管理</h2></td></tr>
				</thead>
			</table></div>
			
			<!-- Main containts -->
			<div id="main" class="row">
				<!-- Operating menues -->
				<table id="operation" class="table" style="padding: 0px; margin: 0px">
					<thead style="text-align: center">
						<tr><td colspan=7 style="text-align: left">
							<button class="btn btn-sm btn-success" type="submit" value="add_member" onclick="addNewMember();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 新規ユーザーの追加</button>
							<button class="btn btn-sm btn-success" type="submit" value="view_selection" onclick="importMembersByCsv();"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> ユーザーのインポート</button>
							<button class="btn btn-sm btn-success" type="submit" value="view_selection" onclick="ExportMembersByCsv();"><span class="glyphicon glyphicon-download" aria-hidden="true"></span> ユーザーのエクスポート</button>
							<button id="del_row" class="btn btn-sm btn-danger" type="submit" value="delete" onclick="deleteASelectedMember();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> 選択したユーザーの削除</button>
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
				$sql_select_member = "SELECT * FROM member ORDER by id";
				
				// Excute the query and get the result of query.
				$result_select_member = pg_query($dbconn, $sql_select_member);
				if (!$result_select_member) {
					// Print the error messages and exit routine if error occors.
					echo "An error occurred in DB query.\n";
					exit;
				}
				
				// Fetch rows of projects. 
				$rows_member = pg_fetch_all($result_select_member);
				$row_count = 0 + intval(pg_num_rows($result_select_member));
				
				// Create section Label and show the total number of the registered project
				echo "<h3>" . $row_count ."件のメンバーが登録されています。</h3>\n";
			?>
			</div>
			
			<!-- Members list -->
			<div class="row"><table id='members' class='table table-striped' style='text-align:center; vertical-align:middle; padding:0px'>
				<thead style="text-align: center">
					<tr><td></td><td>Avatar</td><td style="width: 200px">氏名</td><td>ユーザー名</td><td>ユーザー種別</td></tr>
				</thead>
				<?php
					echo "<form id='selection'>\n";
					foreach ($rows_member as $row){
						$mem_uuid = $row['uuid'];
						$mem_ava = $row['avatar'];
						$mem_snm = $row['surname'];
						$mem_fnm = $row['firstname'];
						$mem_unm = $row['username'];
						$mem_utp = $row['usertype'];
						
						echo "\t\t\t\t\t<tr style='text-align: center;'><td style='vertical-align: middle;'><input type='radio' name='member' value='" .$mem_uuid. "' /></td>";
						if($mem_ava != ""){
							echo "<td style='vertical-align: middle;'><a href='project_members_view.php?mem_uuid=" .$mem_uuid. "'><img height=64 width=64 src='avatar_member_list.php?mem_uuid=" .$mem_uuid."' alt='img'/></a></td>";
						} else {
							echo "<td style='vertical-align: middle;'><a href='project_members_view.php?mem_uuid=" .$mem_uuid. "'><img height=64 width=64  src='images/avatar.jpg' alt='img'/></a></td>";
						}
						echo "<td style='vertical-align: middle;'>". $mem_snm. " " .$mem_fnm. "</td>";
						echo "<td style='vertical-align: middle;'>". $mem_unm. "</td>";
						echo "<td style='vertical-align: middle;'>". $mem_utp. "</td></tr>\n";
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
				window.location.href = "add_member.php";
				return false;
			}
			
			function importMembersByCsv() {
				window.location.href = "project_members_add_csv.php";
				
				return false;
			}
			
			function ExportMembersByCsv() {
				window.location.href = "project_members_export_csv.php";
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
								mem_uuid = selection[i].value;
								
								// Get the selected row and delete the row. 
								table.deleteRow(i+1);
								
								// Send the member id to the PHP script to drop selected project from DB.
								window.location.href = "delete_member.php?uuid=" + mem_uuid;
								
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
							mem_uuid = selection[i].value;
							
							// Send the project id to the PHP script to drop selected project from DB.
							return mem_uuid;
						}
					}
				} catch(e){ alert(e); }
			}
		</script>
    </body>
</html>