<?php
	header("Content-Type: text/html; charset=UTF-8");
	
	// Start the session.
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	if ($_SESSION["USERTYPE"] != "Administrator") {
		header("Location: main.php");
	}
	
	// Load external libraries.
	require 'lib/guid.php';
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
		<title>Consolidation</title>
		
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
		
		<!-- Import external scripts for Bootstrap CSS -->
		<script src="lib/jquery-3.1.1/jquery.min.js"></script>
		\n
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/bootstrap.min.js"></script>
		
		<!-- Import external scripts for generating image -->
		<script type="text/javascript" src="lib/refreshImage.js"></script>
		
		<!-- Import external scripts for calendar control -->
		<link rel="stylesheet" type="text/css" href="lib/calendar/codebase/dhtmlxcalendar.css"/>
		<script src="lib/calendar/codebase/dhtmlxcalendar.js"></script>
		<script type="text/javascript" src="lib/calendar.js"></script>
		
		<!-- Extension of the Bootstrap CSS for file uploads -->
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
		<script>
			$(document).on("change", ".btn-file :file", function() {
				var input = $(this),
				numFiles = input.get(0).files ? input.get(0).files.length : 1,
				label = input.val().replace(/\\/g, "/").replace(/.*\//, "");
				input.trigger("fileselect", [numFiles, label]);
			});
				
			$(document).ready( function() {
				$(".btn-file :file").on("fileselect", function(event, numFiles, label) {
					var input = $(this).parents(".input-group").find(":text"),
					log = numFiles > 1 ? numFiles + " files selected" : label;
					
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
								<h2>統合体登録フォーム</h2>
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
							<td colspan=7 style="text-align: left">
								<div class="btn-group">
									<button id="btn_add_mat"
											name="btn_add_mat"
											class="btn btn-sm btn-success"
											type="submit" value="add_material"
											onclick='addNewConsolidation("<?php echo $prj_id; ?>","<?php echo $tmp_nam; ?>");'>
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
			
			<!-- Avatar -->
			<div class="row">
				<table class="table table" style="border: hidden">
					<!-- iFrame for showing Avatar -->
					<tr style="text-align: left">
						<td>
							<h4>表紙画像</h4>
							<iframe id="iframe_avatar"
									name="iframe_avatar"
									style="width: 510px;
									height: 300px;
									border:
									hidden;
									border-color: #999999;"
									src="avatar_uploaded.php?target=consolidation&hight=400&width=600">
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
						<td id="td_mat_inf" style='vertical-align: top;'>
							<div id="div_con_inf" name="div_con_inf">
								<h4>基本属性</h4>
								<div id="div_con_nam" class="input-group">
									<span class="input-group-addon" style="width: 100px">名　　称:</span>
									<input id="con_nam"
										   name="con_nam"
										   class="form-control"
										   type="text"
										   style="width: 454px"/>
								</div>
								<div id="div_con_bgn" class="input-group">
									<span class="input-group-addon" style="width: 100px">存在期間:</span>
									<div class="input-group-vertical">
										<div id="div_grp_con_bgn" class="input-group">
											<span class="input-group-addon" style="width: 50px">開始</span>
											<input id="con_bgn"
												   name="con_bgn"
												   class="form-control"
												   type="text"
												   style="width: 400px"/>
										</div>
										<div id="div_grp_con_end" class="input-group">
											<span class="input-group-addon" style="width: 50px">終了</span>
											<input id="con_end"
												   name="con_end"
												   class="form-control"
												   type="text"
												   style="width: 400px"/>
											</div>
									</div>
								</div>
								<div class="input-group">
									<span class="input-group-addon" style="width: 100px">説明記述:</span>
									<textarea id="con_dsc"
											  name="con_dsc"
											  class="form-control"
											  style="resize: none;
													 width: 454px;
													 text-align: left"
											  rows="10"></textarea>
								</div>
							</div>
						</td>
					</tr>
					<!-- Address or any explanation about the place -->
					<tr>
						<td>
							<div class="input-group">
									<span class="input-group-addon">所在地名:</span>
									<input id="con_add"
										   name="con_add"
										   class="form-control"
										   type="text" />
							</div>
						</td>
						<td>
							<div class="form-group col-lg-4">
								<div id="div_con_crd" class="input-group">
									<span class="input-group-addon" id="basic-addon1">緯度:</span>
									<input id="con_lat" name="con_lat" class="form-control" type="text" placeholder="DD.DDDDDD"/>
								</div>
							</div>
							<div class="form-group col-lg-4">
								<div class="input-group">
									<span class="input-group-addon" id="basic-addon1">経度:</span>
									<input id="con_lon" name="con_lon" class="form-control" type="text" placeholder="DDD.DDDDDD"/>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<h4>所在地の範囲</h4>
							<form id="frm_ext_geo_id"
								  class="form-inline"
								  method="post"
								  enctype="multipart/form-data">
								<div class="input-group">
									<div class="input-group">
										<span class="input-group-btn">
											<span class="btn btn-primary btn-file" style="width:110px">
												GeoJson&hellip;
												<input id="ipt_ext_geo_id"
													   name="upfile"　
													   type="file"
													   size="50"
													   accept=".geojson" />
											</span>
										</span>
										<input id="nam_ext_geo_id" 
											   name="nam_ext_geo"
											   class="form-control" 
											   type="text"
											   style="width:300px"
											   readonly value=""/>
									</div>
									<input id="btn-upload" 
											   name="btn-upload"
											   class="btn btn-md btn-success"
											   type="submit"
											   value="アップロード"
											   onclick='addGeographicExtent("<?php echo $prj_id; ?>","<?php echo $tmp_nam; ?>");'/>
								</div>
								<input type="hidden" name="max_file_size" value="10000000">
							</form>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<iframe style="border: 0; width: 100%; height: 200px" name="ifr_ext_geo"></iframe>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<h4>想定範囲</h4>
							<form id="frm_est_geo_id"
								  class="form-inline"
								  method="post"
								  enctype="multipart/form-data">
								<div class="input-group">
									<div class="input-group">
										<span class="input-group-btn">
											<span class="btn btn-primary btn-file" style="width:110px">
												GeoJson&hellip;
												<input id="ipt_est_geo_id"
													   name="upfile"　
													   type="file"
													   size="50"
													   accept=".geojson" />
											</span>
										</span>
										<input id="nam_est_geo_id" 
											   name="nam_est_geo"
											   class="form-control" 
											   type="text"
											   style="width:300px"
											   readonly value=""/>
									</div>
									<input id="btn-upload" 
											   name="btn-upload"
											   class="btn btn-md btn-success"
											   type="submit"
											   value="アップロード"
											   onclick='addEstimatedExtent("<?php echo $prj_id; ?>","<?php echo $tmp_nam; ?>");'/>
								</div>
								<input type="hidden" name="max_file_size" value="10000000">
							</form>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<iframe style="border: 0; width: 100%; height: 200px" name="ifr_est_geo"></iframe>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</body>
	<script language="JavaScript" type="text/javascript">
		function addGeographicExtent(prj_id, tmp_nam){
			var ext_form = document.getElementById('frm_ext_geo_id');
			
			var inp_prj_id = document.createElement("input");
			inp_prj_id.setAttribute("type", "hidden");
			inp_prj_id.setAttribute("id", "prj_id");
			inp_prj_id.setAttribute("name", "prj_id");
			inp_prj_id.setAttribute("value", prj_id);
			
			var inp_tmp_nam = document.createElement("input");
			inp_tmp_nam.setAttribute("type", "hidden");
			inp_tmp_nam.setAttribute("id", "tmp_nam");
			inp_tmp_nam.setAttribute("name", "tmp_nam");
			inp_tmp_nam.setAttribute("value", tmp_nam + "_ext");
			
			ext_form.appendChild(inp_prj_id);
			ext_form.appendChild(inp_tmp_nam);
			
			ext_form.action = "parse_geojson.php";
			ext_form.target = "ifr_ext_geo";
		}
		
		function addEstimatedExtent(prj_id, tmp_nam){
			var est_form = document.getElementById('frm_est_geo_id');
			
			var inp_prj_id = document.createElement("input");
			inp_prj_id.setAttribute("type", "hidden");
			inp_prj_id.setAttribute("id", "prj_id");
			inp_prj_id.setAttribute("name", "prj_id");
			inp_prj_id.setAttribute("value", prj_id);
			
			var inp_tmp_nam = document.createElement("input");
			inp_tmp_nam.setAttribute("type", "hidden");
			inp_tmp_nam.setAttribute("id", "tmp_nam");
			inp_tmp_nam.setAttribute("name", "tmp_nam");
			inp_tmp_nam.setAttribute("value", tmp_nam + "_est");
			
			est_form.appendChild(inp_prj_id);
			est_form.appendChild(inp_tmp_nam);
			
			est_form.action = "parse_geojson.php";
			est_form.target = "ifr_est_geo";
		}
		
		function addNewConsolidation(prj_id, tmp_nam){
			var con_form = document.createElement("form");
			document.body.appendChild(con_form);
			
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
			
			var con_nam = document.getElementById("con_nam").value;
			var inp_con_nam = document.createElement("input");
			inp_con_nam.setAttribute("type", "hidden");
			inp_con_nam.setAttribute("id", "con_nam");
			inp_con_nam.setAttribute("name", "con_nam");
			inp_con_nam.setAttribute("value", con_nam);
			
			var con_bgn = document.getElementById("con_bgn").value;
			var inp_con_bgn = document.createElement("input");
			inp_con_bgn.setAttribute("type", "hidden");
			inp_con_bgn.setAttribute("id", "con_bgn");
			inp_con_bgn.setAttribute("name", "con_bgn");
			inp_con_bgn.setAttribute("value", con_bgn);
			
			var con_end = document.getElementById("con_end").value;
			var inp_con_end = document.createElement("input");
			inp_con_end.setAttribute("type", "hidden");
			inp_con_end.setAttribute("id", "con_end");
			inp_con_end.setAttribute("name", "con_end");
			inp_con_end.setAttribute("value", con_end);
			
			var con_dsc = document.getElementById("con_dsc").value;
			var inp_con_dsc = document.createElement("input");
			inp_con_dsc.setAttribute("type", "hidden");
			inp_con_dsc.setAttribute("id", "con_dsc");
			inp_con_dsc.setAttribute("name", "con_dsc");
			inp_con_dsc.setAttribute("value", con_dsc);
			
			var con_add = document.getElementById("con_add").value;
			var inp_con_add = document.createElement("input");
			inp_con_add.setAttribute("type", "hidden");
			inp_con_add.setAttribute("id", "con_add");
			inp_con_add.setAttribute("name", "con_add");
			inp_con_add.setAttribute("value", con_add);
			
			var con_lat = document.getElementById("con_lat").value;
			var inp_con_lat = document.createElement("input");
			inp_con_lat.setAttribute("type", "hidden");
			inp_con_lat.setAttribute("id", "con_lat");
			inp_con_lat.setAttribute("name", "con_lat");
			inp_con_lat.setAttribute("value", con_lat);
			
			var con_lon = document.getElementById("con_lon").value;
			var inp_con_lon = document.createElement("input");
			inp_con_lon.setAttribute("type", "hidden");
			inp_con_lon.setAttribute("id", "con_lon");
			inp_con_lon.setAttribute("name", "con_lon");
			inp_con_lon.setAttribute("value", con_lon);
			
			var inp_con_ext = document.createElement("input");
			inp_con_ext.setAttribute("type", "hidden");
			inp_con_ext.setAttribute("id", "con_ext");
			inp_con_ext.setAttribute("name", "con_ext");
			inp_con_ext.setAttribute("value", tmp_nam + "_ext.wkt");
			
			var inp_con_est = document.createElement("input");
			inp_con_est.setAttribute("type", "hidden");
			inp_con_est.setAttribute("id", "con_est");
			inp_con_est.setAttribute("name", "con_est");
			inp_con_est.setAttribute("value", tmp_nam + "_est.wkt");
			
			con_form.appendChild(inp_prj_id);
			con_form.appendChild(inp_img_fl);
			con_form.appendChild(inp_con_nam);
			con_form.appendChild(inp_con_bgn);
			con_form.appendChild(inp_con_end);
			con_form.appendChild(inp_con_dsc);
			con_form.appendChild(inp_con_add);
			con_form.appendChild(inp_con_lat);
			con_form.appendChild(inp_con_lon);
			con_form.appendChild(inp_con_ext);
			con_form.appendChild(inp_con_est);
			
			con_form.setAttribute("action", "insert_consolidation.php");
			con_form.setAttribute("method", "post");
			con_form.submit();
			
			return false;
		}
		
		function backToConsolidation(prj_id) {
			var con_form = document.createElement("form");
			document.body.appendChild(con_form);
			
			var inp_prj_id = document.createElement("input");
			inp_prj_id.setAttribute("type", "hidden");
			inp_prj_id.setAttribute("id", "prj_id");
			inp_prj_id.setAttribute("name", "prj_id");
			inp_prj_id.setAttribute("value", prj_id);
			
			con_form.appendChild(inp_prj_id);
			
			con_form.setAttribute("action", "consolidation.php");
			con_form.setAttribute("method", "post");
			con_form.submit();
			
			return false;
		}
	</script>
</html>