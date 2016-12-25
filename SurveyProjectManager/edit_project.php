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
	$err = $_REQUEST['err'];
	$uuid = $_REQUEST['uuid'];
	
	// Connect to the DB.
	$conn = pg_connect("host=".DBHOST.
					   " port=".DBPORT.
					   " dbname=".DBNAME.
					   " user=".DBUSER.
					   " password=".DBPASS)
			or die('Connection failed: ' . pg_last_error());
	
	// Find the project.
	$sql_sel_prj = "SELECT * FROM project WHERE uuid = '" . $uuid . "'";
    $sql_res_prj = pg_query($conn, $sql_sel_prj) or die('Query failed: ' . pg_last_error());
    while ($prj_row = pg_fetch_assoc($sql_res_prj)) {
		$prj_uid = $prj_row['uuid'];
        $prj_nam = $prj_row['name'];
        $prj_ttl = $prj_row['title'];
		$prj_bgn = $prj_row['beginning'];
		$prj_end = $prj_row['ending'];
		$prj_phs = $prj_row['phase'];
		$prj_int = $prj_row['introduction'];
		$prj_cas = $prj_row['cause'];
		$prj_dsc = $prj_row['descriptions'];
		$prj_cdt = $prj_row['created'];
		$prj_cby = $prj_row['created_by'];
		$prj_fpg = $prj_row['faceimage'];
    }
	
	pg_close($conn);
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>SurveyProjectManager</title>
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
		
		<!-- Import modal CSS -->
		<link href="lib/modal.css" rel="stylesheet" />
		
		<!-- Import external scripts for Bootstrap CSS -->
		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/bootstrap.min.js"></script>
		
		<script type="text/javascript" src="lib/refreshImage.js"></script>
		
		<!-- Import external scripts for calendar control -->
		<link rel="stylesheet" type="text/css" href="lib/calendar/codebase/dhtmlxcalendar.css"/>
		<script src="lib/calendar/codebase/dhtmlxcalendar.js"></script>
		<script type="text/javascript" src="lib/calendar.js"></script>
		
		<!-- Extension of the Bootstrap CSS for file uploads -->
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
		<script>
			$(document).on('change', '.btn-file :file', function() {
				var input = $(this),
				numFiles = input.get(0).files ? input.get(0).files.length : 1,
				label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
				input.trigger('fileselect', [numFiles, label]);
			});
				
			$(document).ready( function() {
				$('.btn-file :file').on('fileselect', function(event, numFiles, label) {
					var input = $(this).parents('.input-group').find(':text'),
					log = numFiles > 1 ? numFiles + ' files selected' : label;
					
					if( input.length ) {
						input.val(log);
					} else {
						if( log ) alert(log);
					}
				});
			});
		</script>
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
								<h2>プロジェクトの編集</h2>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
									<button id="btn_add_mat"
											name="btn_add_mat"
											class="btn btn-sm btn-default"
											type="submit" value="add_material"
											onclick="backToProject()">
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> マイページに戻る</span>
									</button>
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
			
			<!-- Avatar -->
			<div class="row">
				<table class='table table' style="border: hidden">
					<!-- iFrame for showing Avatar -->
					<tr style="text-align: center">
						<td colspan="2">
							<iframe id="iframe_avatar" 
									name="iframe_avatar"
									style="width: 610px; height: 410px; border: hidden; border-color: #999999;"
									src="avatar_uploaded.php?width=600&height=400&target=project&img_id=<?php echo $uuid; ?>">
							</iframe>
						</td>
					</tr>
					<tr>
						<form id="form_avatar" method="post" enctype="multipart/form-data">
							<td style="width: auto">
								<div class="input-group">
									<span class="input-group-btn">
										<span class="btn btn-primary btn-file">
											Browse&hellip;
											<input id="input_avatar"
												   name="avatar"　
												   type="file"
												   size="50"
												   accept=".jpg,.JPG,.jpeg,.JPEG" />
										</span>
									</span>
									<input id="name_avatar" 
										   name="name_avatar"
										   class="form-control" 
										   type="text"
										   readonly value=""/>
								</div>
							</td>
							<td style="width: 100px">
								<input id="btn-upload" 
									   name="btn-upload"
									   class="btn btn-md btn-success"
									   type="submit"
									   value="アップロード"
									   onclick='refreshAvatar(id="<?php echo $uuid;?>",h=400,w=600,target="project");'/>
							</td>
						</form>
					</tr>
				</table>
			</div>
			
			<!-- Project view -->
			<div class="row">
				<form action="update_project.php" method="post">
					<!-- Information about a user who registered this project -->
					<table class='table table'>
						<!------------------------
						   Project Infrormation
						------------------------->
						<tr style="background-color: #343399; color: #ffffff">
							<td>
								<span class="glyphicon glyphicon-user" aria-hidden="true"> 登録者の情報</span>
							</td>
							<td style="color: red"><?php echo $err; ?></td>
						</tr>
						<tr>
							<td style='width: 200px; text-align: center; vertical-align: middle'>登録者</td>
							<td><?php echo $prj_cby; ?></td>
						</tr>
						<tr>
							<td style='width: 200px; text-align: center; vertical-align: middle'>登録日時</td>
							<td><?php echo $prj_cdt; ?></td>
						</tr>
					</table>
					
					<!-- Generic Information about the project -->
					<table class='table table'>
						<!------------------------
						   Project Infrormation
						------------------------->
						<tr style="background-color: #343399; color: #ffffff">
							<td colspan="2">
								<span class="glyphicon glyphicon-info-sign" aria-hidden="true"> プロジェクト情報</span>
							</td>
						</tr>
						<tr>
							<td style='width: 200px; text-align: center; vertical-align: middle'>課題名</td>
							<td>
								<input class="form-control"  type='text' name="title" value="<?php echo $prj_ttl; ?>">
							</td>
						</tr>
						<tr>
							<td style='width: 200px; text-align: center; vertical-align: middle'>プロジェクト名</td>
							<td>
								<input class="form-control"  type='text' name="name" value="<?php echo $prj_nam; ?>">
							</td>
						</tr>
						<tr>
							<td style='text-align: center; vertical-align: middle'>期間と次数</td>
							<td>
								<div class="row">
									<div class="form-group col-lg-4">
										<div class="input-group">
											<span class="input-group-addon" id="basic-addon1">開始:</span>
											<input id="date_from"　
												   name="date_from"
												   class="form-control"
												   type="text"
												   placeholder="YYYY-MM-DD"
												   value="<?php echo $prj_bgn;?>" 
												   onclick="setSens('date_to', 'max');"
												   readonly="true">
										</div>
									</div>
									<div class="form-group col-lg-4">
										<div class="input-group">
											<span class="input-group-addon" id="basic-addon1">終了:</span>
											<input id="date_to"　
												   name="date_to" 
												   class="form-control"
												   type="text"
												   placeholder="YYYY-MM-DD"
												   value="<?php echo $prj_end;?>" 
												   onclick="setSens('date_from', 'min');"
												   readonly="true"></div>
										</div>
									<div class="form-group col-lg-4">
										<div class="input-group">
											<span class="input-group-addon" id="basic-addon1">調査次数:</span>
											<select class="combobox input-large form-control" name="phase" style='text-align: center'>
												<option value="<?php echo $prj_phs; ?>"><?php echo $prj_phs; ?></option>
												<?php
													for ($i = 1; $i <= 20; $i++) {
														echo "\t\t\t\t\t\t\t<option value='". $i ."'>" . $i . "</option>\n";
													}
												?>
											</select>
										</div>
									</div>
							</div></td>
						</tr>
						
						<tr>
							<td style='text-align: center; vertical-align: middle'>プロジェクト紹介</td>
							<td>
								<textarea class="form-control" style='resize: none;'rows='10' name='intro'><?php echo str_replace("<br />","",$prj_int); ?></textarea>
							</td>
						</tr>
						<tr>
							<td style='text-align: center; vertical-align: middle'>調査原因</td>
							<td>
								<textarea class="form-control" style='resize: none;'rows='10' name='cause'><?php echo str_replace("<br />","",$prj_cas); ?></textarea>
							</td>
						</tr>
						<tr>
							<td style='text-align: center; vertical-align: middle'>特記事項</td>
							<td>
								<textarea class="form-control" style='resize: none;'rows='10' name='desc'><?php echo str_replace("<br />","",$prj_dsc); ?></textarea>
							</td>
						</tr>
						
						<!-- Update button -->
						<tr style="border: hidden; padding: 0px; margin: 0px; text-align: right;">
							<td colspan="2">
								<button class="btn btn-md btn-success" type="submit" value="registeration">
									<span class="glyphicon glyphicon-refresh" aria-hidden="true"> プロジェクトの更新</span>
								</button>
							</td>
						</tr>
					</table>
				</form>
				
				<!-- Information about survey reports which registered this project -->
				<table id="tbl_rep" class="table table-strip">
					<tr style="background-color: #343399; color: #ffffff">
						<td colspan="6">
							<span class="glyphicon glyphicon-th" aria-hidden="true"> 調査報告書の情報</span>
						</td>
						<td style="text-align: right;">
							<button class="btn btn-md btn-success" type="submit" id="btn_add_prj_rep">新規登録</button>
						</td>
					</tr>
					<tr style="text-align: center">
						<td>タイトル</td>
						<td>巻号</td>
						<td>シリーズ</td>
						<td>出版社</td>
						<td>出版年</td>
						<td>備考</td>
						<td></td>
					</tr>
					<tr>
						<?php
							$conn = pg_connect("host=".DBHOST.
											" port=".DBPORT.
											" dbname=".DBNAME.
											" user=".DBUSER.
											" password=".DBPASS)
								 or die('Connection failed: ' . pg_last_error());
							
							// Find all members.
							$sql_sel_rep = "SELECT * from report WHERE prj_id='".$uuid."' ORDER BY id";
							
							// Excute the query and get the result of query.
							$result_select_rep = pg_query($conn, $sql_sel_rep);
							if (!$result_select_rep) {
								// Print the error messages and exit routine if error occors.
								echo "An error occurred in DB query.\n";
								exit;
							}
							// Fetch rows of projects.
							$rows_rep_all = pg_fetch_all($result_select_rep);
							foreach ($rows_rep_all as $row_rep){
								$rep_uuid = $row_rep['uuid'];
								$rep_ttl = $row_rep['title'];
								$rep_vol = $row_rep['volume'];
								$rep_num = $row_rep['edition'];
								$rep_srs = $row_rep['series'];
								$rep_pub = $row_rep['publisher'];
								$rep_yer = $row_rep['year'];
								$rep_dsc = $row_rep['descriptions'];
								
								echo "\t\t\t\t\t<tr style='text-align: center;'>\n";
								echo "\t\t\t\t\t\t<td style='width: 300px; vertical-align: middle;'>".$rep_ttl."</td>\n";
								if (!empty($rep_vol)) {
									if (!empty($rep_num)){
										echo "\t\t\t\t\t\t<td style='width: 100px; vertical-align: middle;'>".$rep_vol."(".$rep_num.")</td>\n";
									} else {
										echo "\t\t\t\t\t\t<td style='width: 100px; vertical-align: middle;'>".$rep_vol."</td>\n";
									}
								} else {
									echo "\t\t\t\t\t\t<td style='width: 100px; vertical-align: middle;'></td>\n";
								}
								echo "\t\t\t\t\t\t<td style='width: 200px; vertical-align: middle;'>".$rep_srs."</td>\n";
								echo "\t\t\t\t\t\t<td style='width: 150px; vertical-align: middle;'>".$rep_pub."</td>\n";
								echo "\t\t\t\t\t\t<td style='width: 150px; vertical-align: middle;'>".$rep_yer."</td>\n";
								echo "\t\t\t\t\t\t<td style='width: auto; vertical-align: middle;'>".$rep_dsc."</td>\n";
								
								// Control buttons
								echo "\t\t\t\t\t\t<td style='width: 150px; vertical-align: top; text-align: right'>\n";
								// Control menue
								echo "\t\t\t\t\t\t\t<div class='btn-group-vertical'>\n";
								
								// Create a button for deleting this material.
								// This operation can be conducted only by Administrators.
								echo "\t\t\t\t\t\t\t\t<button id='btn_del_rep'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_del_rep'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-danger'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
								echo "\t\t\t\t\t\t\t\t\t\tonclick=deleteReport('".$rep_uuid."','".$uuid."');>\n";
								echo "\t\t\t\t\t\t\t\t\t<span>報告書の削除</span>\n";
								echo "\t\t\t\t\t\t\t\t</button>\n";
								
								// Create a button for moving to consolidation page.
								echo "\t\t\t\t\t\t\t\t<button id='btn_edt_rep'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_edt_rep'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
								echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
								echo "\t\t\t\t\t\t\t\t\t\tonclick=editReport('".$rep_uuid."');>\n";
								echo "\t\t\t\t\t\t\t\t\t<span>書誌情報の編集</span>\n";
								echo "\t\t\t\t\t\t\t\t</button>\n";
								echo "\t\t\t\t\t\t\t</div>\n";
								echo "\t\t\t\t\t\t</td>";
								echo "\t\t\t\t\t</tr>\n";
							}
							
							// close the connection to DB.
							pg_close($conn);
						?>
					</tr>
				</table>
				
				<!-- Information about a user who registered this project -->
				<table id="tbl_mem" class="table table">
					<tr style="background-color: #343399; color: #ffffff">
						<td colspan="7">
							<span class="glyphicon glyphicon-th" aria-hidden="true"> 登録者の情報</span>
						</td>
						<td style="text-align: right;">
							<button class="btn btn-md btn-success" type="submit" id="btn_add_prj_mem">新規登録</button>
						</td>
					</tr>
					
					<!-- Trigger/Open The Modal -->
					<tr>
						<?php
							$conn = pg_connect("host=".DBHOST.
											" port=".DBPORT.
											" dbname=".DBNAME.
											" user=".DBUSER.
											" password=".DBPASS)
								 or die('Connection failed: ' . pg_last_error());
								 
							// For each row, HTML list is created and showed on browser.
							// find the roles in the project.
							// echo $sql_sel_mem_all;
							$sql_sel_rol = "SELECT * FROM role WHERE prj_id='".$prj_uid."'";
							$sql_res_rol = pg_query($conn, $sql_sel_rol);
							
							// Fetch rows of projects. 
							$rol_rows = pg_fetch_all($sql_res_rol);
							
							if (!empty($rol_rows)) {
								foreach ($rol_rows as $rol_row){
									// Get a value in each field.
									$mem_uid = $rol_row['mem_id'];		// Project name
									$rol_bgn = $rol_row['beginning'];	// The date of the project begining.
									$rol_end = $rol_row['ending'];		// The date of the project ending.
									$rol_rol = $rol_row['rolename'];	// The phase for the continuous project.
									
									// Find the member.
									$sql_sel_mem = "SELECT * FROM member WHERE uuid = '" . $mem_uid . "'";
									$sql_res_mem = pg_query($conn, $sql_sel_mem) or die('Query failed: ' . pg_last_error());
									while ($mem_row = pg_fetch_assoc($sql_res_mem)) {
										$mem_ava = $mem_row['avatar'];
										$mem_snm = $mem_row['surname'];
										$mem_fnm = $mem_row['firstname'];
										$mem_unm = $mem_row['username'];
										$mem_utp = $mem_row['usertype'];
									}
									if($mem_ava != ""){
										echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>";
										echo "\t\t\t\t\t\t\t<a href='project_members_view.php?mem_uuid=" .$mem_uid. "'>\n";
										echo "\t\t\t\t\t\t\t\t<img height=64 width=64 src='avatar_member_list.php?mem_uuid=" .$mem_uid."' alt='img'/>\n";
										echo "\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t</td>\n";
									} else {
										echo "\t\t\t\t\t\t<td style='vertical-align: top; text-align: right;'>\n";
										echo "\t\t\t\t\t\t\t<a href='project_members_view.php?mem_uuid=" .$mem_uid. "'>\n";
										echo "\t\t\t\t\t\t\t\t<img height=64 width=64  src='images/avatar.jpg' alt='img'/>\n";
										echo "\t\t\t\t\t\t\t</a>\n";
										echo "\t\t\t\t\t\t</td>\n";
									}
									echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $mem_snm. " " .$mem_fnm. "</td>\n";
									echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $rol_bgn. "</td>\n";
									echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $rol_end. "</td>\n";
									echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $rol_rol. "</td>\n";
									echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $mem_unm. "</td>\n";
									echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $mem_utp. "</td>";
									
									// Control buttons
									echo "\t\t\t\t\t\t<td style='width: 150px; vertical-align: top; text-align: right'>\n";
									// Control menue
									echo "\t\t\t\t\t\t\t<div class='btn-group-vertical'>\n";
									
									// Create a button for deleting this material.
									// This operation can be conducted only by Administrators.
									echo "\t\t\t\t\t\t\t\t<button id='btn_del_rep'\n";
									echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_del_rep'\n";
									echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-danger'\n";
									echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
									echo "\t\t\t\t\t\t\t\t\t\tonclick=deleteMember('".$mem_uid."');>\n";
									echo "\t\t\t\t\t\t\t\t\t<span>メンバーの削除</span>\n";
									echo "\t\t\t\t\t\t\t\t</button>\n";
									
									// Create a button for moving to consolidation page.
									echo "\t\t\t\t\t\t\t\t<button id='btn_edt_mem'\n";
									echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_edt_mem'\n";
									echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
									echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
									echo "\t\t\t\t\t\t\t\t\t\tonclick=editMember('".$mem_uid."');>\n";
									echo "\t\t\t\t\t\t\t\t\t<span>メンバーの編集</span>\n";
									echo "\t\t\t\t\t\t\t\t</button>\n";
									echo "\t\t\t\t\t\t\t</div>\n";
									echo "\t\t\t\t\t\t</td>";
									echo "\t\t\t\t\t</tr>\n";
								}
							}
							// Close the connection.
							pg_close($conn);
						?>
					</tr>
				</table>
			</div>
		</div>
		
		<!----------------------------------------------
		----   The Modal for adding project members ----
		----------------------------------------------->
		<div id="modal_prj_mem" class="modal">
			<!-- Modal content -->
			<div class="modal-content" style="width: 800px">
				<span class="close">×</span>
				<form action="insert_role.php" method="post">
					<input type="hidden" name= "prj_uuid" value="<?php echo $uuid;?>"/>
					<button class="btn btn-md btn-success" type="submit">
						<span class="glyphicon glyphicon-plus" aria-hidden="true"> 新規登録</span>
					</button>
					<table id="new_members" class='table table-striped'>
						<thead style="text-align: center">
							<tr>
								<td></td>
								<td></td>
								<td>氏名</td>
								<td>ユーザー名</td>
								<td style="width: 150px">参加年月日</td>
								<td style="width:150px">終了年月日</td>
							</tr>
						</thead>
						<?php
							$conn = pg_connect("host=".DBHOST.
											" port=".DBPORT.
											" dbname=".DBNAME.
											" user=".DBUSER.
											" password=".DBPASS)
								 or die('Connection failed: ' . pg_last_error());
							
							// Find all members.
							$sql_sel_mem_all = "SELECT * from member";
							
							// Excute the query and get the result of query.
							$result_select_mem_all = pg_query($conn, $sql_sel_mem_all);
							if (!$result_select_mem_all) {
								// Print the error messages and exit routine if error occors.
								echo "An error occurred in DB query.\n";
								exit;
							}
								 
							// Fetch rows of projects. 
							$rows_mem_all = pg_fetch_all($result_select_mem_all);
							foreach ($rows_mem_all as $row_mem_all){
								$allmem_uuid = $row_mem_all['uuid'];
								$allmem_ava = $row_mem_all['avatar'];
								$allmem_snm = $row_mem_all['surname'];
								$allmem_fnm = $row_mem_all['firstname'];
								$allmem_unm = $row_mem_all['username'];
								$allmem_utp = $row_mem_all['usertype'];
								
								echo "<tr style='text-align: center;'>\n";
								echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'><input type='checkbox' name='prj_mem[]' value='" .$allmem_uuid. "' /></td>\n";
								if($allmem_ava != ""){
									echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'>\n";
									echo "\t\t\t\t\t\t\t\t<a href='project_members_view.php?mem_uuid=" .$allmem_uuid. "'>\n";
									echo "\t\t\t\t\t\t\t\t\t<img height=64 width=64 src='avatar_member_list.php?mem_uuid=" .$allmem_uuid."' alt='img'/>\n";
									echo "\t\t\t\t\t\t\t\t</a>\n";
									echo "\t\t\t\t\t\t\t</td>\n";
								} else {
									echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'><a href='project_members_view.php?mem_uuid=" .$allmem_uuid. "'>";
									echo "\t\t\t\t\t\t\t\t<img height=64 width=64  src='images/avatar.jpg' alt='img'/></a></td>";
								}
								echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'>". $allmem_snm. " " .$allmem_fnm. "</td>\n";
								echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'>". $allmem_unm. "</td>\n";
								echo "\t\t\t\t\t\t\t<td style='width:150px'>\n";
								echo "\t\t\t\t\t\t\t\t".'<input class="form-control" type="text" name="from_'.$allmem_uuid.'" id="from_'.$allmem_uuid.'" onclick="newDate('."'".$allmem_uuid."'".');" readonly="true"/>'."\n";
								echo "\t\t\t\t\t\t\t</td>\n";
								echo "\t\t\t\t\t\t\t<td style='width:150px'>\n";
								echo "\t\t\t\t\t\t\t\t".'<input class="form-control" type="text" name="to_'.$allmem_uuid.'" id="to_'.$allmem_uuid.'" onclick="newDate('."'".$allmem_uuid."'".');" readonly="true"/>'."\n";
								echo "\t\t\t\t\t\t\t</td>\n";
								echo "\t\t\t\t\t\t\t<td style='vertical-align: middle;'></td>\n";
								echo "\t\t\t\t\t\t</tr>\n";
								
								// Generate calendar for selecting dates.
								echo "\t\t\t\t\t\t".'<script type="text/javascript">newDate("from_'.$allmem_uuid.'");</script>'."\n";
								echo "\t\t\t\t\t\t".'<script type="text/javascript">newDate("to_'.$allmem_uuid.'");</script>'."\n";
							}
							
							// close the connection to DB.
							pg_close($conn);
						?>
					</table>
				</form>
			</div>
		</div>
		
		
		<!----------------------------------------------
		----   The Modal for adding project reports ----
		----------------------------------------------->
		<div id="modal_prj_rep" class="modal">
			<!-- Modal content -->
			<div class="modal-content" style="width: 800px">
				<span class="close">×</span>
				<form action="insert_report.php" method="POST">
					<input type="hidden" name= "prj_uuid" value="<?php echo $uuid;?>"/>
					<table id="new_report" class="table table-striped" style="vertical-align: middle;">
						<tr>
							<td style="width: 150px; text-align: center">タイトル</td>
							<td style="width: auto" colspan="3">
								<input class="form-control"  type='text' id="rep_nam" name="rep_nam">
							</td>
							<td style="width: 150px; text-align: center">シリーズ</td>
							<td style="width: auto" colspan="3">
								<input class="form-control"  type='text' name="rep_srs">
							</td>
						</tr>
						<tr>
							<td style="width: 50px; text-align: center">巻</td>
							<td style="width: 150px" colspan="3">
								<input class="form-control"  type='text' id="rep_vol" name="rep_vol"/>
							</td>
							<td style="width: 50px; text-align: center">号</td>
							<td style="width: 150px"  colspan="3">
								<input class="form-control"  type='text' id="rep_num" name="rep_num">
							</td>
						</tr>
						<tr>
							<td style="width: 50px; text-align: center">出版者</td>
							<td style="width: 150px"  colspan="3">
								<input class="form-control"  type='text' id="rep_pub" name="rep_pub"/>
							</td>
							<td style="width: 100px; text-align: center">出版年</td>
							<td style="width: 150px"  colspan="3">
								<input id="rep_yer" 
									   name="rep_yer" 
									   class="form-control" 
									   type="text" 
									   placeholder="YYYY-MM-DD" 
									   onclick="newDate('pub_dt');" 
									   readonly="true"/>
							</td>
						</tr>
						<tr>
							<td style="width: 50px; text-align: center">備 考</td>
							<td colspan="7">
								<input class="form-control" type='text' id="rep_dsc" name="rep_dsc"/>
							</td>
						</tr>
						<tr>
							<td style="width: 50px; text-align: right" colspan="8">
								<button class="btn btn-md btn-success" type="submit">
									<span class="glyphicon glyphicon-plus" aria-hidden="true"> 新規登録</span>
								</button>
							</td>
						</tr>
					</table>
				</form>
				<script type="text/javascript">newDate("rep_yer");</script>
			</div>
		</div>
		
		<script>
			// Get the modal
			var mdl_prj_mem = document.getElementById('modal_prj_mem');
			var mdl_prj_rep = document.getElementById('modal_prj_rep');

			// Get the button that opens the modal
			var btn_add_prj_mem = document.getElementById("btn_add_prj_mem");
			var btn_add_prj_rep = document.getElementById("btn_add_prj_rep");
			
			// Get the <span> element that closes the modal
			var span_prj_mem = document.getElementsByClassName("close")[0];
			var span_prj_rep = document.getElementsByClassName("close")[1];
			
			// When the user clicks the button, open the modal
			btn_add_prj_mem.onclick = function() {
				mdl_prj_mem.style.display = "block";
			};
			
			// When the user clicks the button, open the modal
			btn_add_prj_rep.onclick = function() {
				mdl_prj_rep.style.display = "block";
			};
			
			// When the user clicks on <span> (x), close the modal
			span_prj_mem.onclick = function() {
				mdl_prj_mem.style.display = "none";
			};
			span_prj_rep.onclick = function() {
				mdl_prj_rep.style.display = "none";
			};
			
			// When the user clicks anywhere outside of the modal, close it
			window.onclick = function(event) {
				if (event.target == mdl_prj_mem) {
					mdl_prj_mem.style.display = "none";
				}
				
				if (event.target == mdl_prj_rep) {
					mdl_prj_rep.style.display = "none";
				}
			};
			
			function backToProject() {
				window.location.href = "main.php";
				return false;
			}
			
			function deleteReport(uuid, prj_id) {
				var diag_del_rep = confirm("この報告書を削除しますか？");
				if (diag_del_rep === true) {
					// Send the member id to the PHP script to drop selected project from DB.
					window.location.href = "delete_report.php?uuid=" + uuid + "&prj_id=" + prj_id;
				}
				return false;
			}
			
			function backToProject() {
				window.location.href = "main.php";
				return false;
			}
		</script>
	</body>
</html>