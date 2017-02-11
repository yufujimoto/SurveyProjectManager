<?php
	header("Content-Type: text/html; charset=UTF-8");
	
	// Start the session.
	session_cache_limiter("private_no_expire");
	session_start();
	
	// Check session status.
	if (!isset($_SESSION["USERNAME"])) {
		header("Location: logout.php");
		exit;
	}
	
	// Load external libraries.
	require "lib/guid.php";
    require "lib/config.php";
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	$prj_id= $_REQUEST['prj_id'];
	
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
	
	// Create a SQL query string.
	$sql_sel_con = "SELECT * FROM consolidation WHERE prj_id = '".$prj_id."' ORDER by id";
	
	// Excute the query and get the result of query.
	$sql_res_con = pg_query($conn, $sql_sel_con);
	if (!$sql_res_con) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_con = pg_fetch_all($sql_res_con);
	$row_cnt = 0 + intval(pg_num_rows($sql_res_con));
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Consolidation</title>
		
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
		
		<!-- Control Menu -->
		<div class="container" style="padding-top: 30px">
			<div id="main" class="row">
				<table id="operation" class="table" style="padding: 0px; margin: 0px">
					<thead style="text-align: center">
						<!-- Main Label of CSV uploader -->
						<tr>
							<td>
								<h2>統合体の管理</h2>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
									<button id="btn_add_mat"
											name="btn_add_mat"
											class="btn btn-sm btn-default"
											type="submit" value="add_material"
											onclick="backToMyPage()">
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> マイページに戻る</span>
									</button>
							</td>
						</tr>
						<tr>
							<td colspan=7 style="text-align: left">
								<div class="btn-group">
									<button id="btn_add_mat"
											name="btn_add_mat"
											class="btn btn-sm btn-success"
											type="submit" value="add_material"
											onclick="addNewConsolidation('<?php echo $prj_id; ?>');">
										<span class="glyphicon glyphicon-plus" aria-hidden="true"> 新規統合体の追加</span>
									</button>
									<!--
									<button id="btn_imp_mat"
											name="btn_imp_mat"
											class="btn btn-sm btn-success"
											type="submit" 
											onclick="importMaterials();">
										<span class="glyphicon glyphicon-upload" aria-hidden="true"> 対象資料のインポート</span>
									</button>
									<button id="btn_exp_mat"
											name="btn_exp_mat"
											class="btn btn-sm btn-success"
											type="submit" 
											onclick="ExportConsolidationByCsv();">
										<span class="glyphicon glyphicon-download" aria-hidden="true"> 対象資料のエクスポート</span>
									</button>
									-->
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
				<h3><?php echo $row_cnt?>件の統合体が登録されています。</h3>
			</div>
			
			<!-- Consolidation list -->
			<div class="row">
				<table id="consolidation" class="table table-striped">
					<?php
						// For each row, HTML list is created and showed on browser.
						foreach ($rows_con as $row){
							// Get a value in each field.
							$con_uuid = $row['uuid'];
							$con_nam = $row['name'];
							$con_fim = $row['faceimage'];
							$con_gnm = $row['geographic_name'];
							$con_beg = $row['estimated_period_beginning'];
							$con_end = $row['estimated_period_ending'];
							$con_dsc = $row['descriptions'];
							
							// Make HTML tag elements using aquired field values.
							
							// -------------------------------
							// Header row
							echo "\t\t\t\t\t<tr style='text-align: left;'>\n";
							echo "\t\t\t\t\t\t<td style='vertical-align: middle;' colspan='2'><h3>". $con_nam. "</h3></td>";
							echo "\t\t\t\t\t\t<td style='vertical-align: middle; text-align:right'>";
							
							// Control menue
							echo "\t\t\t\t\t\t\t\t<div class='btn-group-vertical'>";
							// Create a button for operation.
							if ($_SESSION["USERTYPE"] == "Administrator") {
								// Create a button for deleting this consolidation.
								// This operation can be conducted only by Administrators.
								echo "\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-danger'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
								echo "\t\t\t\t\t\t\t\t\t\tonclick=deleteConsolidation('".$prj_id."','".$con_uuid."');>\n";
								echo "\t\t\t\t\t\t\t\t\t<span>統合体の削除</span>\n";
								echo "\t\t\t\t\t\t\t\t</button>\n";
							}
							// Create a button for moving to consolidation page.
							echo "\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=editConsolidation('".$prj_id."','".$con_uuid."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span>統合体の編集</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							
							// Create a button for moving to material list page.
							echo "\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=moveToMaterials('".$prj_id."','".$con_uuid."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span>資料細目の編集</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							echo "\t\t\t\t\t\t\t\t</div>";
							
							// -------------------------------
							// Contents row
							echo "\t\t\t\t\t<tr style='text-align: center;'>\n";
							// Create a thumbnail image container.
							echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
							if($con_fim != ""){
								echo "\t\t\t\t\t\t\t<img height=200 src='avatar_consolidation_face.php?uuid=" .$con_uuid."' alt='img'/>\n";
							} else {
								echo "\t\t\t\t\t\t\t<img height=200 src='images/noimage.jpg' alt='img'/>\n";
							}
							echo "\t\t\t\t\t\t</td>\n";
							
							// Create a informaion box.
							echo "\t\t\t\t\t\t<td style='vertical-align: middle; text-align: left'>\n";
							echo "\t\t\t\t\t\t\t<ul>";
							echo "\t\t\t\t\t\t\t\t<li>所　在：". $con_gnm. "</li>\n";
							echo "\t\t\t\t\t\t\t\t<li>時　代：". $con_beg. "〜".$con_end."</li\n>";
							echo "\t\t\t\t\t\t\t\t<li>概　要：<p>". $con_dsc. "</p></li\n>";
							echo "\t\t\t\t\t\t\t</ul>";
							echo "\t\t\t\t\t\t</td><td></td>\n";
						}
						// Close the connection to the database.
						pg_close($conn);
					;?>
				</table>
			</div>
		</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function backToMyPage() {
				window.location.href = "main.php";
				return false;
			}
			
			function addNewConsolidation(prj_id) {
				var con_form = document.createElement("form");
				document.body.appendChild(con_form);
				
				var inp_prj_id = document.createElement("input");
				inp_prj_id.setAttribute("type", "hidden");
				inp_prj_id.setAttribute("id", "prj_id");
				inp_prj_id.setAttribute("name", "prj_id");
				inp_prj_id.setAttribute("value", prj_id);
				
				con_form.appendChild(inp_prj_id);
				
				con_form.setAttribute("action", "add_consolidation.php");
				con_form.setAttribute("method", "post");
				con_form.submit();
				
				return false;
			}
			
			function editConsolidation(prj_id, con_id){
				var con_form = document.createElement("form");
				document.body.appendChild(con_form);
				
				var inp_prj_id = document.createElement("input");
				inp_prj_id.setAttribute("type", "hidden");
				inp_prj_id.setAttribute("id", "prj_id");
				inp_prj_id.setAttribute("name", "prj_id");
				inp_prj_id.setAttribute("value", prj_id);
				
				var inp_con_id = document.createElement("input");
				inp_con_id.setAttribute("type", "hidden");
				inp_con_id.setAttribute("id", "con_id");
				inp_con_id.setAttribute("name", "con_id");
				inp_con_id.setAttribute("value", con_id);
				
				con_form.appendChild(inp_prj_id);
				con_form.appendChild(inp_con_id);
				
				con_form.setAttribute("action", "edit_consolidation.php");
				con_form.setAttribute("method", "post");
				con_form.submit();
				
				return false;
			}
			
			function moveToMaterials(prj_id, con_id){
				var con_form = document.createElement("form");
				document.body.appendChild(con_form);
				
				var inp_prj_id = document.createElement("input");
				inp_prj_id.setAttribute("type", "hidden");
				inp_prj_id.setAttribute("id", "prj_id");
				inp_prj_id.setAttribute("name", "prj_id");
				inp_prj_id.setAttribute("value", prj_id);
				
				var inp_con_id = document.createElement("input");
				inp_con_id.setAttribute("type", "hidden");
				inp_con_id.setAttribute("id", "con_id");
				inp_con_id.setAttribute("name", "con_id");
				inp_con_id.setAttribute("value", con_id);
				
				con_form.appendChild(inp_prj_id);
				con_form.appendChild(inp_con_id);
				
				con_form.setAttribute("action", "material.php");
				con_form.setAttribute("method", "post");
				con_form.submit();
				
				return false;
			}
			
			function deleteConsolidation(prj_id, con_id) {
				var diag_del_con = confirm("この統合体を削除しますか？");
				if (diag_del_con === true) {
					var con_form = document.createElement("form");
					document.body.appendChild(con_form);
					
					var inp_prj_id = document.createElement("input");
					inp_prj_id.setAttribute("type", "hidden");
					inp_prj_id.setAttribute("id", "prj_id");
					inp_prj_id.setAttribute("name", "prj_id");
					inp_prj_id.setAttribute("value", prj_id);
					
					var inp_con_id = document.createElement("input");
					inp_con_id.setAttribute("type", "hidden");
					inp_con_id.setAttribute("id", "con_id");
					inp_con_id.setAttribute("name", "con_id");
					inp_con_id.setAttribute("value", con_id);
					
					con_form.appendChild(inp_prj_id);
					con_form.appendChild(inp_con_id);
					
					con_form.setAttribute("action", "delete_consolidation.php");
					con_form.setAttribute("method", "post");
					con_form.submit();
					
					return false;
				}
			}
		</script>
    </body>
</html>