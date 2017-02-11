<?php
    // Start the session.
	session_cache_limiter("private_no_expire");
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
	require_once "lib/guid.php";
    require_once "lib/config.php";
	require_once "lib/password.php";
	
	// Get parameters from post.
	$err = $_REQUEST['err'];
	$mem_id = $_REQUEST['mem_id'];
	$prj_id = $_REQUEST['prj_id'];
	
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
	
	// Find the current member.
	$sql_sel_mem = "SELECT 
						A.avatar AS mem_ava,
						A.surname AS mem_snm,
						A.firstname AS mem_fnm,
						A.birthday AS mem_brt,
						A.administrativearea AS mem_adm,
						A.city AS mem_cty,
						A.contact_address AS mem_add,
						A.zipcode AS mem_zip,
						A.email AS mem_eml,
						A.phone AS mem_phn,
						A.mobile_phone AS mem_mbl,
						A.apointment AS mem_apt,
						A.username AS mem_unm,
						A.password AS mem_pwd,
						A.usertype AS mem_uty,
						B.uuid AS org_id,
						B.name AS org_nam,
						B.section AS org_sec,
						B.administrativearea AS org_adm,
						B.city AS org_cty,
						B.contact_address AS org_add,
						B.zipcode AS org_zip,
						B.phone AS org_phn,
						C.beginning AS rol_bgn,
						C.ending AS rol_end,
						C.rolename AS rol_nam,
						C.biography AS rol_bio
					FROM (
							(member AS A LEFT JOIN organization AS B ON A.org_id = B.uuid)
							LEFT JOIN role AS C ON A.uuid=mem_id AND C.prj_id='".$prj_id."')
					WHERE A.uuid = '" . $mem_id . "'";

    $sql_res_mem = pg_query($conn, $sql_sel_mem);
	if (!$sql_res_mem) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
    while ($mem_row = pg_fetch_assoc($sql_res_mem)) {
		$mem_uid = $mem_row['mem_id'];
        $mem_img = $mem_row['mem_ava'];
        $mem_snm = $mem_row['mem_snm'];
		$mem_fnm = $mem_row['mem_fnm'];
		$mem_brt = explode("-", $mem_row['mem_brt']);
		$mem_adm = $mem_row['mem_adm'];
		$mem_cty = $mem_row['mem_cty'];
		$mem_add = $mem_row['mem_add'];
		$mem_zip = $mem_row['mem_zip'];
		$mem_eml = $mem_row['mem_eml'];
		$mem_phn = $mem_row['mem_phn'];
		$mem_mbl = $mem_row['mem_mbl'];
		$mem_apt = $mem_row['mem_apt'];
		$mem_unm = $mem_row['mem_unm'];
		$mem_pwd = $mem_row['mem_pwd'];
		$mem_uty = $mem_row['mem_uty'];
		$org_id =  $mem_row['org_id'];
		$org_nam = $mem_row['org_nam'];
		$org_sec = $mem_row['org_sec'];
		$org_adm = $mem_row['org_adm'];
		$org_cty = $mem_row['org_cty'];
		$org_add = $mem_row['org_add'];
		$org_zip = $mem_row['org_zip'];
		$org_phn = $mem_row['org_phn'];
		$rol_bgn = $mem_row['rol_bgn'];
		$rol_end = $mem_row['rol_end'];
		$rol_nam = $mem_row['rol_nam'];
		$rol_bio = $mem_row['rol_bio'];
    }
	// close the connection to DB.
	pg_close($conn);
	
	// Translate the role name from English to Japanese.
	if ($mem_uty =="Administrator"){ $mem_uty_trs = "管理者";}
	elseif ($mem_uty =="Standard"){ $mem_uty_trs = "標準";}
	elseif ($mem_uty =="Part Time"){ $mem_uty_trs = "アルバイト";}
	
	if ($mem_apt =="Professor"){ $mem_apt_trs = "教授";}
	elseif ($mem_apt =="Assistant Professor"){ $mem_apt_trs = "准教授";}
	elseif ($mem_apt =="Lecturer"){ $mem_apt_trs = "講師";}
	elseif ($mem_apt =="Researcher"){ $mem_apt_trs = "研究者";}
	elseif ($mem_apt =="Curator"){ $mem_apt_trs = "学芸員";}
	elseif ($mem_apt =="Engineer"){ $mem_apt_trs = "技術者";}
	elseif ($mem_apt =="Teacher"){ $mem_apt_trs = "教師";}
	elseif ($mem_apt =="Student"){ $mem_apt_trs = "学生";}
	elseif ($mem_apt =="Administrative Director"){ $mem_apt_trs = "理事長";}
	elseif ($mem_apt =="Director"){ $mem_apt_trs = "代表";}
	elseif ($mem_apt =="President"){ $mem_apt_trs = "学長・館長";}
	elseif ($mem_apt =="Division Chief"){ $mem_apt = "部長";}
	elseif ($mem_apt =="Section Chief"){ $mem_apt_trs = "課長";}
	elseif ($mem_apt =="Unit Chief"){ $mem_apt_trs = "係長";}
	elseif ($mem_apt =="Part-time Worker"){ $mem_apt_trs = "パート・アルバイト";}
	elseif ($mem_apt =="Other"){ $mem_apt_trs = "その他";}
	
	if ($rol_nam =="General Manager"){ $rol_nam_trs = "統括者";}
	elseif ($rol_nam =="Vice Manager"){ $rol_nam_trs = "副統括者";}
	elseif ($rol_nam =="Unit Chief"){ $rol_nam_trs = "部門管理者";}
	elseif ($rol_nam =="Analyst"){ $rol_nam_trs = "分析担当";}
	elseif ($rol_nam =="Researcher"){ $rol_nam_trs = "調査担当";}
	elseif ($rol_nam =="Information Manager"){ $rol_nam_trs = "情報管理担当";}
	elseif ($rol_nam =="Educator"){ $rol_nam_trs = "教育担当";}
	elseif ($rol_nam =="Clerk"){ $rol_nam_trs = "一般事務担当";}
	elseif ($rol_nam =="Part-time Worker"){ $rol_nam_trs = "パート・アルバイト";}
	elseif ($rol_nam =="Other"){ $rol_nam_trs = "その他";}
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
		
		<script type="text/javascript" src="lib/refreshImage.js"></script>
		
		<!-- Import external scripts for Bootstrap CSS -->
		<script src="lib/jquery-3.1.1/jquery.min.js"></script>
		
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/bootstrap.min.js"></script>
		
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
		<script>
			function doOnLoad(){
				refreshAvatar(id="<?php echo $mem_uid;?>",h=128,w=128,target="member");
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
			<div id="mem_prf" class="row">
				<table id="operation" class="table" style="padding: 0px; margin: 0px">
					<thead style="text-align: center">
						<!-- Main Label of CSV uploader -->
						<tr>
							<td>
								<h2>メンバーの編集</h2>
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
											onclick='updateMember("<?php echo $mem_id; ?>","<?php echo $tmp_nam; ?>");'>
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
			
			<!-- Registration Form -->
			<!-- Avatar -->
			
			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#tab_ind">個人情報</a></li>
				<li><a data-toggle="tab" href="#tab_org">所属情報</a></li>
				<li><a data-toggle="tab" href="#tab_prj">役割情報</a></li>
			</ul>
			
			<div class="tab-content">
				<div id="tab_ind" class="tab-pane fade in active">
					<h4><span class="glyphicon glyphicon-user" aria-hidden="true">アカウント情報</span></h4>
					<!-- iFrame for showing Avatar -->
					<div id="div_ind_prf" class="row" style="text-align: center; vertical-align: middle">
						<div id="div_ind_img" class="col-md-5">
							<div id="div_ifm">
								<iframe id="iframe_avatar"
										name="iframe_avatar"
										style="width: 150px;
										height: 150px;
										border:
										hidden;
										border-color: #999999;"
										src="avatar_uploaded.php?target=member&height=150&width=150&img_id=<?php echo $mem_id; ?>">
								</iframe>
							</div>
							<div id="div_upl" style="text-align: center">
								<form id="form_avatar" class="form-inline" style="text-align: left" method="post" enctype="multipart/form-data">
									<div class="form-group" style="text-align: center">
										<div class="input-group">
											<span class="input-group-btn">
												<span class="btn btn-primary btn-file">画像の参照&hellip;
													<input id="input_avatar"
														   name="avatar"　
														   type="file"
														   accept=".jpg,.JPG,.jpeg,.JPEG" />
												</span>
											</span>
											<input id="name_avatar"
												   name="name_avatar"
												   type="text"
												   class="form-control"
												   style="width: 100%"
												   readonly/>
										</div>
										<div class="form-group">
											<input id="btn-upload"
												   name="btn-upload"
												   class="btn btn-md btn-success"
												   type="submit"
												   style="width: 100%"
												   value="アップロード"
												   onclick='refreshAvatar(id="<?php echo $tmp_nam;?>",h="150",w="150",target="menber");'/>
										</div>
									</div>
								</form>
							</div>
						</div>
						
						<div  id="div_ind_inf" class="col-md-7">
							<div id="div_mem_unm" class="input-group" style="width: 100%">
								<span class="input-group-addon" style="width: 100px">アカウント</span>
								<input id="mem_unm"
									   name="mem_unm"
									   class="form-control"
									   type="text"
									   value="<?php echo $mem_unm;?>"/>
							</div>
							
							<div id="div_mem_typ" class="input-group" style="width: 100%">
								<span class="input-group-addon" style="width: 100px">管理権限</span>
								<select id="mem_typ"
										name="mem_typ"
										class="combobox input-large form-control"
										style="text-align: center;">
									<option value="<?php echo $mem_uty;?>"><?php echo $mem_uty_trs;?></option>
									<option value="Administrator">管理者</option>
									<option value="Standard">標準</option>
									<option value="Part Time">アルバイト</option>
								</select>
							</div>
							
							<div id="div_mem_snm" class="input-group" style="width: 100%">
								<span class="input-group-addon" style="width: 100px">氏</span>
								<input id="mem_snm"
									   name="mem_snm"
									   class="form-control"
									   type="text"
									   value="<?php echo $mem_snm;?>"/>
							</div>
							
							<div id="div_mem_fnm" class="input-group" style="width: 100%">
								<span class="input-group-addon" style="width: 100px">名</span>
								<input id="mem_fnm"
									   name="mem_fnm"
									   class="form-control"
									   type="text"
									   readonly="true"
									   value="<?php echo $mem_fnm; ?>"/>
							</div>
							
							<div id="div_mem_bdy" class="input-group" style="width: 100%; text-align: left">
								<span class="input-group-addon" style="width: 100px">生年月日</span>
								<form class="form-inline">
									<div class="form-group">
										<select id="mem_bdy_y"
												name="mem_bdy_y"
												class="combobox input-large form-control"/>
											<option value="<?php echo $mem_brt[0]; ?>"><?php echo $mem_brt[0]; ?></option>
											<?php
												for ($i = 0; $i <= 100; $i++) {
													$year = 2015 - $i;
													echo "<option value='". $year ."'>" . $year . "</option>";
												}
											?>
										</select>
									</div>
									
									<div class="form-group">
										<select id="mem_bdy_m"
												name="mem_bdy_m"
												class="combobox input-large form-control"/>
											<option value="<?php echo $mem_brt[1]; ?>"><?php echo $mem_brt[1]; ?></option>
											<?php
												for ($i = 1; $i <= 12; $i++) {
													$month = str_pad($i, 2, "0", STR_PAD_LEFT);
													echo "<option value='". $i ."'>" . $month . "</option>";
												}
											?>
										</select>
									</div>
									<div class="form-group">
										<select id="mem_bdy_d"
												name="mem_bdy_d"
												class="combobox input-large form-control"/>
											<option value="<?php echo $mem_brt[2]; ?>"><?php echo $mem_brt[2]; ?></option>
											<?php
												for($i = 1; $i <= 31; $i++) {
													$day = str_pad($i, 2, "0", STR_PAD_LEFT);
													echo "<option value='". $day ."'>" . $day . "</option>";
												}
											?>
										</select>
									</div>
								</form>
							</div>
							
							<div id="div_mem_adr" class="input-group" style="width: 100%">
								<span class="input-group-addon" style="width: 100px">住　所</span>
								<div id="div_mem_zip" class="input-group" style="width: 100%">
									<span class="input-group-addon" style="width: 100px">郵便番号</span>
									<input id="mem_zip"
										   name="mem_zip" 
										   class="form-control"
										   type='text'
										   placeholder="郵便番号"
										   value="<?php echo $mem_zip;?>"/>
								</div>
								<div id="div_mem_adm" class="input-group" style="width: 100%">
									<span class="input-group-addon" style="width: 100px">都道府県</span>
									<input id="mem_adm"
										   name="mem_adm"
										   class="form-control"
										   type='text'
										   placeholder="都道府県"
										   value="<?php echo $mem_adm;?>"/>
								</div>
								<div id="div_mem_cty" class="input-group" style="width: 100%">
									<span class="input-group-addon" style="width: 100px">市町村</span>
									<input id="mem_cty"
										   name="mem_cty"
										   class="form-control"
										   type='text'
										   placeholder="市町村"
										   value="<?php echo $mem_cty;?>"/>
								</div>

								<div id="div_mem_add" class="input-group" style="width: 100%">
									<span class="input-group-addon" style="width: 100px">住　所</span>
									<input id="mem_add"
										   name="mem_add"
										   class="form-control"
										   type='text'
										   placeholder="住所"
										   value="<?php echo $mem_add;?>"/>
								</div>
							</div>
							
							<div id="div_mem_cnt" class="input-group" style="width: 100%">
								<span class="input-group-addon" style="width: 100px">連絡先</span>
								<div id="div_mem_phn" class="input-group" style="width: 100%">
									<span class="input-group-addon" style="width: 100px">電話番号</span>
									<input id="mem_phn"
										   name="mem_phn"
										   class="form-control"
										   type='text'
										   placeholder="電話番号"
										   value="<?php echo $mem_phn;?>"/>
								</div>
								<div id="div_mem_mph" class="input-group" style="width: 100%">
									<span class="input-group-addon" style="width: 100px">携帯電話</span>
									<input id="mem_mph"
										   name="mem_mph"
										   class="form-control"
										   type='text'
										   placeholder="携帯電話"
										   value="<?php echo $mem_mbl;?>"/>
								</div>
								<div id="div_grp_mem_eml" class="input-group" style="width: 100%">
									<span class="input-group-addon" style="width: 100px">PCメール</span>
									<input id="mem_eml"
										   name="mem_eml"
										   class="form-control"
										   type='email'
										   placeholder="example@mail.com"
										   value="<?php echo $mem_eml;?>"/>
								</div>
							</div>
						</div>
					</div>
				</div>
				
			<!-- Member Profile -->
			<div id="tab_org" class="tab-pane fade">
				<!-- Affiliation -->
				<h4><span class="glyphicon glyphicon-home" aria-hidden="true"> </span> 所属情報</h4>
				<div id="org_prf" class="row">
					<div id="div_org_nam" class="input-group" style="width: 100%">
						<span class="input-group-addon" style="width: 100px">所属組織</span>
						<input id="org_nam"
							   name="org_nam"
							   class="form-control"
							   type='text'
							   placeholder="組織名"
							   value="<?php echo $org_nam; ?>"/>
					</div>
					<div id="div_org_sec" class="input-group" style="width: 100%">
						<span class="input-group-addon" style="width: 100px">所属部門</span>
						<input id="org_sec"
							   name="org_sec"
							   class="form-control"
							   type='text'
							   placeholder="部署名"
							   value="<?php echo $org_sec; ?>"/>
					</div>
					<div id="div_org_apt" class="input-group" style="width: 100%">
						<span class="input-group-addon" style="width: 100px">役職</span>
						<select id="mem_apt" name="mem_apt" class="combobox input-large form-control">
							<option value="<?php echo $mem_apt;?>"><?php echo $mem_apt_trs;?></option>
							<option value="Professor">教授</option>
							<option value="Assistant Professor">准教授</option>
							<option value="Lecturer">講師</option>
							<option value="Researcher">研究者</option>
							<option value="curator">学芸員</option>
							<option value="Engineer">技術者</option>
							<option value="Teacher">教師</option>
							<option value="Student">学生</option>
							<option value="Administrative Director">理事長</option>
							<option value="Director">代表</option>
							<option value="President">学長・館長</option>
							<option value="Division Chief">部長</option>
							<option value="Section Chief">課長</option>
							<option value="Unit Chief">係長</option>
							<option value="Part-time Worker">パート・アルバイト</option>
							<option value="Other">その他</option>
						</select>
					</div>
					
					<div id="div_org_adr" class="input-group" style="width: 100%">
						<span class="input-group-addon" style="width: 100px">住　所</span>
						<div id="div_org_zip" class="input-group" style="width: 100%">
							<span class="input-group-addon" style="width: 100px">郵便番号</span>
							<input id="org_zip"
								   name="org_zip"
								   class="form-control"
								   type='text'
								   placeholder="郵便番号"
								   value="<?php echo $org_zip;?>"/>
						</div>
						<div id="div_org_adm" class="input-group" style="width: 100%">
							<span class="input-group-addon" style="width: 100px">都道府県</span>
							<input id="org_adm"
								   name="org_adm"
								   class="form-control"
								   type='text'
								   placeholder="都道府県"
								   value="<?php echo $org_adm;?>"/>
						</div>
						<div id="div_org_cty" class="input-group" style="width: 100%">
							<span class="input-group-addon" style="width: 100px">市町村</span>
							<input id="org_cty"
								   name="org_cty"
								   class="form-control"
								   type='text'
								   placeholder="市町村"
								   value="<?php echo $org_cty;?>"/>
						</div>
						<div id="div_org_add" class="input-group" style="width: 100%">
							<span class="input-group-addon" style="width: 100px">住所</span>
							<input id="org_add"
								   name="org_add"
								   class="form-control"
								   type='text'
								   placeholder="住所"
								   value="<?php echo $org_add;?>"/>
						</div>
					</div>
					<div id="div_org_cnt" class="input-group" style="width: 100%">
						<span class="input-group-addon" style="width: 100px">連絡先</span>
						<div id="div_org_phn" class="input-group" style="width: 100%">
							<span class="input-group-addon" style="width: 100px">電話番号</span>
							<input id="org_phn"
								   name="org_phn"
								   class="form-control"
								   type='text'
								   placeholder="電話番号"
								   value="<?php echo $org_phn; ?>"/>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Project Role Profile -->
			<div id="tab_prj" class="tab-pane fade">
				<!-- Affiliation -->
				<h4><span class="glyphicon glyphicon-home" aria-hidden="true"> </span> 役割情報</h4>
				<div id="prj_prf" class="row">
					<div id="div_rol_nam" class="input-group" style="width: 100%">
						<span class="input-group-addon" style="width: 100px">役割</span>
						<select id="rol_nam" name="rol_nam" class="combobox input-large form-control">
							<option value="<?php echo $rol_nam;?>"><?php echo $rol_nam_trs;?></option>
							<option value="General Manager">統括者</option>
							<option value="Vice Manager">副統括者</option>
							<option value="Unit Chief">部門管理者</option>
							<option value="Analyst">分析担当</option>
							<option value="Researcher">調査担当</option>
							<option value="Information Manager">情報管理担当</option>
							<option value="Educator">教育担当</option>
							<option value="Clerk">一般事務担当</option>
							<option value="Part-time Worker">パート・アルバイト</option>
							<option value="Other">その他</option>
						</select>
					</div>
					
					<div id="div_rol_prd" class="input-group" style="width: 100%">
						<span class="input-group-addon" style="width: 100px">従事期間</span>
						<div id="div_rol_bgn" class="input-group" style="width: 100%">
							<span class="input-group-addon" style="width: 100px">開始</span>
							<input id="rol_bgn"
								   name="rol_bgn"
								   class="form-control"
								   type='text'
								   placeholder="開始時期"
								   value="<?php echo $rol_bgn;?>"
								   onclick="newDate('date_from');"/>
						</div>
						<script type="text/javascript">newDate("rol_bgn");</script>
						
						<div id="div_rol_end" class="input-group" style="width: 100%">
							<span class="input-group-addon" style="width: 100px">終了</span>
							<input id="rol_end"
								   name="rol_end"
								   class="form-control"
								   type='text'
								   placeholder="終了時期"
								   value="<?php echo $rol_end;?>"
								   onclick="newDate('date_to');"/>
						</div>
						<script type="text/javascript">newDate("rol_end");</script>
						
					</div>
					<div class="input-group" style="width: 100%">
						<span class="input-group-addon" style="width: 100px">略歴</span>
						<textarea id="rol_bio"
								  name="rol_bio"
								  class="form-control"
								  style="resize: none;
										 width: 100%;
										 text-align: left"
										 rows="10"><?php echo str_replace("<br />","",$rol_bio); ?></textarea>
					</div>
				</div>
			</div>
		</div>
		<script language="JavaScript" type="text/javascript">
			function backToMyPage() {
				window.location.href = "main.php";
				return false;
			}
			
			function updateMember(mem_id, tmp_nam){
				var mem_form = document.createElement("form");
				
				var inp_mem_id = document.createElement("input");
				inp_mem_id.setAttribute("type", "hidden");
				inp_mem_id.setAttribute("id", "mem_id");
				inp_mem_id.setAttribute("name", "mem_id");
				inp_mem_id.setAttribute("value", mem_id);
				
				var inp_img_fl = document.createElement("input");
				inp_img_fl.setAttribute("type", "hidden");
				inp_img_fl.setAttribute("id", "img_fl");
				inp_img_fl.setAttribute("name", "img_fl");
				inp_img_fl.setAttribute("value", tmp_nam +".jpg");
				
				var mem_unm = document.getElementById("mem_unm").value;
				var inp_mem_unm = document.createElement("input");
				inp_mem_unm.setAttribute("type", "hidden");
				inp_mem_unm.setAttribute("id", "mem_unm");
				inp_mem_unm.setAttribute("name", "mem_unm");
				inp_mem_unm.setAttribute("value", mem_unm);
				
				var mem_typ_idx = document.getElementById("mem_typ").selectedIndex;
				var mem_typ = document.getElementById("mem_typ").options[mem_typ_idx].value;
				var inp_mem_typ = document.createElement("input");
				inp_mem_typ.setAttribute("type", "hidden");
				inp_mem_typ.setAttribute("id", "mem_typ");
				inp_mem_typ.setAttribute("name", "mem_typ");
				inp_mem_typ.setAttribute("value", mem_typ);
				
				var mem_snm = document.getElementById("mem_snm").value;
				var inp_mem_snm = document.createElement("input");
				inp_mem_snm.setAttribute("type", "hidden");
				inp_mem_snm.setAttribute("id", "mem_snm");
				inp_mem_snm.setAttribute("name", "mem_snm");
				inp_mem_snm.setAttribute("value", mem_snm);
				
				var mem_fnm = document.getElementById("mem_fnm").value;
				var inp_mem_fnm = document.createElement("input");
				inp_mem_fnm.setAttribute("type", "hidden");
				inp_mem_fnm.setAttribute("id", "mem_fnm");
				inp_mem_fnm.setAttribute("name", "mem_fnm");
				inp_mem_fnm.setAttribute("value", mem_fnm);
				
				var mem_bdy_y = document.getElementById("mem_bdy_y").value;
				var mem_bdy_m = document.getElementById("mem_bdy_m").value;
				var mem_bdy_d = document.getElementById("mem_bdy_d").value;
				var mem_bdy = mem_bdy_y + "-" + mem_bdy_m + "-" + mem_bdy_d;
				var inp_mem_bdy = document.createElement("input");
				inp_mem_bdy.setAttribute("type", "hidden");
				inp_mem_bdy.setAttribute("id", "mem_bdy");
				inp_mem_bdy.setAttribute("name", "mem_bdy");
				inp_mem_bdy.setAttribute("value", mem_bdy);
				
				var mem_zip = document.getElementById("mem_zip").value;
				var inp_mem_zip = document.createElement("input");
				inp_mem_zip.setAttribute("type", "hidden");
				inp_mem_zip.setAttribute("id", "mem_zip");
				inp_mem_zip.setAttribute("name", "mem_zip");
				inp_mem_zip.setAttribute("value", mem_zip);
				
				var mem_adm = document.getElementById("mem_adm").value;
				var inp_mem_adm = document.createElement("input");
				inp_mem_adm.setAttribute("type", "hidden");
				inp_mem_adm.setAttribute("id", "mem_adm");
				inp_mem_adm.setAttribute("name", "mem_adm");
				inp_mem_adm.setAttribute("value", mem_adm);
				
				var mem_cty = document.getElementById("mem_cty").value;
				var inp_mem_cty = document.createElement("input");
				inp_mem_cty.setAttribute("type", "hidden");
				inp_mem_cty.setAttribute("id", "mem_cty");
				inp_mem_cty.setAttribute("name", "mem_cty");
				inp_mem_cty.setAttribute("value", mem_cty);
				
				var mem_add = document.getElementById("mem_add").value;
				var inp_mem_add = document.createElement("input");
				inp_mem_add.setAttribute("type", "hidden");
				inp_mem_add.setAttribute("id", "mem_add");
				inp_mem_add.setAttribute("name", "mem_add");
				inp_mem_add.setAttribute("value", mem_add);
				
				var mem_phn = document.getElementById("mem_phn").value;
				var inp_mem_phn = document.createElement("input");
				inp_mem_phn.setAttribute("type", "hidden");
				inp_mem_phn.setAttribute("id", "mem_phn");
				inp_mem_phn.setAttribute("name", "mem_phn");
				inp_mem_phn.setAttribute("value", mem_phn);
				
				var mem_mph = document.getElementById("mem_mph").value;
				var inp_mem_mph = document.createElement("input");
				inp_mem_mph.setAttribute("type", "hidden");
				inp_mem_mph.setAttribute("id", "mem_mph");
				inp_mem_mph.setAttribute("name", "mem_mph");
				inp_mem_mph.setAttribute("value", mem_mph);
				
				var mem_eml = document.getElementById("mem_eml").value;
				var inp_mem_eml = document.createElement("input");
				inp_mem_eml.setAttribute("type", "hidden");
				inp_mem_eml.setAttribute("id", "mem_eml");
				inp_mem_eml.setAttribute("name", "mem_eml");
				inp_mem_eml.setAttribute("value", mem_eml);
				
				var org_nam = document.getElementById("org_nam").value;
				var inp_org_nam = document.createElement("input");
				inp_org_nam.setAttribute("type", "hidden");
				inp_org_nam.setAttribute("id", "org_nam");
				inp_org_nam.setAttribute("name", "org_nam");
				inp_org_nam.setAttribute("value", org_nam);
				
				var org_sec = document.getElementById("org_sec").value;
				var inp_org_sec = document.createElement("input");
				inp_org_sec.setAttribute("type", "hidden");
				inp_org_sec.setAttribute("id", "org_sec");
				inp_org_sec.setAttribute("name", "org_sec");
				inp_org_sec.setAttribute("value", org_sec);
				
				var mem_apt = document.getElementById("mem_apt").value;
				var inp_mem_apt = document.createElement("input");
				inp_mem_apt.setAttribute("type", "hidden");
				inp_mem_apt.setAttribute("id", "mem_apt");
				inp_mem_apt.setAttribute("name", "mem_apt");
				inp_mem_apt.setAttribute("value", mem_apt);
				
				var org_zip = document.getElementById("org_zip").value;
				var inp_org_zip = document.createElement("input");
				inp_org_zip.setAttribute("type", "hidden");
				inp_org_zip.setAttribute("id", "org_zip");
				inp_org_zip.setAttribute("name", "org_zip");
				inp_org_zip.setAttribute("value", org_zip);
				
				var org_adm = document.getElementById("org_adm").value;
				var inp_org_adm = document.createElement("input");
				inp_org_adm.setAttribute("type", "hidden");
				inp_org_adm.setAttribute("id", "org_adm");
				inp_org_adm.setAttribute("name", "org_adm");
				inp_org_adm.setAttribute("value", org_adm);
				
				var org_cty = document.getElementById("org_cty").value;
				var inp_org_cty = document.createElement("input");
				inp_org_cty.setAttribute("type", "hidden");
				inp_org_cty.setAttribute("id", "org_cty");
				inp_org_cty.setAttribute("name", "org_cty");
				inp_org_cty.setAttribute("value", org_cty);
				
				var org_add = document.getElementById("org_add").value;
				var inp_org_add = document.createElement("input");
				inp_org_add.setAttribute("type", "hidden");
				inp_org_add.setAttribute("id", "org_add");
				inp_org_add.setAttribute("name", "org_add");
				inp_org_add.setAttribute("value", org_add);
				
				var org_phn = document.getElementById("org_phn").value;
				var inp_org_phn = document.createElement("input");
				inp_org_phn.setAttribute("type", "hidden");
				inp_org_phn.setAttribute("id", "org_phn");
				inp_org_phn.setAttribute("name", "org_phn");
				inp_org_phn.setAttribute("value", org_phn);
				
				mem_form.appendChild(inp_mem_id);
				mem_form.appendChild(inp_img_fl);
				mem_form.appendChild(inp_mem_unm);
				mem_form.appendChild(inp_mem_typ);
				mem_form.appendChild(inp_mem_snm);
				mem_form.appendChild(inp_mem_fnm);
				mem_form.appendChild(inp_mem_bdy);
				mem_form.appendChild(inp_mem_zip);
				mem_form.appendChild(inp_mem_adm);
				mem_form.appendChild(inp_mem_cty);
				mem_form.appendChild(inp_mem_add);
				mem_form.appendChild(inp_mem_phn);
				mem_form.appendChild(inp_mem_mph);
				mem_form.appendChild(inp_mem_eml);
				mem_form.appendChild(inp_org_nam);
				mem_form.appendChild(inp_org_sec);
				mem_form.appendChild(inp_mem_apt);
				mem_form.appendChild(inp_org_zip);
				mem_form.appendChild(inp_org_adm);
				mem_form.appendChild(inp_org_cty);
				mem_form.appendChild(inp_org_add);
				mem_form.appendChild(inp_org_phn);
				
				mem_form.setAttribute("action", "update_project_member.php");
				mem_form.setAttribute("method", "post");
				mem_form.submit();
				
				return false;
			}
		</script>
		
	</body>
</html>