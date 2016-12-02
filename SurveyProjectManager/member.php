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
	
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	
	// Connect to the DB.
	$conn = pg_connect("host=".DBHOST.
					   " port=".DBPORT.
					   " dbname=".DBNAME.
					   " user=".DBUSER.
					   " password=".DBPASS)
		or die('Connection failed: ' . pg_last_error());
	
	// Get a list of registered project.
	// Create a SQL query string.
	$sql_select_mem = "SELECT * FROM member ORDER by id";
	
	// Excute the query and get the result of query.
	$sql_result_mem = pg_query($conn, $sql_select_mem);
	if (!$sql_result_mem) {
		// Print the error messages and exit routine if error occors.
		echo "An error occurred in DB query.\n";
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_mem = pg_fetch_all($sql_result_mem);
	$row_cnt = 0 + intval(pg_num_rows($sql_result_mem));
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Member</title>
		
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
		
		<!-- Control Menu -->
		<div class="container" style="padding-top: 30px">
			<!-- Main containts -->
			<div id="main" class="row">
				<!-- Operating menues -->
				<table id="operation" class="table" style="padding: 0px; margin: 0px">
					<thead style="text-align: center">
						<tr>
							<td>
								<h2>メンバー管理</h2>
							</td>
						</tr>
						<tr>
							<td colspan=7 style="text-align: left">
								<div class="btn-group">
									<button id="btn_add_mem" 
											name="btn_add_mem" 
											class="btn btn-sm btn-success"
											type="submit" value="add_member"
											onclick="addNewMember();">
										<span class="glyphicon glyphicon-plus" aria-hidden="true"> 新規ユーザーの追加</span>
									</button>
									<!--
									<button id="btn_imp_mem"
											name="btn_imp_mem"
											class="btn btn-sm btn-success"
											type="submit"
											onclick="importMembersByCsv();">
										<span class="glyphicon glyphicon-upload" aria-hidden="true"> ユーザーのインポート</span>
									</button>
									<button id="btn_exp_mem"
											name="btn_exp_mem"
											class="btn btn-sm btn-success"
											type="submit" value="view_selection"
											onclick="ExportMembersByCsv();">
										<span class="glyphicon glyphicon-download" aria-hidden="true"> ユーザーのエクスポート</span>
									</button>
									-->
									<button id="del_row"
											name="del_row"
											class="btn btn-sm btn-danger"
											type="submit" value="delete"
											onclick="deleteSelectedMember();">
										<span class="glyphicon glyphicon-remove" aria-hidden="true"> 選択したユーザーの削除</span>
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
				<h3><?php echo $row_cnt?>件のメンバーが登録されています。</h3>
			</div>
			
			<!-- Members list -->
			<div class="row">
				<table id='members' class='table table-striped' style='text-align:center; vertical-align:middle; padding:0px'>
					<thead style="text-align: center">
						<tr>
							<td></td>
							<td>Avatar</td>
							<td style="width: 200px">氏名</td>
							<td>ユーザー名</td>
							<td>ユーザー種別</td>
						</tr>
					</thead>
					<form id="selection">
					<?php
						foreach ($rows_mem as $row){
							$mem_uuid = $row['uuid'];
							$mem_ava = $row['avatar'];
							$mem_snm = $row['surname'];
							$mem_fnm = $row['firstname'];
							$mem_unm = $row['username'];
							$mem_utp = $row['usertype'];
							
							echo "\t\t\t\t\t<tr style='text-align: center;'>\n";
							echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
							echo "\t\t\t\t\t\t\t<input type='radio' name='member' value='" .$mem_uuid. "' />\n";
							echo "\t\t\t\t\t\t</td>";
							if($mem_ava != ""){
								echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
								echo "\t\t\t\t\t\t\t<a href='project_members_view.php?mem_uuid=" .$mem_uuid. "'>\n";
								echo "\t\t\t\t\t\t\t\t<img height=64 width=64 src='avatar_member_list.php?mem_uuid=" .$mem_uuid."' alt='img'/>\n";
								echo "\t\t\t\t\t\t\t\t\t</a>\n";
								echo "\t\t\t\t\t\t</td>\n";
							} else {
								echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
								echo "\t\t\t\t\t\t\t<<a href='project_members_view.php?mem_uuid=" .$mem_uuid. "'>\n";
								echo "\t\t\t\t\t\t\t\t<img height=64 width=64  src='images/avatar.jpg' alt='img'/>\n";
								echo "\t\t\t\t\t\t\t\t\t</a>\n";
								echo "\t\t\t\t\t\t</td>\n";
							}
							echo "<td style='vertical-align: middle;'>". $mem_snm. " " .$mem_fnm. "</td>";
							echo "<td style='vertical-align: middle;'>". $mem_unm. "</td>";
							echo "<td style='vertical-align: middle;'>". $mem_utp. "</td></tr>\n";
						}
						
						// Close the connection to the database.
						pg_close($conn);
					;?>
					</form>
				</table>
			</div>
		</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function addNewMember() {
				window.location.href = "add_member.php";
				return false;
			}
			
			function deleteSelectedMember() {
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
								uuid = selection[i].value;
								
								// Get the selected row and delete the row. 
								table.deleteRow(i+1);
								
								// Send the member id to the PHP script to drop selected project from DB.
								window.location.href = "delete_member.php?uuid=" + uuid;
								
								// only one radio can be logically checked, don't check the rest
								break;
							}
						}
					} catch(e){ alert(e); }
				}
			}
		</script>
    </body>
</html>