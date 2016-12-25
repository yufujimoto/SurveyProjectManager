<?php
	// Start the session.
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	// Load external libraries.
	require "lib/guid.php";
    require "lib/config.php";
	
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	$con_id= $_REQUEST["uuid"];
	$prj_id= $_REQUEST["prj_id"];
	
	// Connect to the DB.
	$conn = pg_connect(
					"host=".DBHOST.
					" port=".DBPORT.
					" dbname=".DBNAME.
					" user=".DBUSER.
					" password=".DBPASS)
			or die("Connection failed: " . pg_last_error());
	
	// Get the way of sorting.
	if(isset($_POST['srt_key']) ){
		$srt_way = explode(":", $_POST["srt_key"]);
		$srt_key = $srt_way[0];
		$srt_lab = $srt_way[1];
	} else {
		$srt_key = "id";
		$srt_lab = "登録順";
	}
	
	// Get a list of registered project.
	// Create a SQL query string.
	$sql_sel_mat = "SELECT * FROM material WHERE con_id = '".$con_id."' ORDER by ".$srt_key;
	
	// Excute the query and get the result of query.
	$sql_res_mat = pg_query($conn, $sql_sel_mat);
	if (!$sql_res_mat) {
		// Print the error messages and exit routine if error occors.
		echo "An error occurred in DB query.\n";
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_mat = pg_fetch_all($sql_res_mat);
	$row_cnt = 0 + intval(pg_num_rows($sql_res_mat));
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Material</title>
		
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
			<div id="main" class="row">
				<table id="operation" class="table" style="padding: 0px; margin: 0px">
					<thead style="text-align: center">
						<!-- Main Label of CSV uploader -->
						<tr>
							<td colspan="2">
								<h2>対象資料の管理</h2>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
									<button id="btn_add_mat"
											name="btn_add_mat"
											class="btn btn-sm btn-default"
											type="submit" value="add_material"
											onclick="backToConsolidation('<?php echo $prj_id;?>');">
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> 統合体に戻る</span>
									</button>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
								<div class="btn-group">
									<button id="btn_add_mat"
											name="btn_add_mat"
											class="btn btn-sm btn-success"
											type="submit" value="add_material"
											onclick="addNewMaterial('<?php echo $con_id;?>');">
										<span class="glyphicon glyphicon-plus" aria-hidden="true"> 対象資料の追加</span>
									</button>
									<button id="btn_imp_mat"
											name="btn_imp_mat"
											class="btn btn-sm btn-success"
											type="submit" 
											onclick="importMaterials('<?php echo $con_id;?>','<?php echo $prj_id;?>');">
										<span class="glyphicon glyphicon-upload" aria-hidden="true"> 対象資料のインポート</span>
									</button>
									<!--
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
							<td style="text-align: right">
								<!-- Sorting option.-->
								<form method="POST" class="form-inline">
									<div class="form-group">
										<label for="email">並び替え:</label>
										<select id="srt_key"
												name="srt_key"
												class="form-control"
												style="width: 200px"
												onchange="this.form.submit();">
											<option value="<?php echo $srt_key; ?>"><?php echo $srt_lab; ?></option>
											<option value="id:登録順">登録順</option>
											<option value="name:資料名順">資料名順</option>
											<option value="material_number:資料番号順">資料番号順</option>
										</select>
									</div>
									<input type="hidden" name="selected_text" id="selected_text" value="" />
								</form>
							</td>
						</tr>
						<!-- Display Errors -->
						<tr>
							<td colspan="2">
								<p style="color: red; text-align: left"><?php echo $err; ?></p>
							</td>
						</tr>
					</thead>
				</table>
			</div>
			
			<!-- Contents -->
			<div id="contents" class="row">
				<h3><?php echo $row_cnt?>件の資料が登録されています。</h3>
			</div>

			<!-- Members list -->
			<div class="row">
				<table id="material" class="table table-striped">
					<thead style="text-align: center">
						<tr>
							<td style="width: 100px"></td>
							<td style="width: 200px">名称</td>
							<td style="width: 150px">資料番号</td>
							<td style="width: 150px">開始時期</td>
							<td style="width: 150px">終了時期</td>
							<td style="width: auto">備考</td>
							<td style="width: 120px"></td>
						</tr>
					</thead>
					<?php
						foreach ($rows_mat as $row){
							$mat_uuid = $row["uuid"];
							$mat_nam = $row["name"];
							$mat_num = $row["material_number"];
							$mat_beg = $row["estimated_period_beginning"];
							$mat_end = $row["estimated_period_ending"];
							$mat_dsc = $row["descriptions"];
							
							$sql_sel_img = "SELECT uuid FROM digitized_image WHERE mat_id='" .$mat_uuid."'" ;
							$sql_res_img = pg_query($sql_sel_img);
							$img_cnt = 0 + intval(pg_num_rows($sql_res_img));
							
							echo "\t\t\t\t\t<tr style='text-align: center;'>\n";
							
							if($img_cnt > 0){
								echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
								echo "\t\t\t\t\t\t\t<a href='avatar_material.php?uuid=" .$mat_uuid. "&type=original'>\n";
								echo "\t\t\t\t\t\t\t\t<img height=96 src='avatar_material.php?uuid=" .$mat_uuid."' alt='img'/>\n";
								echo "\t\t\t\t\t\t\t</a>\n";
								echo "\t\t\t\t\t\t</td>\n";
							} else {
								echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
								echo "\t\t\t\t\t\t\t<a href='images/noimage.jpg'>\n";
								echo "\t\t\t\t\t\t\t\t<img height=96 src='images/noimage.jpg' alt='img'/>\n";
								echo "\t\t\t\t\t\t\t</a>\n";
								echo "\t\t\t\t\t\t</td>\n";
							}
							
							echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $mat_nam. "</td>\n";
							echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $mat_num. "</td>\n";
							echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $mat_beg. "</td>\n";
							echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $mat_end. "</td>\n";
							echo "\t\t\t\t\t\t<td style='text-align:left; vertical-align: middle;'>". $mat_dsc. "</td>\n";
							
							// Control buttons
							echo "\t\t\t\t\t\t<td style='vertical-align: top;'>\n";
							// Control menue
							echo "\t\t\t\t\t\t\t<div class='btn-group-vertical'>\n";
							// Create a button for operation.
							if ($_SESSION["USERTYPE"] == "Administrator") {
								// Create a button for deleting this material.
								// This operation can be conducted only by Administrators.
								echo "\t\t\t\t\t\t\t\t<button id='btn_del_mat'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_del_mat'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-danger'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
								echo "\t\t\t\t\t\t\t\t\t\tonclick=deleteMaterial('".$prj_id."','".$con_id."','".$mat_uuid."');>\n";
								echo "\t\t\t\t\t\t\t\t\t<span>資料の削除</span>\n";
								echo "\t\t\t\t\t\t\t\t</button>\n";
							}
							// Create a button for moving to consolidation page.
							echo "\t\t\t\t\t\t\t\t<button id='btn_edt_mat'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_edt_mat'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=editMaterial('".$prj_id."','".$con_id."','".$mat_uuid."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span>資料情報の編集</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							echo "\t\t\t\t\t\t\t</div>\n";
							echo "\t\t\t\t\t\t</td>";
							echo "</tr>\n";
						}
						
						// Close the connection to the database.
						pg_close($conn);
					;?>
			</table></div>
		</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function backToConsolidation(uuid) {
				window.location.href = "consolidation.php?uuid=" + uuid;
				return false;
			}
			
			function addNewMaterial(uuid) {
				window.location.href = "add_material.php?uuid=" + uuid;
				return false;
			}
			
			function importMaterials(uuid, prj_id) {
				window.location.href = "import_materials.php?uuid=" + uuid + "&prj_id=" + prj_id;
				return false;
			}
			
			function deleteMaterial(prj_id, con_id, mat_id) {
				var diag_del_mat = confirm("この資料を削除しますか？");
				if (diag_del_mat == true) {
					// Send the member id to the PHP script to drop selected project from DB.
					window.location.href = "delete_material.php?uuid=" + mat_id + "&con_id=" + con_id + "&prj_id=" + prj_id;
				}
			}
			
			function editMaterial(prj_id, con_id, mat_id) {
				window.location.href = "update_material.php?uuid=" + mat_id + "&con_id=" + con_id + "&prj_id=" + prj_id;
			}
		</script>
    </body>
</html>