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
	$err = $_REQUEST['err'];
	$uuid = $_REQUEST['prj_id'];
	
	// Generate unique ID for saving temporal files.
	$tmp_nam = uniqid($_SESSION["USERNAME"]."_");
	
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
	
	// Find the project.
	$sql_sel_prj = "SELECT * FROM project WHERE uuid = '" . $uuid . "'";
    $sql_res_prj = pg_query($conn, $sql_sel_prj);
	if (!$sql_res_prj) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
    while ($prj_row = pg_fetch_assoc($sql_res_prj)) {
		$prj_id = $prj_row['uuid'];
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
		<script src="lib/jquery-3.1.1/jquery.min.js"></script>
		\n
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
		
		<!-- Get an avatar image on load. -->
		<script>
			function doOnLoad(){
				refreshAvatar(id="<?php echo $tmp_nam;?>",h=400,w="",target="consolidation");
			}
		</script>
	</head>

	<body onload="doOnLoad();">
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
									<button id="btn_udt_prj"
											name="btn_udt_prj"
											class="btn btn-sm btn-primary"
											type="submit"
											onclick='updateProject("<?php echo $prj_id; ?>","<?php echo $tmp_nam; ?>");'>
										<span class="glyphicon glyphicon-save" aria-hidden="true"> 上書き保存</span>
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
			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#tab_bsc">基本情報</a></li>
				<li><a data-toggle="tab" href="#tab_dsc">概要</a></li>
				<li><a data-toggle="tab" href="#tab_rep">報告書管理</a></li>
				<li><a data-toggle="tab" href="#tab_mem">メンバー管理</a></li>
			</ul>
			
			<div class="tab-content">
				<div id="tab_bsc" class="tab-pane fade in active">
					<h4><span class="glyphicon glyphicon-info-sign" aria-hidden="true"> 基本情報</span></h4>
					<table class="table table" style="border: hidden">
						<!-- iFrame for showing Avatar -->
						<tr style="text-align: left">
							<td>
								<iframe id="iframe_avatar"
										name="iframe_avatar"
										style="width: 510px;
										height: 300px;
										border:
										hidden;
										border-color: #999999;"
										src="avatar_uploaded.php?target=project&height=300&width=500&img_id=<?php echo $prj_id; ?>">
								</iframe>
								<form id="form_avatar" class="form-inline" method="post" enctype="multipart/form-data">
									<div class="form-group">
										<div class="input-group">
											<span class="input-group-btn">
												<span class="btn btn-primary btn-file">画像の参照&hellip;
													<input id="input_avatar"
													   name="avatar"　
													   type="file"
													   size="50"
													   accept=".jpg,.JPG,.jpeg,.JPEG" />
												</span>
											</span>
											<input id="name_avatar"
												   name="name_avatar"
												   type="text"
												   class="form-control"
												   style="width: 300px"
												   readonly/>
										</div>
									</div>
									<div class="form-group">
										<input id="btn-upload"
											   name="btn-upload"
											   class="btn btn-md btn-success"
											   type="submit"
											   value="アップロード"
											   style="width: 100px"
											   onclick='refreshAvatar(id="<?php echo $tmp_nam;?>",h=300,w="",target="consolidation");'/>
									</div>
								</form>
							</td>
							<td id="td_prj_inf" style='vertical-align: top;'>
								<div id="div_prj_bsc_inf" name="div_con_inf">
									<div id="div_prj_reg_psn" class="input-group">
										<span class="input-group-addon" style="width: 120px">登録者:</span>
										<input id="prj_reg_psn"
											   name="prj_reg_psn"
											   class="form-control"
											   type="text"
											   style="width: 454px;"
											   readonly="true"
											   value="<?php echo $prj_cby; ?>"
											   />
									</div>
									<div id="div_prj_reg_dt" class="input-group">
										<span class="input-group-addon" style="width: 120px">登録日時:</span>
										<input id="prj_reg_dt"
											   name="prj_reg_dt"
											   class="form-control"
											   type="text"
											   style="width: 454px;"
											   readonly="true"
											   value="<?php echo $prj_cdt; ?>"
											   />
									</div>
									<div id="div_prj_ttl" class="input-group">
										<span class="input-group-addon" style="width: 120px">課題名:</span>
										<input id="prj_ttl"
											   name="prj_ttl"
											   class="form-control"
											   type="text"
											   style="width: 454px;"
											   value="<?php echo $prj_ttl; ?>"
											   />
									</div>
									<div id="div_prj_nam" class="input-group">
										<span class="input-group-addon" style="width: 120px">プロジェクト名:</span>
										<input id="prj_nam"
											   name="prj_nam"
											   class="form-control"
											   type="text"
											   style="width: 454px;"
											   value="<?php echo $prj_nam; ?>"
											   />
									</div>
									<div id="div_prj_bgn" class="input-group">
										<span class="input-group-addon" style="width: 120px">存在期間:</span>
										<div class="input-group-vertical">
											<div id="div_grp_prj_bgn" class="input-group">
												<span class="input-group-addon" style="width: 100px">開始年月日</span>
												<input id="prj_bgn"
													   name="prj_bgn"
													   class="form-control"
													   type="text"
													   style="width: 354px"
													   onclick="newDate('date_from');"  
													   value="<?php echo $prj_bgn; ?>"/>
											</div>
											<script type="text/javascript">newDate("prj_bgn");</script>
											
											<div id="div_grp_prj_end" class="input-group">
												<span class="input-group-addon" style="width: 100px">終了年月日</span>
												<input id="prj_end"
													   name="prj_end"
													   class="form-control"
													   type="text"
													   style="width: 354px" 
													   onclick="newDate('date_to');" 
													   value="<?php echo $prj_end; ?>"/>
											</div>
											<script type="text/javascript">newDate("prj_end");</script>
											
											<div id="div_grp_con_end" class="input-group">
												<span class="input-group-addon" style="width: 100px">調査次数</span>
												<select id="prj_phs" 
														name="prj_phs"
														class="combobox input-large form-control" 
														style="text-align: center; width: 354px">
													<option value="<?php echo $prj_phs; ?>"><?php echo $prj_phs; ?></option>
													<?php
														for ($i = 1; $i <= 20; $i++) {
															echo "\t\t\t\t\t\t\t<option value='". $i ."'>" . $i . "</option>\n";
														}
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</td>
						</tr>
					</table>
				</div>
				<div id="tab_dsc" class="tab-pane fade">
					<h4><span class="glyphicon glyphicon-list-alt" aria-hidden="true"> 説明記述</span></h4>
					<table>
						<tr>
							<td colspan="2">
								<div class="input-group">
									<span class="input-group-addon" style="width: 120px">概　要:</span>
									<textarea id="prj_int"
											  name="prj_int"
											  class="form-control"
											  style="resize: none;
													 width: 1000px;
													 text-align: left"
													 rows="10"><?php echo str_replace("<br />","",$prj_int); ?></textarea>
								</div>
								<div class="input-group">
									<span class="input-group-addon" style="width: 120px">調査原因:</span>
									<textarea id="prj_cas"
											  name="prj_cas"
											  class="form-control"
											  style="resize: none;
													 width: 1000px;
													 text-align: left"
													 rows="10"><?php echo str_replace("<br />","",$prj_cas); ?></textarea>
								</div>
								<div class="input-group">
									<span class="input-group-addon" style="width: 120px">特記事項:</span>
									<textarea id="prj_dsc"
											  name="prj_dsc"
											  class="form-control"
											  style="resize: none;
													 width: 1000px;
													 text-align: left"
													 rows="10"><?php echo str_replace("<br />","",$prj_dsc); ?></textarea>
								</div>
							</td>
						</tr>
					</table>
				</div>
			
				<div id="tab_rep" class="tab-pane fade">
					<h4><span class="glyphicon glyphicon-th" aria-hidden="true"> 報告書管理</span></h4>
					<table>
						<tr>
							<td style="text-align: right;">
								<button id="btn_add_prj_rep"
										name="btn_add_prj_rep"
										class="btn btn-sm btn-success"
										type="submit">
									<span class="glyphicon glyphicon-plus" aria-hidden="true"> 報告書の登録</span>
								</button>
							</td>
						</tr>
						<tr>
							<td>調査報告書の情報</td>
						</tr>
						<tr>
							<td colspan="2">
								<table id="tbl_rep" class="table table">
									<tr style="text-align: center">
										<td style="width: 300px; vertical-align: middle;">タイトル</td>
										<td style="width: 100px; vertical-align: middle;">巻号</td>
										<td style="width: 200px; vertical-align: middle;">シリーズ</td>
										<td style="width: 150px; vertical-align: middle;">出版社</td>
										<td style="width: 150px; vertical-align: middle;">出版年</td>
										<td style="width: auto; vertical-align: middle;">備考</td>
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
												echo "\t\t\t\t\t\t\t\t\t\tonclick=deleteReport('".$uuid."','".$rep_uuid."');>\n";
												echo "\t\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-remove' aria-hidden='true'> 報告書の削除</span>\n";
												echo "\t\t\t\t\t\t\t\t</button>\n";
												
												// Create a button for moving to consolidation page.
												echo "\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t</td>";
												echo "\t\t\t\t\t</tr>\n";
											}
											
											// close the connection to DB.
											pg_close($conn);
										?>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>

				<div id="tab_mem" class="tab-pane fade">
					<h4><span class="glyphicon glyphicon-th" aria-hidden="true"> メンバー管理</span></h4>
					<table>
						<tr>
							<td style="text-align: right;">
								<button id="btn_add_prj_mem"
										name="btn_add_prj_mem"
										class="btn btn-sm btn-success"
										type="submit">
									<span class="glyphicon glyphicon-plus" aria-hidden="true"> メンバーの登録</span>
								</button><script type="text/javascript">newDate("rep_yer");</script>
							</td>
						</tr>
						<tr>
							<td>現在参加中のメンバー</td>
						</tr>
						<tr>
							<td colspan="2">
								<table id="tbl_mem" class="table table">
									<tr style="text-align: center">
										<td></td>
										<td style="width: 250px">所属組織</td>
										<td style="width: 250px">所属部門</td>
										<td style="width: 250px">氏名</td>
										<td style="width: 150px">参画開始</td>
										<td style="width: 150px">参画終了</td>
										<td style="width: 200px">役割</td>
										<td style="width: 250px">ユーザー名</td>
										<td></td>
									</tr>
									<tr style="text-align: center">
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
											$sql_sel_rol = "SELECT
												B.prj_id,
												C.name,
												C.section,
												B.mem_id,
												A.surname,
												A.firstname,
												A.username,
												A.avatar,
												B.beginning,
												B.ending,
												B.rolename
											FROM (member AS A LEFT JOIN role AS B ON B.mem_id = A.uuid)
											LEFT JOIN organization AS C ON A.org_id = C.uuid 
											WHERE B.prj_id='".$prj_id."'";

											
											//$sql_sel_rol = "SELECT * FROM role WHERE prj_id='".$prj_id."'";
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
													$mem_ava = $rol_row['avatar'];
													$mem_snm = $rol_row['surname'];
													$mem_fnm = $rol_row['firstname'];
													$mem_unm = $rol_row['username'];
													$org_nam = $rol_row['name'];
													$org_sec = $rol_row['section'];
													
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
													echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $org_nam. "</td>\n";
													echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $org_sec. "</td>\n";
													echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $mem_snm. " " .$mem_fnm. "</td>\n";
													echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $rol_bgn. "</td>\n";
													echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $rol_end. "</td>\n";
													echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $rol_rol. "</td>\n";
													echo "\t\t\t\t\t\t<td style='vertical-align: middle;'>". $mem_unm. "</td>\n";
													
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
													echo "\t\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-remove' aria-hidden='true'> メンバーの削除</span>\n";
													echo "\t\t\t\t\t\t\t\t</button>\n";
													
													// Create a button for moving to consolidation page.
													echo "\t\t\t\t\t\t\t\t<button id='btn_edt_mem'\n";
													echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_edt_mem'\n";
													echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
													echo "\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
													echo "\t\t\t\t\t\t\t\t\t\tonclick=editMember('".$mem_uid."');>\n";
													echo "\t\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-pencil' aria-hidden='true'> メンバーの編集</span>\n";
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
							</td>
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
					<input type="hidden" name= "prj_id" value="<?php echo $uuid;?>"/>
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
			
			function updateProject(prj_id,tmp_nam){
				var prj_form = document.createElement("form");
				
				var inp_prj_id = document.createElement("input");
				inp_prj_id.setAttribute("type", "hidden");
				inp_prj_id.setAttribute("id", "prj_id");
				inp_prj_id.setAttribute("name", "prj_id");
				inp_prj_id.setAttribute("value", prj_id);
				
				var inp_img_fl = document.createElement("input");
				inp_img_fl.setAttribute("type", "hidden");
				inp_img_fl.setAttribute("id", "img_fl");
				inp_img_fl.setAttribute("name", "img_fl");
				inp_img_fl.setAttribute("value", tmp_nam+".jpg");
				
				var prj_ttl = document.getElementById("prj_ttl").value;
				var inp_prj_ttl = document.createElement("input");
				inp_prj_ttl.setAttribute("type", "hidden");
				inp_prj_ttl.setAttribute("id", "prj_ttl");
				inp_prj_ttl.setAttribute("name", "prj_ttl");
				inp_prj_ttl.setAttribute("value", prj_ttl);
				
				var prj_nam = document.getElementById("prj_nam").value;
				var inp_prj_nam = document.createElement("input");
				inp_prj_nam.setAttribute("type", "hidden");
				inp_prj_nam.setAttribute("id", "prj_nam");
				inp_prj_nam.setAttribute("name", "prj_nam");
				inp_prj_nam.setAttribute("value", prj_nam);
				
				var prj_bgn = document.getElementById("prj_bgn").value;
				var inp_prj_bgn = document.createElement("input");
				inp_prj_bgn.setAttribute("type", "hidden");
				inp_prj_bgn.setAttribute("id", "prj_bgn");
				inp_prj_bgn.setAttribute("name", "prj_bgn");
				inp_prj_bgn.setAttribute("value", prj_bgn);
				
				var prj_end = document.getElementById("prj_end").value;
				var inp_prj_end = document.createElement("input");
				inp_prj_end.setAttribute("type", "hidden");
				inp_prj_end.setAttribute("id", "prj_end");
				inp_prj_end.setAttribute("name", "prj_end");
				inp_prj_end.setAttribute("value", prj_end);
				
				var prj_phs = document.getElementById("prj_phs").value;
				var inp_prj_phs = document.createElement("input");
				inp_prj_phs.setAttribute("type", "hidden");
				inp_prj_phs.setAttribute("id", "prj_phs");
				inp_prj_phs.setAttribute("name", "prj_phs");
				inp_prj_phs.setAttribute("value", prj_phs);
				
				var prj_int = document.getElementById("prj_int").value;
				var inp_prj_int = document.createElement("input");
				inp_prj_int.setAttribute("type", "hidden");
				inp_prj_int.setAttribute("id", "prj_int");
				inp_prj_int.setAttribute("name", "prj_int");
				inp_prj_int.setAttribute("value", prj_int);
				
				var prj_cas = document.getElementById("prj_cas").value;
				var inp_prj_cas = document.createElement("input");
				inp_prj_cas.setAttribute("type", "hidden");
				inp_prj_cas.setAttribute("id", "prj_cas");
				inp_prj_cas.setAttribute("name", "prj_cas");
				inp_prj_cas.setAttribute("value", prj_cas);
				
				var prj_dsc = document.getElementById("prj_dsc").value;
				var inp_prj_dsc = document.createElement("input");
				inp_prj_dsc.setAttribute("type", "hidden");
				inp_prj_dsc.setAttribute("id", "prj_dsc");
				inp_prj_dsc.setAttribute("name", "prj_dsc");
				inp_prj_dsc.setAttribute("value", prj_dsc);
				
				prj_form.appendChild(inp_prj_id);
				prj_form.appendChild(inp_img_fl);
				prj_form.appendChild(inp_prj_ttl);
				prj_form.appendChild(inp_prj_nam);
				prj_form.appendChild(inp_prj_bgn);
				prj_form.appendChild(inp_prj_end);
				prj_form.appendChild(inp_prj_phs);
				prj_form.appendChild(inp_prj_int);
				prj_form.appendChild(inp_prj_cas);
				prj_form.appendChild(inp_prj_dsc);
				
				prj_form.setAttribute("action", "update_project.php");
				prj_form.setAttribute("method", "post");
				prj_form.submit();
				
				return false;
			}
			
			function deleteReport(prj_id, rep_id) {
				var diag_del_rep = confirm("この報告書を削除しますか？");
				if (diag_del_rep === true) {
					
					var rep_form = document.createElement("form");
					document.body.appendChild(rep_form);
					
					var inp_prj_id = document.createElement("input");
					inp_prj_id.setAttribute("type", "hidden");
					inp_prj_id.setAttribute("id", "prj_id");
					inp_prj_id.setAttribute("name", "prj_id");
					inp_prj_id.setAttribute("value", prj_id);
					
					var inp_rep_id = document.createElement("input");
					inp_rep_id.setAttribute("type", "hidden");
					inp_rep_id.setAttribute("id", "rep_id");
					inp_rep_id.setAttribute("name", "rep_id");
					inp_rep_id.setAttribute("value", rep_id);
					
					rep_form.appendChild(inp_prj_id);
					rep_form.appendChild(inp_rep_id);
					
					rep_form.setAttribute("action", "delete_report.php");
					rep_form.setAttribute("method", "post");
					rep_form.submit();
					
					return false;
				}
				return false;
			}
			
			function backToMyPage() {
				window.location.href = "main.php";
				return false;
			}
		</script>
	</body>
</html>