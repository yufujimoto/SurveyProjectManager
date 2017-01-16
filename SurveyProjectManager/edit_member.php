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
	require "lib/guid.php";
    require "lib/config.php";
	
	// Get parameters from post.
	$err = $_REQUEST['err'];
	$mem_id = $_REQUEST['mem_id'];
	
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
						B.phone AS org_phn
					FROM member As A LEFT JOIN organization AS B ON A.org_id = B.uuid
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
		$mem_phn = $mem_row['org_phn'];
    }
	
	// close the connection to DB.
	pg_close($conn);
	
	// Translate the role name from English to Japanese.
	if ($mem_uty=="Administrator"){ $mem_uty = "管理者";}
	elseif ($mem_uty=="Standard"){ $mem_uty = "標準";}
	elseif ($mem_uty=="Part Time"){ $mem_uty = "アルバイト";}
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
			<div id="main" class="row">
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
			
			<!-- Registration Form -->
			<!-- Avatar -->
			<div class="row">
				<div id="tab_bsc" class="tab-pane fade in active">
					<h4><span class="glyphicon glyphicon-user" aria-hidden="true"> アカウント情報</span></h4>
					<table style="border: hidden; vertical-align: top">
						<!-- iFrame for showing Avatar -->
						<tr>
							<td style="width: 500px; text-align: center" rowspan="8">
								<iframe id="iframe_avatar"
										name="iframe_avatar"
										style="width: 150px;
										height: 150px;
										border:
										hidden;
										border-color: #999999;"
										src="avatar_uploaded.php?target=member&height=150&width=150&img_id=<?php echo $mem_id; ?>">
								</iframe>
								<form id="form_avatar" class="form-inline" style="text-align: left" method="post" enctype="multipart/form-data">
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
												   style="width: 200px"
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
											   onclick='refreshAvatar(id="<?php echo $tmp_nam;?>",h="150",w="150",target="menber");'/>
									</div>
								</form>
							</td>
						</tr>
						<tr>
							<td>
								<div id="div_grp_con_end" class="input-group" >
									<span class="input-group-addon" style="width: 100px">アカウント</span>
									<input id="mem_unm"
										   name="mem_unm"
										   class="form-control"
										   type="text"
										   style="width: 400px"
										   value="<?php echo $mem_unm;?>"/>
									<input id="mem_pwd"
										   name="mem_pwd"
										   class="form-control"
										   type="password"
										   readonly="true"
										   style="width: 400px"
										   value="<?php echo $mem_pwd; ?>"
										   />
									<select id="mem_typ"
											name="mem_typ"
											class="combobox input-large form-control"
											style="text-align: center; width: 400px">
										<option value="<?php echo $mem_uty;?>"><?php echo $mem_uty;?></option>
										<option value="Administrator">管理者</option>
										<option value="Standard">標準</option>
										<option value="Part Time">アルバイト</option>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div id="div_grp_con_end" class="input-group">
									<span class="input-group-addon" style="width: 100px">氏　名</span>
									<form class="form-inline">
											<div class="form-group">
												<input id="mem_snm"
													   name="mem_snm"
													   class="form-control"
													   type="text"
													   style="width: 100px"
													   value="<?php echo $mem_snm;?>"/>
											</div>
											<div class="form-group">
												<input id="mem_fnm"
													   name="mem_fnm"
													   class="form-control"
													   type="text"
													   readonly="true"
													   style="width: 100px"
													   value="<?php echo $mem_fnm; ?>"
													   />
											</div>
											<div class="form-group">
												<select class="combobox input-large form-control"
														name="mem_apt"
														style='text-align: center; width: 192px'>
													<option value="<?php echo $mem_apt;?>"><?php echo $mem_apt;?></option>
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
									</form>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div id="div_grp_con_end" class="input-group">
									<span class="input-group-addon" style="width: 100px">生年月日</span>
									<form class="form-inline">
										<div class="form-group">
											<select id="mem_bdy_y"
													name="mem_bdy_y"
													class="combobox input-large form-control"
													style='text-align: center; width: 132px'>
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
													class="combobox input-large form-control"
													style='text-align: center; width: 130px'>
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
													class="combobox input-large form-control"
													style='text-align: center; width: 130px'>
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
							</td>
						</tr>
						<tr>
							<td>
								<div id="div_grp_con_end" class="input-group">
									<span class="input-group-addon" style="width: 100px">住　所</span>
									<input class="form-control"
										   type='text'
										   name="mem_zip"
										   placeholder="郵便番号"
										   style="width: 400px"
										   value="<?php echo $mem_zip;?>"/>
									<input id="mem_adm"
										   name="mem_adm"
										   class="form-control"
										   type='text'
										   placeholder="都道府県"
										   style="width: 400px"
										   value="<?php echo $mem_adm;?>"/>
									<input id="mem_cty"
										   name="mem_cty"
										   class="form-control"
										   type='text'
										   placeholder="市町村"
										   style="width: 400px"
										   value="<?php echo $mem_cty;?>"/>
									<input id="mem_add"
										   name="mem_add"
										   class="form-control"
										   type='text'
										   placeholder="住所"
										   style="width: 400px"
										   value="<?php echo $mem_add;?>"/>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div id="div_grp_mem_phn" class="input-group">
									<span class="input-group-addon" style="width: 100px">電話番号</span>
									<input id="mem_phn"
										   name="mem_phn"
										   class="form-control"
										   type='text'
										   placeholder="電話番号"
										   style="width: 400px"
										   value="<?php echo $mem_phn;?>"/>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div id="div_grp_mem_mph" class="input-group">
									<span class="input-group-addon" style="width: 100px">携帯電話</span>
									<input id="mem_mph"
										   name="mem_mph"
										   class="form-control"
										   type='text'
										   placeholder="携帯電話"
										   style="width: 400px"
										   value="<?php echo $mem_mbl;?>"/>
								</div>
						<tr>
							<td>
								<div id="div_grp_mem_eml" class="input-group">
									<span class="input-group-addon" style="width: 100px">PCメール</span>
									<input id="mem_eml"
										   name="mem_eml"
										   class="form-control"
										   type='email'
										   placeholder="example@mail.com"
										   style="width: 400px"
										   value="<?php echo $mem_eml;?>"/>
								</div>
							</td>
						</tr>
					</table>
					
					<h4><span class="glyphicon glyphicon-star" aria-hidden="true"> ユーザープロファイル</span></h4>
				</div>
				
			<!-- Member Profile -->
			<div class="row">
				<!-- Affiliation -->
				<table class='table table'>
					<tr style="background-color:#343399; color:#ffffff;">
						<td colspan="12">
							<span class="glyphicon glyphicon-home" aria-hidden="true"> 所属に関する情報</span>
						</td>
					</tr>
					<tr>
						<td style='text-align: center; vertical-align: middle'>所　　属</td>
						<td colspan="4">
							<input id="org_nam"
								   name="org_nam"
								   class="form-control"
								   type='text'
								   placeholder="組織名"
								   value="<?php echo $org_nam; ?>"/>
						</td>
						<td colspan="4">
							<input id="org_sec"
								   name="org_sec"
								   class="form-control"
								   type='text'
								   placeholder="部署名"
								   value="<?php echo $org_sec; ?>"/>
						</td>
						<td style='text-align: center; vertical-align: middle'>連絡先</td>
						<td colspan="3">
							<input id="org_phn"
								   name="org_phn"
								   class="form-control"
								   type='text'
								   placeholder="電話番号"
								   value="<?php echo $org_phn; ?>"/>
						</td>
					</tr>
					<tr>
						<td rowspan="2" style='text-align: center; vertical-align: middle'>住　　所</td>
						<td colspan="3">
							<input id="org_zip"
								   name="org_zip"
								   class="form-control"
								   type='text'
								   placeholder="郵便番号"
								   value="<?php echo $org_zip; ?>"/>
						</td>
						<td colspan="4">
							<input id="org_adm"
								   name="org_adm"
								   class="form-control"
								   type='text'
								   placeholder="都道府県"
								   value="<?php echo $org_adm; ?>"/>
						</td>
						<td colspan="4">
							<input id="org_cty"
								   name="org_cty"
								   class="form-control"
								   type='text'
								   placeholder="市町村名"
								   value="<?php echo $org_cty; ?>"/>
						</td>
					</tr>
					<tr>
						<td colspan=11>
							<input id="org_add"
								   name="org_add"
								   class="form-control"
								   type='text'
								   placeholder="住所"
								   value="<?php echo $org_add; ?>"/>
						</td>
					</tr>
					<tr>
						<td colspan=12 style="text-align: right">
							<button class="btn btn-md btn-success" type="submit" value="registeration">
								<span class="glyphicon glyphicon-plus" aria-hidden="true"> このユーザー追加する</span>
							</button>
						</td>
					</tr>
				</table>
				<input type="hidden" name="mem_avt" value="<?php echo $mem_uid;?>.jpg">
			</form>
		</div></div>
	</body>
</html>