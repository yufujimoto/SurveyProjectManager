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
	$sql_select_prj = "SELECT * FROM project WHERE uuid = '" . $uuid . "'";
    $sql_result_prj = pg_query($conn, $sql_select_prj) or die('Query failed: ' . pg_last_error());
    while ($prj_row = pg_fetch_assoc($sql_result_prj)) {
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
	
	// $sql_select_mem_all = "SELECT M.uuid, M.avatar, M.surname, M.firstname, M.username, M.usertype FROM role INNER JOIN member as M ON role.mem_id = M.uuid WHERE prj_id != '" .$uuid. "'";
	$sql_select_mem_all = "SELECT * from member";

	echo $sql_select_mem_all;
	// Excute the query and get the result of query.
	$result_select_mem_all = pg_query($conn, $sql_select_mem_all);
	if (!$result_select_mem_all) {
		// Print the error messages and exit routine if error occors.
		echo "An error occurred in DB query.\n";
		exit;
	}
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
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> プロジェクトの管理に戻る</span>
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
				
				<!-- Information about a user who registered this project -->
				<table class='table table'>
					<tr style="background-color: #343399; color: #ffffff">
						<td colspan="7">
							<span class="glyphicon glyphicon-th" aria-hidden="true"> 調査報告書の情報</span>
						</td>
						<td style="text-align: right;">
							<button class="btn btn-md btn-success" type="submit" id="btn_add_prjMem">報告書の追加</button>
							<button class="btn btn-md btn-danger" type="submit" id="btn_del_prjMem">報告書の削除</button>
						</td>
					</tr>
				</table>
				
				<!-- Information about a user who registered this project -->
				<table class='table table'>
					<tr style="background-color: #343399; color: #ffffff">
						<td colspan="7">
							<span class="glyphicon glyphicon-th" aria-hidden="true"> 登録者の情報</span>
						</td>
						<td style="text-align: right;">
							<button class="btn btn-md btn-success" type="submit" id="btn_add_prj_mem">新規メンバーの追加</button>
							<button class="btn btn-md btn-danger" type="submit" id="btn_del_prj_mem">既存メンバーの削除</button>
						</td>
					</tr>
					<!-- Trigger/Open The Modal -->

					<tr>
						<?php
							// For each row, HTML list is created and showed on browser.
							// find the roles in the project.
							// echo $sql_select_mem_all;
							$rol_query = "SELECT * FROM role WHERE prj_id='".$prj_uid."'";
							$rol_result = pg_query($conn, $rol_query);
							
							// Fetch rows of projects. 
							$rows_role = pg_fetch_all($rol_result);
							
							if (!empty($rows_role)) {
								foreach ($rows_role as $row_role){
									// Get a value in each field.
									$mem_uid = $row_role['mem_id'];	// Project name
									$rol_bgn = $row_role['beginning'];	// The date of the project begining.
									$rol_end = $row_role['ending'];		// The date of the project ending.
									$rol_rol = $row_role['rolename'];			// The phase for the continuous project.
									
									// Find the member.
									$mem_query = "SELECT * FROM member WHERE uuid = '" . $mem_uid . "'";
									$mem_result = pg_query($conn, $mem_query) or die('Query failed: ' . pg_last_error());
									while ($mem_row = pg_fetch_assoc($mem_result)) {
										$mem_ava = $mem_row['avatar'];
										$mem_snm = $mem_row['surname'];
										$mem_fnm = $mem_row['firstname'];
										$mem_unm = $mem_row['username'];
										$mem_utp = $mem_row['usertype'];
									}
									echo "\t\t\t\t\t<tr style='text-align: center;'><td style='vertical-align: middle;'><input type='radio' name='member' value='" .$mem_uid. "' /></td>";
									if($mem_ava != ""){
										echo "<td style='vertical-align: middle;'><a href='project_members_view.php?mem_uuid=" .$mem_uid. "'><img height=64 width=64 src='avatar_member_list.php?mem_uuid=" .$mem_uid."' alt='img'/></a></td>";
									} else {
										echo "<td style='vertical-align: middle;'><a href='project_members_view.php?mem_uuid=" .$mem_uid. "'><img height=64 width=64  src='images/avatar.jpg' alt='img'/></a></td>";
									}
									echo "<td style='vertical-align: middle;'>". $mem_snm. " " .$mem_fnm. "</td>";
									echo "<td style='vertical-align: middle;'>". $rol_bgn. "</td>";
									echo "<td style='vertical-align: middle;'>". $rol_end. "</td>";
									echo "<td style='vertical-align: middle;'>". $rol_rol. "</td>";
									echo "<td style='vertical-align: middle;'>". $mem_unm. "</td>";
									echo "<td style='vertical-align: middle;'>". $mem_utp. "</td></tr>\n";
								}
							}
						?>
					</tr>
				</table>
			</div>
		</div>
		
		<!-- The Modal -->
		<div id="modal_prj_mem" class="modal">
			<!-- Modal content -->
			<div class="modal-content" style="width: 800px">
				<span class="close">×</span>
				<form action="insert_role.php" method="post">
					<input type="hidden" name= "prj_uuid" value="<?php echo $uuid;?>"</input>
					<button class="btn btn-md btn-success" type="submit" value="registeration"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> メンバーに登録</button>
					<table id="members" class='table table-striped'>
						<thead style="text-align: center">
							<tr><td></td><td></td><td>氏名</td><td>ユーザー名</td><td style="width: 150px">参加年月日</td><td style="width:150px">終了年月日</td></tr>
						</thead>
						<?php
							// Fetch rows of projects. 
							$rows_allmember = pg_fetch_all($result_select_mem_all);
							foreach ($rows_allmember as $row_allmember){
								$allmem_uuid = $row_allmember['uuid'];
								$allmem_ava = $row_allmember['avatar'];
								$allmem_snm = $row_allmember['surname'];
								$allmem_fnm = $row_allmember['firstname'];
								$allmem_unm = $row_allmember['username'];
								$allmem_utp = $row_allmember['usertype'];
								
								echo "\t\t\t\t\t<tr style='text-align: center;'><td style='vertical-align: middle;'><input type='checkbox' name='prj_mem[]' value='" .$allmem_uuid. "' /></td>";
								if($allmem_ava != ""){
									echo "<td style='vertical-align: middle;'><a href='project_members_view.php?mem_uuid=" .$allmem_uuid. "'><img height=64 width=64 src='avatar_member_list.php?mem_uuid=" .$allmem_uuid."' alt='img'/></a></td>";
								} else {
									echo "<td style='vertical-align: middle;'><a href='project_members_view.php?mem_uuid=" .$allmem_uuid. "'><img height=64 width=64  src='images/avatar.jpg' alt='img'/></a></td>";
								}
								echo "<td style='vertical-align: middle;'>". $allmem_snm. " " .$allmem_fnm. "</td>";
								echo "<td style='vertical-align: middle;'>". $allmem_unm. "</td>\n";
								echo '<td style="width:150px"><input class="form-control" type="text" name="from_'.$allmem_uuid.'" id="from_'.$allmem_uuid.'" onclick="newDate('."'".$allmem_uuid."'".');" readonly="true"></td>'."\n";
								echo '<td style="width:150px"><input class="form-control" type="text" name="to_'.$allmem_uuid.'" id="to_'.$allmem_uuid.'" onclick="newDate('."'".$allmem_uuid."'".');" readonly="true"></td>'."\n";
								echo "<td style='vertical-align: middle;'></td></tr>\n";
								
								// Generate calendar for selecting dates.
								echo '<script type="text/javascript">newDate("from_'.$allmem_uuid.'");</script>';
								echo '<script type="text/javascript">newDate("to_'.$allmem_uuid.'");</script>';
							}
							
							// close the connection to DB.
							pg_close($conn);
						?>
					</table>
				</form>
			</div>
		</div>
		
		<script>
			// Get the modal
			var mdl_prj_mem = document.getElementById('modal_prj_mem');
			
			// Get the button that opens the modal
			var btn_add_prj_mem = document.getElementById("btn_add_prj_mem");
			
			// Get the <span> element that closes the modal
			var span = document.getElementsByClassName("close")[0];
			
			// When the user clicks the button, open the modal
			btn_add_prj_mem.onclick = function() {
				mdl_prj_mem.style.display = "block";
			};
			
			// When the user clicks on <span> (x), close the modal
			span.onclick = function() {
				mdl_prj_mem.style.display = "none";
			};
			
			// When the user clicks anywhere outside of the modal, close it
			window.onclick = function(event) {
				if (event.target == mdl_prj_mem) {
					mdl_prj_mem.style.display = "none";
				}
			};
		</script>
	</body>
</html>