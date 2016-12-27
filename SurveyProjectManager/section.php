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
	
	
	$config['img_path'] = TXT_IMG_PATH;
	mkdir($config['img_path'],0777);
	
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	$prj_id= $_REQUEST['prj_id'];
	$rep_id= $_REQUEST['rep_id'];
	$sec_id= $_REQUEST['uuid'];
	
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
	$sql_sel_sec = "SELECT
						uuid,
						order_number,
						section_name,
						written_by,
						modified_by,
						to_char(date_created, 'yyyy/mm/dd hh24:mm:ss') as date_created,
						to_char(date_modified, 'yyyy/mm/dd hh24:mm:ss') as date_modified,
						body
						FROM section WHERE uuid = '".$sec_id."'";
	
	// Excute the query and get the result of query.
	$sql_res_sec = pg_query($conn, $sql_sel_sec) or die('Query failed: ' . pg_last_error());
	
	while ($row = pg_fetch_assoc($sql_res_sec)) {
		$sec_uuid = $row['uuid'];
		$sec_mod = $row['modified_by'];
		$sec_ord = intval($row['order_number'])+1;
		$sec_cdt = $row['date_created'];
		$sec_mdt = $row['date_modified'];
		$sec_nam = $row['section_name'];
		$sec_wtr = $row['written_by'];
		$sec_bdy = htmlspecialchars_decode($row['body']);
	}
	// Remove special characters.
	$sec_bdy = str_replace("&#13;","",$sec_bdy);
	$sec_bdy = str_replace("\n","",$sec_bdy);
	
	// Get user name by uuid.
	$sql_sel_mem = "SELECT username FROM member WHERE uuid = '" .$sec_mod. "'";
	$sql_res_mem = pg_query($conn, $sql_sel_mem) or die('Query failed: ' . pg_last_error());
	$mem_nam = pg_fetch_assoc($sql_res_mem)["username"]; 
	
	
	// Fetch rows of projects. 
	$rows_sec = pg_fetch_all($sql_res_sec);
	
	$timezone = "'Asia/Tokyo'";
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Section</title>
		
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
		
		<!-- Import TinyMCE modules -->
		<script type="text/javascript" src="lib/tinymce/tinymce.min.js"></script>
		<script>
			tinymce.init({
				selector: '#sec_txt',
				theme: 'modern',
				mode : "textareas",
				plugins: [
					'advlist autolink image link lists charmap print preview hr anchor pagebreak spellchecker',
					'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
					'save table contextmenu directionality emoticons template paste textcolor jbimages'
				],
				toolbar: 'insertfile undo redo |styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image jbimages | print preview media fullpage | forecolor backcolor emoticons',
				image_caption: true,
				relative_urls: false,
				image_advtab: true,
				image_description: true
			});
			
			function doOnLoad(){
				<?php echo "tinymce.activeEditor.setContent('".$sec_bdy."');"?>
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
								<h2>章の管理</h2>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
									<button id="btn_bck_rep"
											name="btn_bck_rep"
											class="btn btn-sm btn-default"
											type="submit" value="backToReport"
											onclick='backToReport("<?php echo $prj_id; ?>");'>
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> 報告書の管理に戻る</span>
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
											onclick='updateSection(
														"<?php echo $sec_id; ?>",
														"<?php echo $prj_id; ?>",
														"<?php echo $rep_id; ?>"
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
				<table id="tbl_rep" class="table table-striped">
					<tr style='text-align: left;'>
						<td style='vertical-align: middle;' colspan="2">
							<h3>第<?php echo $sec_ord; ?>章 <?php echo $sec_nam; ?></h3>
							<ul>
								<li>作 成 日：<?php echo $sec_cdt; ?></li>
								<li>修 正 日：<?php echo $sec_mdt; ?></li>
								<li>作 成 者：<?php echo $mem_nam; ?></li>
							</ul>
							<p>執筆者：
								<input class="form-control"
											   type="text"
											   name="writer"
											   id="writer" value='<?php echo $sec_wtr;?>'/>
							</p>
							<form id="frm_txt" method="post">
								<textarea id="sec_txt" class='form-control' style='resize: none;'rows='50' name='intro'></textarea>
							</form>
							
						</td>
					</tr>
				</table>
			</div>
		</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">			
			function deleteRow(tbl_id, row_id){
				var tbl_sec=document.getElementById(tbl_id);
				var row_num;
				for (var i=1;i<tbl_sec.rows.length;i++){
					if (tbl_sec.rows[i].id==row_id){
						row_num = i;
					}
				}
				
				var diag_del_con = confirm("この章を削除しますか？");
				if (diag_del_con === true) {
					// Send the member id to the PHP script to drop selected project from DB.
					// window.location.href = "delete_consolidation.php?uuid=" + con_id + "&prj_id=" + prj_id;
					
					// Delete the row.
					tbl_sec.deleteRow(row_num);
				}
			}
			
			function backToReport(uuid) {
				window.location.href = "report.php?uuid=" + uuid;
				return false;
			}
			
			function updateSection(uuid, prj_id, rep_id){
				var writer = document.getElementById("writer").value;
				var frm_txt = document.getElementById("frm_txt");
				
				frm_txt.action="update_article.php?uuid=" + uuid + "&wrtr=" + writer + "&prj_id=" + prj_id + "&rep_id=" + rep_id;
				frm_txt.submit();
			}
		</script>
	</body>
</html>