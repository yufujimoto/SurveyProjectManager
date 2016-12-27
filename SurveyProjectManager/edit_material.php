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
	require_once "lib/tinymce/plugins/jbimages/config.php";
	
	define('TXT_IMG_PATH', '/project/SurveyProjectManager/uploads/hoge');
	
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	$prj_id= $_REQUEST['prj_id'];
	$con_id= $_REQUEST['con_id'];
	$mat_id= $_REQUEST['uuid'];
	
	// Connect to the DB.
	$conn = pg_connect(
				"host=".DBHOST.
				" port=".DBPORT.
				" dbname=".DBNAME.
				" user=".DBUSER.
				" password=".DBPASS)
			or die('Connection failed: ' . pg_last_error());
	
	// Get a list of registered project.
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
		// Print the error messages and exit routine if error occors.
		echo "An error occurred in DB query.\n";
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
		<link href="../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" />
		<link href="../theme.css" rel="stylesheet" />
		
		<!-- Import modal CSS -->
		<link href="lib/modal.css" rel="stylesheet" />
		
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
									<span>上書き保存</span>
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
				<table id="tbl_rep" class="table table">
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
														FROM additional_information WHERE mat_id = '".$mat_id."'";
								
								// Excute the query and get the result of query.
								$sql_res_mat_add = pg_query($conn, $sql_sel_mat_add);
								if (!$sql_res_mat_add) {
									// Print the error messages and exit routine if error occors.
									echo "An error occurred in DB query.\n";
									exit;
								}
								// Get additional Attributes of the material
								$rows_mat_add = pg_fetch_all($sql_res_mat_add);
								
								// Give the table id by using report id.
								$tbl_mat_id = "tbl_img_".$mat_id;
							}
						?>
						<td style='vertical-align: top; width: 500px'>
							<h4>基本属性</h4>
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1" style="width: 100px">資料番号:</span>
								<input class="form-control"
											   type="text"
											   name="inp_mat_num"
											   id="inp_mat_num"
											   style="width: 354px"
											   value="<?php echo $mat_mnm; ?>"/>
							</div>
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1" style="width: 100px">資料名:</span>
								<input class="form-control"
											   type="text"
											   name="inp_mat_nam"
											   id="inp_mat_nam"
											   style="width: 354px"
											   value="<?php echo $mat_nam; ?>"/>
							</div>
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1" style="width: 100px">存在期間:</span>
								<div class="input-group-vertical">
									<div class="input-group">
										<span class="input-group-addon" id="basic-addon1" style="width: 50px">開始</span>
										<input class="form-control"
													   type="text"
													   name="inp_mat_bgn"
													   id="inp_mat_bgn"
													   style="width: 300px"
													   value="<?php echo $mat_est_bgn; ?>"/>
									</div>
									<div class="input-group">
										<span class="input-group-addon" id="basic-addon1" style="width: 50px">終了</span>
										<input class="form-control"
													   type="text"
													   name="inp_mat_end"
													   id="inp_mat_end"
													   style="width: 300px"
													   value="<?php echo $mat_est_end; ?>"/>
										</div>
								</div>
							</div>
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1" style="width: 100px">備考</span>
								<textarea id="inp_mat_dsc"
										  class='form-control'
										  style='resize: none; width: 354px; text-align: left'rows='5'
										  name='intro'><?php echo $mat_dsc; ?></textarea>
							</div>
							<h4>付加属性</h4>
								<?php
									foreach ($rows_mat_add as $row_mat_add){
										$mat_add_id = $row_mat_add['uuid'];
										$mat_add_key = $row_mat_add['key'];
										$mat_add_val = $row_mat_add['value'];
										$mat_add_typ = $row_mat_add['type'];
										
										if($mat_add_key!="" or $mat_add_val!=""){
											echo "\t\t\t\t\t\t\t\t<div class='input-group'>\n";
											echo "\t\t\t\t\t\t\t\t\t<span class='input-group-addon' id='basic-addon1' style='width: 100px'>".$mat_add_key.":</span>\n";
											echo "\t\t\t\t\t\t\t\t\t<input class='form-control' type='text' name='inp_mat_nam' id='inp_mat_nam' style='width: 354px' value='".$mat_add_val."'/>\n";
											echo "\t\t\t\t\t\t\t\t</div>\n";
										}
									}
									
								?>
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
												echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\tonclick=deleteRow('".$tbl_img_id."','".$img_id."');>\n";
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
													   onclick='addImageRow();'/>
										</div>
									</form>
								</td>
							</tr>
						</table>
					</div>
				</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function addImageRow(){
				var tbl_id="tbl_img";
				var tbl_fig=document.getElementById(tbl_id);
				var cnt_row=tbl_fig.rows.length;
				
				var row=tbl_fig.insertRow(cnt_row);
				var row_id = guid();
				
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
				
				
				var frm_img_frm = document.createElement("form");
				frm_img_frm.className="form-horizontal";
				
				var img_lab_ipt=document.createElement("input");
				frm_img_frm.appendChild(img_lab_ipt);
				
				img_cll_lab.appendChild(frm_img_frm);
				
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
				
				// Create a button for editing the section.
				var img_itm_edt=document.createElement("P");
				img_itm_edt.id="btn_img_edt_"  + row_id;
				img_itm_edt.name="btn_img_edt_"  + row_id;
				
				// Create a button for deleting the section.
				var img_itm_dll=document.createElement("button");
				img_itm_dll.className="btn btn-sm btn-danger";
				img_itm_dll.type=type="submit";
				img_itm_dll.id="btn_img_dll_"  + row_id;
				img_itm_dll.name="btn_img_dll_" + row_id;
				
				// Define the function of deleting the section.
				var img_itm_dll_func = new Function("deleteRow('"+ tbl_id + "','" + row_id + "');");
				img_itm_dll.onclick=img_itm_dll_func;
				
				var spn_edt = document.createElement("span");
				var spn_dll = document.createElement("span");
				spn_edt.style.color="red";
				spn_edt.innerHTML="未確定";
				spn_dll.innerHTML="章の削除";
				
				img_itm_edt.appendChild(spn_edt);
				img_itm_dll.appendChild(spn_dll);
				frm_mat_ctr.appendChild(img_itm_edt);
				frm_mat_ctr.appendChild(img_itm_dll);
				img_cll_edt.appendChild(frm_mat_ctr);
			}
			
			function deleteRow(tbl_id, row_id){
				var tbl_mat=document.getElementById(tbl_id);
				var row_num;
				for (var i=1;i<tbl_mat.rows.length;i++){
					if (tbl_mat.rows[i].id==row_id){
						row_num = i;
					}
				}
				
				var diag_del_con = confirm("この章を削除しますか？");
				if (diag_del_con === true) {
					// Send the member id to the PHP script to drop selected project from DB.
					// window.location.href = "delete_consolidation.php?uuid=" + con_id + "&prj_id=" + prj_id;
					
					// Delete the row.
					tbl_mat.deleteRow(row_num);
				}
			}
			
			function updateMaterial(prj_id, rep_id, tbl_id){

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
				var mat_img_desc = document.getElementById('dsc_'+img_id).value;
				window.location.href = "update_material_image.php?uuid=" + img_id + "&dsc=" + mat_img_desc + "&prj_id=" + prj_id + "&con_id=" + con_id + "&mat_id=" + mat_id;
				return false;
			}
			
			function backToMaterial(con_id, prj_id) {
				window.location.href = "material.php?uuid=" + con_id + "&prj_id=" + prj_id;
				return false;
			}
			
			function updateMeterial(prj_id, con_id, mat_id){
				window.location.href = "update_material.php?uuid=" + mat_id + "&prj_id=" + prj_id + "&con_id=" + con_id;
			}
		</script>
	</body>
</html>