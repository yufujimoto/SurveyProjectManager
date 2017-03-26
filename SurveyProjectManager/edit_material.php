<?php
	header("Content-Type: text/html; charset=UTF-8");
	
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
	require_once "lib/tinymce/plugins/jbimages/config.php";
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	$prj_id= $_REQUEST['prj_id'];
	$con_id= $_REQUEST['con_id'];
	$mat_id= $_REQUEST['mat_id'];
	
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
	$sql_sel_mat = "SELECT
		uuid,
		name,
		estimated_period_beginning,
		estimated_period_ending,
		represented_point,
		path,
		area,
		material_number,
		descriptions
		FROM material WHERE uuid = '".$mat_id."'";
	
	// Excute the query and get the result of query.
	$sql_res_mat = pg_query($conn, $sql_sel_mat);
	if (!$sql_res_mat) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_mat = pg_fetch_all($sql_res_mat);
	
	$timezone = "'Asia/Tokyo'";
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
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
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
											onclick='updateMeterial(
														"<?php echo $prj_id; ?>",
														"<?php echo $con_id; ?>",
														"<?php echo $mat_id; ?>"
										);'>
										<span class="glyphicon glyphicon-save" aria-hidden="true"> 上書き保存</span>
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
			<!-- Consolidation list -->
			<div class="row">
				<table id="tbl_mat" class="table table">
					<tr style='text-align: left;'>
						<?php
							// For each row, HTML list is created and showed on browser.
							foreach ($rows_mat as $row_mat){
								// Get a value in each field.
								$mat_nam = $row_mat['name'];
								$mat_est_bgn = $row_mat['estimated_period_beginning'];
								$mat_est_end = $row_mat['estimated_period_ending'];
								$mat_pnt = $row_mat['represented_point'];
								$mat_pth = $row_mat['path'];
								$mat_are = $row_mat['area'];
								$mat_mnm = $row_mat['material_number'];
								$mat_dsc = $row_mat['descriptions'];
								
								$sql_sel_mat_add = "SELECT
									uuid,
									key,
									value,
									type
									FROM additional_information WHERE mat_id = '".$mat_id."' ORDER BY id";
								
								// Excute the query and get the result of query.
								$sql_res_mat_add = pg_query($conn, $sql_sel_mat_add);
								if (!$sql_res_mat_add) {
									// Get the error message.
									$err = "DB Error: ".pg_last_error($conn);
									
									// Move to Main Page.
									header("Location: main.php?err=".$err);
									exit;
								}
								// Get additional Attributes of the material
								$rows_mat_add = pg_fetch_all($sql_res_mat_add);
								
								// Give the table id by using report id.
								$tbl_mat_id = "tbl_img_".$mat_id;
							}
						?>
						<td id="td_mat_inf" style='vertical-align: top; width: 500px'>
							<div id="mat_bsc_inf" name="mat_bsc_inf">
								<h4>基本属性</h4>
								<div id="mat_uid" class="input-group">
									<span class="input-group-addon" style="width: 100px">mat_id:</span>
									<input class="form-control"
												   type="text"
												   name="inp_mat_uid"
												   style="width: 354px"
												   readonly 
												   value="<?php echo $mat_id; ?>"/>
								</div>
								<div id="mat_num" class="input-group">
									<span class="input-group-addon" style="width: 100px">資料番号:</span>
									<input class="form-control"
												   type="text"
												   name="inp_mat_num"
												   style="width: 354px"
												   value="<?php echo $mat_mnm; ?>"/>
								</div>
								<div id="mat_nam" class="input-group">
									<span class="input-group-addon" style="width: 100px">資料名:</span>
									<input class="form-control"
												   type="text"
												   name="inp_mat_nam"
												   style="width: 354px"
												   value="<?php echo $mat_nam; ?>"/>
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
														   value="<?php echo $mat_est_bgn; ?>"/>
										</div>
										<div id="mat_end" class="input-group">
											<span class="input-group-addon" style="width: 50px">終了</span>
											<input class="form-control"
														   type="text"
														   name="inp_mat_end"
														   style="width: 300px"
														   value="<?php echo $mat_est_end; ?>"/>
										</div>
									</div>
								</div>
								<div class="input-group">
									<span class="input-group-addon" style="width: 100px">備考</span>
									<textarea id="mat_dsc"
											  class='form-control'
											  style='resize: none; width: 354px; text-align: left'rows='5'
											  name='intro'><?php echo $mat_dsc; ?></textarea>
								</div>
							</div>
							<div id="mat_add_inf" name="mat_add_inf">
								<h4>付加属性</h4>
									<?php
										foreach ($rows_mat_add as $row_mat_add){
											$mat_add_id = $row_mat_add['uuid'];
											$mat_add_key = $row_mat_add['key'];
											$mat_add_val = $row_mat_add['value'];
											$mat_add_typ = $row_mat_add['type'];
											
											if($mat_add_key!="" or $mat_add_val!=""){
												echo "\t\t\t\t\t\t\t\t<div id='$mat_add_id' class='input-group'>\n";
												echo "\t\t\t\t\t\t\t\t\t<span class='input-group-addon' id='".$mat_add_key."' style='width: 100px'>".$mat_add_key.":</span>\n";
												echo "\t\t\t\t\t\t\t\t\t<input class='form-control' type='text' name='inp_mat_nam' id='inp_mat_nam' style='width: 354px' value='".$mat_add_val."'/>\n";
												echo "\t\t\t\t\t\t\t\t</div>\n";
											}
										}
										
									?>
								</div>
							</td>
							<td>
								<iframe
									id="iframe_img"
									name="hoge"
									style="width: 700px; height: 400px; border: hidden; border-color: #999999;">
								</iframe>
								<input id="img_zoom" type="range" onchange="zoomChanged();"/>
							</td>
						</tr>
						<tr style="text-align: left;">
							<td style="vertical-align: middle;" colspan="2">
								<table id="tbl_img" class="table table">
										<tr style="text-align: left;">
											<td style="vertical-align: middle; text-align: center; width: 200px">サムネイル</td>
											<td style="vertical-align: middle; text-align: center; width: 200px">作成日時</td>
											<td style="vertical-align: middle; text-align: center; width: auto">説明文</td>
											<td> </td>
										</tr>
										<?php
											// Get user name by uuid. 
											$sql_sel_fig = "SELECT * FROM digitized_image WHERE mat_id = '" .$mat_id. "' ORDER BY id";
											$sql_res_fig = pg_query($conn, $sql_sel_fig) or die('Query failed: ' . pg_last_error());
											$rows_fig = pg_fetch_all($sql_res_fig);
											
											foreach ($rows_fig as $row_fig){
												$img_id = $row_fig['uuid'];
												$img_dtc = $row_fig['exif_datetime'];
												$img_dsc = $row_fig['descriptions'];
												$src_img = "material_image_view.php?uuid=".$img_id;
												
												echo "\t\t\t\t\t\t\t\t\t\t<tr id='".$img_id."' name='".$img_id."'>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<td style='vertical-align: top; width: 200px; text-align:center'>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<a href='#' onclick=".'"'."showViewer('".$img_id."'); return false;".'"'.">\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<img height=150 src='".$src_img."' alt='".$src_img."'/>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t</a>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle;text-align:center; width: 200px'>".$img_dtc."</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle;text-align:center'>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<textarea id='dsc_".$img_id."' class='form-control' style='resize: none;' rows='5'>".$img_dsc."</textarea>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</td>\n";
												
												// Control menu.
												echo "\t\t\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle; width:100px'>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t<div class='btn-group-vertical'>\n";
												
												// Create a button for moving to consolidation page.
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-danger'\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\tonclick=deleteRow('tbl_img','".$prj_id."','".$con_id."','".$mat_id."','".$img_id."');>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>図の削除</span>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</button>\n";
												
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\tonclick=updateImageDescription('".$prj_id."','".$con_id."','".$mat_id."','".$img_id."');>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>備考の更新</span>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t</button>\n";
												
												echo "\t\t\t\t\t\t\t\t\t\t\t\t</div>\n";
												echo "\t\t\t\t\t\t\t\t\t\t\t</td>\n";
												echo "\t\t\t\t\t\t\t\t\t\t</tr>\n";
											}
											echo "\t\t\t\t\t\t\t\t\t</table>\n";
											echo "\t\t\t\t\t\t\t\t</td>\n";
											echo "\t\t\t\t\t\t\t</tr>\n";
										// Close the connection to the database.
										pg_close($conn);
									?>
							<tr style='text-align: left;'>
								<td style="width: auto; text-align: right" colspan="2">
									<form class="form-inline" id="form_figure" method="post" enctype="multipart/form-data">
										<div class="input-group">
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
													   style="width:300px"
													   readonly value=""/>
											</div>
											<input id="btn-upload" 
													   name="btn-upload"
													   class="btn btn-md btn-success"
													   type="submit"
													   value="アップロード"
													   onclick='addImageRow(
															"<?php echo $prj_id; ?>",
															"<?php echo $con_id; ?>",
															"<?php echo $mat_id; ?>",
															"<?php echo $_SESSION['USERNAME']; ?>"
														);'/>
										</div>
									</form>
								</td>
							</tr>
						</table>
					</div>
				</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function addImageRow(prj_id, con_id, mat_id, uname){
				var tbl_id="tbl_img";
				var tbl_fig=document.getElementById(tbl_id);
				var cnt_row=tbl_fig.rows.length;
				
				var row=tbl_fig.insertRow(cnt_row);
				var row_id = uname + "_" + guid();
				
				row.setAttribute("id", row_id);
				
				// Define the cell for section name.
				var img_cll_thm=row.insertCell(0);
				img_cll_thm.style.textAlign="left";
				img_cll_thm.style.verticalAlign="middle";
				
				// Define the iframe showing uploaded image.
				var img_cll_thm_frm = document.createElement("iframe");
				img_cll_thm_frm.name=row_id + "_ifrm";
				img_cll_thm_frm.width="300px";
				img_cll_thm_frm.style.border="hidden";
				img_cll_thm_frm.style.border.color="#999999";
				
				// Append the iframe to the cell.
				img_cll_thm.append(img_cll_thm_frm);
				
				// Send parameters for uploading image.
				var param_h = "&height=150";
				var param_w = "";
				var param_target = "&table=consolidation";
				
				if (param_w == "&width="){ param_w = ""; }
				if (param_h == "&height="){ param_h = ""; }
				if (param_target == "&table="){ param_target = ""; }
				
				img_frm = document.getElementById("form_figure");
				img_frm.action = "avatar_uploaded.php?id="+row_id+param_h+param_w+param_target; 
				img_frm.target=row_id + "_ifrm";
				
				// Insert label & number of caption.
				var img_cll_lab=row.insertCell(1);
				img_cll_lab.style.width="200px";
				img_cll_lab.style.textAlign="center";
				img_cll_lab.style.verticalAlign="middle";
				
				// Insert modified date & time.
				var img_cll_dsc=row.insertCell(2);
				img_cll_dsc.style.textAlign="center";
				img_cll_dsc.style.verticalAlign="middle";
				
				// Create a button for editing the section.
				var img_cll_edt=row.insertCell(3);
				img_cll_edt.style.textAlign="center";
				img_cll_edt.style.verticalAlign="middle";
				img_cll_edt.style.width="100px";
				
				// Create a button group for control menue.
				var frm_mat_ctr = document.createElement("div");
				frm_mat_ctr.className="btn-group-vertical";
				
				// Create a button for deleting the section.
				var img_itm_dll=document.createElement("button");
				img_itm_dll.className="btn btn-sm btn-danger";
				img_itm_dll.type=type="submit";
				img_itm_dll.id="btn_img_dll_"  + row_id;
				img_itm_dll.name="btn_img_dll_" + row_id;
				
				// Define the function of deleting the image.
				var img_itm_dll_func = new Function("deleteRow('"+ tbl_id + "','" + row_id + "');");
				img_itm_dll.onclick=img_itm_dll_func;
				
				// Create a button for editing the section.
				var img_itm_smt=document.createElement("button");
				img_itm_smt.className="btn btn-sm btn-primary";
				img_itm_smt.type=type="submit";
				img_itm_smt.id="btn_img_smt_"  + row_id;
				img_itm_smt.name="btn_img_smt_"  + row_id;
				
				// Define the function of submitting the image.
				var img_itm_smt_func = new Function("submitImage('"+ prj_id + "','" + con_id + "','" + mat_id + "','" + row_id + "');");
				img_itm_smt.onclick=img_itm_smt_func;
				
				var spn_edt = document.createElement("span");
				var spn_dll = document.createElement("span");
				spn_edt.innerHTML="画像の登録";
				spn_dll.innerHTML="画像の削除";
				
				img_itm_smt.appendChild(spn_edt);
				img_itm_dll.appendChild(spn_dll);
				frm_mat_ctr.appendChild(img_itm_dll);
				frm_mat_ctr.appendChild(img_itm_smt);
				img_cll_edt.appendChild(frm_mat_ctr);
			}
			
			function deleteRow(tbl_id, prj_id, con_id, mat_id, img_id){
				var tbl_mat=document.getElementById(tbl_id);
				var row_num;
				for (var i=1;i<tbl_mat.rows.length;i++){
					if (tbl_mat.rows[i].id==img_id){
						row_num = i;
					}
				}
				
				var diag_del_con = confirm("この画像を削除しますか？");
				if (diag_del_con === true) {
					// Delete the row.
					tbl_mat.deleteRow(row_num);
					
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
					
					var inp_mat_id = document.createElement("input");
					inp_mat_id.setAttribute("type", "hidden");
					inp_mat_id.setAttribute("id", "mat_id");
					inp_mat_id.setAttribute("name", "mat_id");
					inp_mat_id.setAttribute("value", mat_id);
					
					var inp_img_id = document.createElement("input");
					inp_img_id.setAttribute("type", "hidden");
					inp_img_id.setAttribute("id", "img_id");
					inp_img_id.setAttribute("name", "img_id");
					inp_img_id.setAttribute("value", img_id);
					
					mat_form.appendChild(inp_prj_id);
					mat_form.appendChild(inp_con_id);
					mat_form.appendChild(inp_mat_id);
					mat_form.appendChild(inp_img_id);
					
					mat_form.setAttribute("action", "delete_material_image.php");
					mat_form.setAttribute("method", "post");
					mat_form.submit();
				}
			}
			
			function submitImage(prj_id, con_id, mat_id, img_id){
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
				
				var inp_mat_id = document.createElement("input");
				inp_mat_id.setAttribute("type", "hidden");
				inp_mat_id.setAttribute("id", "mat_id");
				inp_mat_id.setAttribute("name", "mat_id");
				inp_mat_id.setAttribute("value", mat_id);
				
				var inp_img_id = document.createElement("input");
				inp_img_id.setAttribute("type", "hidden");
				inp_img_id.setAttribute("id", "img_id");
				inp_img_id.setAttribute("name", "img_id");
				inp_img_id.setAttribute("value", img_id);
				
				mat_form.appendChild(inp_prj_id);
				mat_form.appendChild(inp_con_id);
				mat_form.appendChild(inp_mat_id);
				mat_form.appendChild(inp_img_id);
				
				mat_form.setAttribute("action", "insert_material_image.php");
				mat_form.setAttribute("method", "post");
				mat_form.submit();
				
				return false;
			}
			
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
			
			function updateImageDescription(prj_id, con_id, mat_id, img_id) {
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
				
				var inp_mat_id = document.createElement("input");
				inp_mat_id.setAttribute("type", "hidden");
				inp_mat_id.setAttribute("id", "mat_id");
				inp_mat_id.setAttribute("name", "mat_id");
				inp_mat_id.setAttribute("value", mat_id);
				
				var inp_img_id = document.createElement("input");
				inp_img_id.setAttribute("type", "hidden");
				inp_img_id.setAttribute("id", "img_id");
				inp_img_id.setAttribute("name", "img_id");
				inp_img_id.setAttribute("value", img_id);
				
				var mat_img_desc = document.getElementById('dsc_'+img_id).value;
				var inp_mat_dsc = document.createElement("input");
				inp_mat_dsc.setAttribute("type", "hidden");
				inp_mat_dsc.setAttribute("id", "img_dsc");
				inp_mat_dsc.setAttribute("name", "img_dsc");
				inp_mat_dsc.setAttribute("value", mat_img_desc);
				
				mat_form.appendChild(inp_prj_id);
				mat_form.appendChild(inp_con_id);
				mat_form.appendChild(inp_mat_id);
				mat_form.appendChild(inp_img_id);
				mat_form.appendChild(inp_mat_dsc);
				
				mat_form.setAttribute("action", "update_material_image.php");
				mat_form.setAttribute("method", "post");
				mat_form.submit();
				
				return false;
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
			
			function updateMeterial(prj_id, con_id, mat_id){
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
				
				var inp_mat_id = document.createElement("input");
				inp_mat_id.setAttribute("type", "hidden");
				inp_mat_id.setAttribute("id", "mat_id");
				inp_mat_id.setAttribute("name", "mat_id");
				inp_mat_id.setAttribute("value", mat_id);
				
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
				mat_form.appendChild(inp_mat_id);
				mat_form.appendChild(inp_add_inf_ids);
				mat_form.appendChild(inp_add_inf_key);
				mat_form.appendChild(inp_add_inf_val);
				mat_form.appendChild(inp_bsc_inf_key);
				mat_form.appendChild(inp_bsc_inf_val);
				
				mat_form.setAttribute("action", "update_material.php");
				mat_form.setAttribute("method", "post");
				mat_form.submit();
				
				return false;
			}
		</script>
	</body>
</html>