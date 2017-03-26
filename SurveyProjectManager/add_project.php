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
	$prj_id = $_REQUEST['prj_id'];
	
	// Generate unique ID for saving temporal files.
	$tmp_nam = uniqid($_SESSION["USERNAME"]."_");
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
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
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
								<h2>プロジェクトの追加</h2>
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
											onclick='addNewProject("<?php echo $tmp_nam; ?>");'>
										<span class="glyphicon glyphicon-plus" aria-hidden="true"> 新規登録</span>
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
											   onclick='refreshAvatar(id="<?php echo $tmp_nam;?>",h=300,w="",target="consolidation");'
											   />
									</div>
								</form>
							</td>
							<td id="td_prj_inf" style='vertical-align: top;'>
								<div id="div_prj_bsc_inf" name="div_con_inf">
									<div id="div_prj_ttl" class="input-group">
										<span class="input-group-addon" style="width: 120px">課題名:</span>
										<input id="prj_ttl"
											   name="prj_ttl"
											   class="form-control"
											   type="text"
											   style="width: 454px;"
											   />
									</div>
									<div id="div_prj_nam" class="input-group">
										<span class="input-group-addon" style="width: 120px">プロジェクト名:</span>
										<input id="prj_nam"
											   name="prj_nam"
											   class="form-control"
											   type="text"
											   style="width: 454px;"
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
													   onclick="newDate('date_from');"/>
											</div>
											<script type="text/javascript">newDate("prj_bgn");</script>
											
											<div id="div_grp_prj_end" class="input-group">
												<span class="input-group-addon" style="width: 100px">終了年月日</span>
												<input id="prj_end"
													   name="prj_end"
													   class="form-control"
													   type="text"
													   style="width: 354px" 
													   onclick="newDate('date_to');"/>
											</div>
											<script type="text/javascript">newDate("prj_end");</script>
											
											<div id="div_grp_con_end" class="input-group">
												<span class="input-group-addon" style="width: 100px">調査次数</span>
												<select id="prj_phs" 
														name="prj_phs"
														class="combobox input-large form-control" 
														style="text-align: center; width: 354px">
													<option value="1">1</option>
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
													 rows="10"></textarea>
								</div>
								<div class="input-group">
									<span class="input-group-addon" style="width: 120px">調査原因:</span>
									<textarea id="prj_cas"
											  name="prj_cas"
											  class="form-control"
											  style="resize: none;
													 width: 1000px;
													 text-align: left"
													 rows="10"></textarea>
								</div>
								<div class="input-group">
									<span class="input-group-addon" style="width: 120px">特記事項:</span>
									<textarea id="prj_dsc"
											  name="prj_dsc"
											  class="form-control"
											  style="resize: none;
													 width: 1000px;
													 text-align: left"
													 rows="10"></textarea>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function addNewProject(tmp_nam){
				var prj_form = document.createElement("form");
				document.body.appendChild(prj_form);
				
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
				
				prj_form.appendChild(inp_img_fl);
				prj_form.appendChild(inp_prj_ttl);
				prj_form.appendChild(inp_prj_nam);
				prj_form.appendChild(inp_prj_bgn);
				prj_form.appendChild(inp_prj_end);
				prj_form.appendChild(inp_prj_phs);
				prj_form.appendChild(inp_prj_int);
				prj_form.appendChild(inp_prj_cas);
				prj_form.appendChild(inp_prj_dsc);
				
				prj_form.setAttribute("action", "insert_project.php");
				prj_form.setAttribute("method", "post");
				prj_form.submit();
				
				return false;
			}
			
			function backToMyPage() {
				window.location.href = "main.php";
				return false;
			}
		</script>
	</body>
</html>