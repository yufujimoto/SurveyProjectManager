<?php
	// Start the session.
	session_start();
	
	// Check session status.
	if (!isset($_SESSION["USERNAME"])) {
	  header("Location: logout.php");
	  exit;
	}
	
	// Load external libraries.
	require_once "lib/guid.php";
	require_once "lib/config.php";
	
	header("Content-Type: text/html; charset=UTF-8");
	
	$err = $_REQUEST["err"];
	$prj_id= $_REQUEST['prj_id'];
	$con_id= $_REQUEST['con_id'];
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Details about Material</title>
		
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
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/bootstrap.min.js"></script>
		
		<!-- Import external scripts for generating image -->
		<script type="text/javascript" src="lib/refreshImage.js"></script>
		
		<!-- Import external scripts for calendar control -->
		<link rel="stylesheet" type="text/css" href="lib/calendar/codebase/dhtmlxcalendar.css"/>
		<script src="lib/calendar/codebase/dhtmlxcalendar.js"></script>
		<script type="text/javascript" src="lib/calendar.js"></script>
		
		<!-- Import external scripts for moving up/down rows -->
		<script type="text/javascript" src="lib/moving_rows.js"></script>
		
		<!-- Import external scripts for generating GUID -->
		<script type="text/javascript" src="lib/guid.js"></script>
		
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
							  echo "<li><a href='project.php'>プロジェクトの管理</a></li>\n";
							  echo "\t\t\t\t\t<li><a href='member.php'>メンバーの管理</a></li>\n";
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
								<h2>資料細目の編集</h2>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
									<button id="btn_bck_mat"
											name="btn_bck_mat"
											class="btn btn-sm btn-default"
											type="submit" value="backToMaterial"
											onclick='backToMaterial("<?php echo $con_id; ?>","<?php echo $prj_id; ?>");'>
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> 資料管理に戻る</span>
									</button>
							</td>
						</tr>
						<tr>
							<td colspan=7 style="text-align: left">
								<div class="btn-group">
									<button id="btn_save"
											name="btn_save"
											class="btn btn-sm btn-primary"
											type="submit"
											onclick='insertMeterial(
														"<?php echo $prj_id; ?>",
														"<?php echo $con_id; ?>"
									);'>
									<span class="glyphicon glyphicon-save" aria-hidden="true"> 保存して次へ</span>
									</button>
									<button id="btn_add_inf"
											name="btn_add_inf"
											class="btn btn-sm btn-success"
											type="submit" 
											onclick="addNewAttribute();">
										<span class="glyphicon glyphicon-plus" aria-hidden="true"> 付加属性の追加</span>
									</button>
									<button id="btn_del_inf"
											name="btn_del_inf"
											class="btn btn-sm btn-danger"
											type="submit" 
											onclick="deleteAttribute();">
										<span class="glyphicon glyphicon-minus" aria-hidden="true"> 付加属性の削除</span>
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
			
			<!-- Contents -->
			<!-- Consolidation list -->
			<div class="row">
				<table id="tbl_mat" class="table table">
					<tr style='text-align: left;'>
						<td id="td_mat_inf" style='vertical-align: top; width: 500px'>
							<div id="mat_bsc_inf" name="mat_bsc_inf">
								<h4 style="text-align: center">基本属性</h4>
								<div id="mat_num" class="input-group">
									<span class="input-group-addon" style="width: 100px">資料番号:</span>
									<input class="form-control"
												   type="text"
												   name="inp_mat_num"
												   style="width: 354px"
												   value=""/>
								</div>
								<div id="mat_nam" class="input-group">
									<span class="input-group-addon" style="width: 100px">資料名:</span>
									<input class="form-control"
												   type="text"
												   name="inp_mat_nam"
												   style="width: 354px"
												   value=""/>
								</div>
								<div class="input-group">
									<span class="input-group-addon" style="width: 100px">存在期間:</span>
									<div class="input-group-vertical">
										<div id="mat_bgn" class="input-group">
											<span class="input-group-addon" style="width: 50px">開始</span>
											<input class="form-control"
														   type="text"
														   name="inp_mat_bgn"
														   style="width: 300px"
														   value=""/>
										</div>
										<div id="mat_end" class="input-group">
											<span class="input-group-addon" style="width: 50px">終了</span>
											<input class="form-control"
														   type="text"
														   name="inp_mat_end"
														   style="width: 300px"
														   value=""/>
										</div>
									</div>
								</div>
								<div class="input-group">
									<span class="input-group-addon" style="width: 100px">備考</span>
									<textarea id="mat_dsc"
											  class='form-control'
											  style='resize: none; width: 354px; text-align: left'rows='10'
											  name='intro'></textarea>
								</div>
							</div>
						</td>
						<td>
							<h4 style="text-align: center">付加属性</h4>
							<table id="tbl_add_inf" name="tbl_add_inf" class="table table-condensed" style="text-align: center">
								<tr>
									<td></td>
									<td>属性名</td>
									<td>属性値</td>
									<td>属性型</td>
								</tr>
								<tr>
									<td><input type='radio' style="vertical-align: middle" name='member' value='' /></td>
									<td><input class='form-control' type='text' name='inp_mat_nam' id='inp_mat_nam' value=''/></td>
									<td><input class='form-control' type='text' name='inp_mat_nam' id='inp_mat_nam' value=''/></td>
									<td>
										<select class="combobox input-large form-control" name="mem_typ" style='text-align: center'>
											<option value="Integer">整数値</option>
											<option value="Real">倍精度浮動小数点</option>
											<option value="CharacterString">文字列</option>
											<option value="GM_Point">ジオメトリ（ポイント）</option>
											<option value="GM_Curve">ジオメトリ（ポリライン）</option>
											<option value="GM_Surface">ジオメトリ（ポリゴン）</option>
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function showViewer(uuid){
				var iframe = document.getElementById('iframe_img');
				var ratio = document.getElementById('img_zoom').value/100;
				var source = "lib/simple_viewer.php?uuid=" + uuid + "&ratio=" + ratio;
				
				document.getElementById('iframe_img').name = uuid;
				
				iframe.src = source;
			}
			
			function zoomChanged(){
				var iframe = document.getElementById('iframe_img');
				var uuid = iframe.name;
				var ratio = document.getElementById('img_zoom').value/100;
				var source = "lib/simple_viewer.php?uuid=" + uuid + "&ratio=" + ratio;
				
				iframe.src = source;
			}
			
			function backToMaterial(con_id, prj_id) {
				var mat_form = document.createElement("form");
				document.body.appendChild(mat_form);
				
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
				
				mat_form.appendChild(inp_prj_id);
				mat_form.appendChild(inp_con_id);
				
				mat_form.setAttribute("action", "material.php");
				mat_form.setAttribute("method", "post");
				mat_form.submit();
				
				return false;
			}
			
			function insertMeterial(prj_id, con_id){
				// Get additional attribute.
				var bsc_mat_keys = [];
				var bsc_mat_vals = [];
				var mat_num = document.getElementById("mat_num");
				var mat_nam = document.getElementById("mat_nam");
				var mat_bgn = document.getElementById("mat_bgn");
				var mat_end = document.getElementById("mat_end");
				var mat_dsc = document.getElementById("mat_dsc");
				
				for (i=0; i < mat_num.childNodes.length; i++) {
					if(mat_num.childNodes[i].tagName === "INPUT"){
						bsc_mat_keys[0] = "mat_num";
						bsc_mat_vals[0] = mat_num.childNodes[i].value;
					}
				}
				for (i=0; i < mat_nam.childNodes.length; i++) {
					if(mat_nam.childNodes[i].tagName === "INPUT"){
						bsc_mat_keys[1] = "mat_nam";
						bsc_mat_vals[1] = mat_nam.childNodes[i].value;
					}
				}
				for (i=0; i < mat_bgn.childNodes.length; i++) {
					if(mat_bgn.childNodes[i].tagName === "INPUT"){
						bsc_mat_keys[2] = "mat_bgn";
						bsc_mat_vals[2] = mat_bgn.childNodes[i].value;
					}
				}
				for (i=0; i < mat_end.childNodes.length; i++) {
					if(mat_end.childNodes[i].tagName === "INPUT"){
						bsc_mat_keys[3] = "mat_end";
						bsc_mat_vals[3] = mat_end.childNodes[i].value;
					}
				}
				bsc_mat_keys[4] = "mat_dsc";
				bsc_mat_vals[4] = mat_dsc.value;
				
				var add_inf = document.getElementById("mat_add_inf");
				var add_ord = 0;
				var add_inf_ids = [];
				var add_inf_keys = [];
				var add_inf_vals = [];
				
				for (i=0; i < add_inf.childNodes.length; i++) {
					elm_nam = add_inf.childNodes[i].tagName;
					if (elm_nam === "DIV"){
						add_inf_nd = add_inf.childNodes[i];
						add_inf_ids[add_ord] = add_inf_nd.id;
						
						for (j=0; j < add_inf_nd.childNodes.length; j++) {
							if(add_inf_nd.childNodes[j].tagName === "SPAN"){
								add_inf_keys[add_ord] = add_inf_nd.childNodes[j].id;
							} else if (add_inf_nd.childNodes[j].tagName === "INPUT"){
								add_inf_vals[add_ord] = add_inf_nd.childNodes[j].value;
							}
						}
						add_ord += 1;
					}
				}
				
				var mat_form = document.createElement("form");
				document.body.appendChild(mat_form);
				
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
				
				var inp_bsc_inf_key = document.createElement("input");
				inp_bsc_inf_key.setAttribute("type", "hidden");
				inp_bsc_inf_key.setAttribute("id", "bsc_mat_keys");
				inp_bsc_inf_key.setAttribute("name", "bsc_mat_keys");
				inp_bsc_inf_key.setAttribute("value", bsc_mat_keys);
				
				var inp_bsc_inf_val = document.createElement("input");
				inp_bsc_inf_val.setAttribute("type", "hidden");
				inp_bsc_inf_val.setAttribute("id", "bsc_mat_vals");
				inp_bsc_inf_val.setAttribute("name", "bsc_mat_vals");
				inp_bsc_inf_val.setAttribute("value", bsc_mat_vals);
				
				var inp_add_inf_ids = document.createElement("input");
				inp_add_inf_ids.setAttribute("type", "hidden");
				inp_add_inf_ids.setAttribute("id", "add_inf_ids");
				inp_add_inf_ids.setAttribute("name", "add_inf_ids");
				inp_add_inf_ids.setAttribute("value", add_inf_ids);
				
				var inp_add_inf_key = document.createElement("input");
				inp_add_inf_key.setAttribute("type", "hidden");
				inp_add_inf_key.setAttribute("id", "add_inf_keys");
				inp_add_inf_key.setAttribute("name", "add_inf_keys");
				inp_add_inf_key.setAttribute("value", add_inf_keys);
				
				var inp_add_inf_val = document.createElement("input");
				inp_add_inf_val.setAttribute("type", "hidden");
				inp_add_inf_val.setAttribute("id", "add_inf_vals");
				inp_add_inf_val.setAttribute("name", "add_inf_vals");
				inp_add_inf_val.setAttribute("value", add_inf_vals);
				
				mat_form.appendChild(inp_prj_id);
				mat_form.appendChild(inp_con_id);
				mat_form.appendChild(inp_add_inf_ids);
				mat_form.appendChild(inp_add_inf_key);
				mat_form.appendChild(inp_add_inf_val);
				mat_form.appendChild(inp_bsc_inf_key);
				mat_form.appendChild(inp_bsc_inf_val);
				
				mat_form.setAttribute("action", "insert_material.php");
				mat_form.setAttribute("method", "post");
				mat_form.submit();
				
				return false;
			}
		</script>
	</body>
</html>