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
	$err = $_REQUEST["err"];
	$prj_id= $_REQUEST['prj_id'];
	
	// Define the timezone.
	$timezone = "'Asia/Tokyo'";
	
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
	$sql_sel_rep = "SELECT * FROM report WHERE prj_id = '".$prj_id."' ORDER by id";
	
	// Excute the query and get the result of query.
	$sql_res_rep = pg_query($conn, $sql_sel_rep);
	if (!$sql_res_rep) {
		// Get the error message.
		$err = "DB Error: ".pg_last_error($conn);
		
		// Move to Main Page.
		header("Location: main.php?err=".$err);
		exit;
	}
	
	// Fetch rows of projects. 
	$rows_rep = pg_fetch_all($sql_res_rep);
	$row_cnt_rep = 0 + intval(pg_num_rows($sql_res_rep));
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Report</title>
		
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
								<h2>報告書の管理</h2>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
									<button id="btn_add_mat"
											name="btn_add_mat"
											class="btn btn-sm btn-default"
											type="submit" value="add_material"
											onclick="backToMyPage();">
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> マイページに戻る</span>
									</button>
							</td>
						</tr>
						<tr>
							<td colspan=7 style="text-align: left">
								<div class="btn-group">
									<!--
									<button id="btn_add_mat"
											name="btn_add_mat"
											class="btn btn-sm btn-success"
											type="submit" value="add_material"
											onclick="addNewConsolidation('<?php echo $prj_id; ?>');">
										<span class="glyphicon glyphicon-plus" aria-hidden="true"> 新規統合体の追加</span>
									</button>
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
			<div id="contents" class="row">
				<h3><?php echo $row_cnt_rep?>冊の報告書が登録されています。</h3>
			</div>
			
			<!-- Consolidation list -->
			<div class="row">
				<table id="tbl_rep" class="table table-striped">
					<?php
						// For each row, HTML list is created and showed on browser.
						foreach ($rows_rep as $row_rep){
							// Get a value in each field.
							$rep_id = $row_rep['uuid'];
							$rep_ttl = $row_rep['title'];
							$rep_vol = $row_rep['volume'];
							$rep_num = $row_rep['edition'];
							$rep_srs = $row_rep['series'];
							$rep_pub = $row_rep['publisher'];
							$rep_yer = $row_rep['year'];
							$rep_dsc = $row_rep['descriptions'];
							
							// Make HTML tag elements using aquired field values.
							
							// -------------------------------
							// Header row
							if (!empty($rep_vol)) {
								if (!empty($rep_num)){
									$lbl_ttl = $rep_ttl."(".$rep_vol."-".$rep_num.")";
								} else {
									$lbl_ttl = $rep_ttl."(".$rep_vol.")";
								}
							} else {
								$lbl_ttl = $rep_ttl;
							}
							
							echo "<tr style='text-align: left;'>\n";
							echo "\t\t\t\t\t\t<td style='vertical-align: middle;' colspan='2'><h3>". $lbl_ttl."</h3>\n";
							echo "\t\t\t\t\t\t\t<ul>\n";
							echo "\t\t\t\t\t\t\t\t<li>シリーズ：".$rep_srs."</li>\n";
							echo "\t\t\t\t\t\t\t\t<li>出 版 者：".$rep_pub."</li>\n";
							echo "\t\t\t\t\t\t\t\t<li>出 版 年：".$rep_yer."</li>\n";
							echo "\t\t\t\t\t\t\t\t<li>備 　 考：".$rep_dsc."</li>\n";
							echo "\t\t\t\t\t\t\t</ul>\n";
							echo "\t\t\t\t\t\t</td>\n";
							echo "\t\t\t\t\t\t<td style='vertical-align: top; text-align:right;' width=150px>\n";
							
							// Give the table id by using report id.
							$tbl_sec_id = "tbl_sec_".$rep_id;
							
							echo "\t\t\t\t\t\t\t<div class='btn-group-vertical'>\n";
							
							// Create a button for moving to consolidation page.
							echo "\t\t\t\t\t\t\t\t<button id='btn_add_sec_".$rep_id."'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_add_sec'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-success'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='submit' style='text-align: left'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=addSectionRow('".$prj_id."','".$rep_id."','".$_SESSION["USERNAME"]."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-plus'> 章の追加</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							echo "\t\t\t\t\t\t\t\t<button id='btn_edt_sec_".$rep_id."'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_edt_sec_'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-success'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='submit' style='text-align: left'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=editReport('".$rep_id."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-pencil'> 書誌情報の編集</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							echo "\t\t\t\t\t\t\t\t<button id='btn_udt_sec_".$rep_id."'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."name='btn_udt_sec_".$rep_id."'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-warning'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."type='button'\n";
							echo "\t\t\t\t\t\t\t\t\t\t"."style='display:none'\n";
							echo "\t\t\t\t\t\t\t\t\t\tonclick=updateSection('".$prj_id."','".$rep_id."','".$tbl_sec_id."');>\n";
							echo "\t\t\t\t\t\t\t\t\t<span>未確定項目の確定</span>\n";
							echo "\t\t\t\t\t\t\t\t</button>\n";
							echo "\t\t\t\t\t\t\t</div>\n";
							echo "\t\t\t\t\t\t</td>\n";
							echo "\t\t\t\t\t</tr>\n";
							
							// Create a table for showing section list.
							echo "\t\t\t\t\t<tr style='text-align: left;'>\n";
							echo "\t\t\t\t\t\t<td style='vertical-align: middle;' colspan='3'>\n";
							
							// Create the header row of the section list.
							echo "\t\t\t\t\t\t\t<table id='".$tbl_sec_id."' class='table table'>\n";
							echo "\t\t\t\t\t\t\t\t<tr style='text-align: left;'>\n";
							echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle; text-align: center'>セクション名</td>\n";
							echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle; text-align: center'>作成日</td>\n";
							echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle; text-align: center'>更新日</td>\n";
							echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle; text-align: center'>更新者</td>\n";
							echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle; text-align: center'></td>\n";
							echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle; text-align: center'></td>\n";
							echo "\t\t\t\t\t\t\t\t</tr>\n";
							
							// Get a list of sections in this report.
							$sql_sel_sec = "SELECT
												uuid,
												modified_by,
												order_number,
												section_name,
												to_char(date_created, 'yyyy/mm/dd hh24:mm:ss') as date_created,
												to_char(date_modified, 'yyyy/mm/dd hh24:mm:ss') as date_modified 
												FROM section WHERE rep_id = '".$rep_id."' ORDER by order_number";
							
							// Excute the query and get the result of query.
							$sql_res_sec = pg_query($conn, $sql_sel_sec);
							if (!$sql_res_sec) {
								// Print the error messages and exit routine if error occors.
								echo "An error occurred in DB query.\n";
								exit;
							}
							
							// Fetch rows of projects. 
							$rows_sec = pg_fetch_all($sql_res_sec);
							$row_cnt_sec = 0 + intval(pg_num_rows($sql_res_sec));
							
							// For each row, HTML list is created and showed on browser.
							foreach ($rows_sec as $row_sec){
								// Get a value in each field.
								$sec_id = $row_sec['uuid'];
								$sec_mod = $row_sec['modified_by'];
								$sec_onm = $row_sec['order_number'];
								$sec_nam = $row_sec['section_name'];
								$sec_dtc = $row_sec['date_created'];
								$sec_dtm = $row_sec['date_modified'];
								
								// Get user name by uuid.
								$sql_sel_mem = "SELECT username FROM member WHERE uuid = '" .$sec_mod. "'";
								$sql_res_mem = pg_query($conn, $sql_sel_mem) or die('Query failed: ' . pg_last_error());
								while ($row = pg_fetch_assoc($sql_res_mem)) {
									$mem_nam = $row['username'];
								}
								
								echo "\t\t\t\t\t\t\t\t<tr id='".$sec_id."' name='".$sec_onm."'>\n";
								echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle;'>".$sec_nam."</td>\n";
								echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle;text-align:center'>".$sec_dtc."</td>\n";
								echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle;text-align:center'>".$sec_dtm."</td>\n";
								echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle;text-align:center'>".$mem_nam."</td>\n";
								
								// Control menu.
								echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle; width:100px' id='sct_ctr_mv_".$sec_id."'>\n";
								echo "\t\t\t\t\t\t\t\t\t\t<div class='btn-group'>\n";
								
								echo "\t\t\t\t\t\t\t\t\t\t<button id='btn_sct_up_".$sec_id."'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."name='btn_itm_up'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\tonclick=moveUp('".$tbl_sec_id."','".$sec_id."');>\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-chevron-up'></span>\n";
								echo "\t\t\t\t\t\t\t\t\t\t</button>\n";
								
								echo "\t\t\t\t\t\t\t\t\t\t<button id='btn_sct_dwn_".$sec_id."'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."name='btn_itm_dwn'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\tonclick=moveDown('".$prj_id."','".$sec_id."','".$tbl_sec_id."');>\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-chevron-down'></span>\n";
								echo "\t\t\t\t\t\t\t\t\t\t</button>\n";
								
								echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
								echo "\t\t\t\t\t\t\t\t\t</td>\n";
								
								echo "\t\t\t\t\t\t\t\t\t<td style='vertical-align: middle; width:100px'>\n";
								echo "\t\t\t\t\t\t\t\t\t\t<div class='btn-group-vertical'>\n";
								
								// Create a button for moving to consolidation page.
								echo "\t\t\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-danger'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\tonclick=deleteRow('".$prj_id."','".$sec_id."','".$tbl_sec_id."');>\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-remove'> 章の削除</span>\n";
								echo "\t\t\t\t\t\t\t\t\t\t</button>\n";
								
								echo "\t\t\t\t\t\t\t\t\t\t<button id='btn_add_prj'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."name='btn_add_prj'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."class='btn btn-sm btn-primary'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\t"."type='submit'\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t\tonclick=moveToSection('".$sec_id."','".$prj_id."','".$rep_id."');>\n";
								echo "\t\t\t\t\t\t\t\t\t\t\t<span class='glyphicon glyphicon-pencil'> 章の編集</span>\n";
								echo "\t\t\t\t\t\t\t\t\t\t</button>\n";
								
								echo "\t\t\t\t\t\t\t\t\t\t</div>\n";
								echo "\t\t\t\t\t\t\t\t\t</td>\n";
								echo "\t\t\t\t\t\t\t\t\t</tr>\n";
							}
							echo "\t\t\t\t\t\t\t</table>\n";
							echo "\t\t\t\t\t\t</td>\n";
							echo "\t\t\t\t\t</tr>\n";
						}
						// Close the connection to the database.
						pg_close($conn);
					?>
				</table>
			</div>
		</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function addSectionRow(prj_id, rep_id, crt_mem){
				var tbl_id="tbl_sec_"+rep_id;
				var tbl_sec=document.getElementById(tbl_id);
				var cnt_row=tbl_sec.rows.length;
				var row=tbl_sec.insertRow(cnt_row);
				var row_id = guid();

				row.setAttribute("id", row_id);
				
				// Get the date and time of now.
				var cur_dt = new Date();
				var cur_dt_txt = cur_dt.getFullYear() + "-" + (cur_dt.getMonth()+1)  + "-" + cur_dt.getDate();
				var cur_tm_txt = cur_dt.getHours() + ":" + cur_dt.getMinutes() + ":" + cur_dt.getSeconds();
				
				// Get the section title via input prompt.
				var sct_name = prompt("章の名前を入力してください。");
				if (sct_name === null || sct_name === ""){
					exit();
				}
				
				// Define the cell for section name.
				var sct_cll_nam=row.insertCell(0);
				sct_cll_nam.style.textAlign="left";
				sct_cll_nam.style.verticalAlign="middle";
				sct_cll_nam.innerHTML = sct_name;
				
				// Insert current date & time.
				var sct_cll_cdt=row.insertCell(1);
				sct_cll_cdt.style.textAlign="center";
				sct_cll_cdt.style.verticalAlign="middle";
				sct_cll_cdt.innerHTML = cur_dt_txt + " " + cur_tm_txt;
				
				// Insert modified date & time.
				var sct_cll_mdt=row.insertCell(2);
				sct_cll_mdt.style.textAlign="center";
				sct_cll_mdt.style.verticalAlign="middle";
				sct_cll_mdt.innerHTML = cur_dt_txt + " " + cur_tm_txt;
				
				// Insert current date & time.
				var sct_cll_mem=row.insertCell(3);
				sct_cll_mem.style.textAlign="center";
				sct_cll_mem.style.verticalAlign="middle";
				sct_cll_mem.innerHTML = crt_mem;
				
				// Insert control menu of move up/down.
				var sct_cll_ctr=row.insertCell(4);
				sct_cll_ctr.style.width="100px";
				sct_cll_ctr.style.textAlign="center";
				sct_cll_ctr.style.verticalAlign="middle";
				sct_cll_ctr.id="sct_ctr_mv_" + row.id;
				sct_cll_ctr.name="sct_ctr_mv_" + row.id;
				
				// Create a button group for control menu.
				var frm_sec_mv = document.createElement("div");
				frm_sec_mv.className="btn-group";
				
				// Create a up button.
				var sct_itm_up=document.createElement("button");
				sct_itm_up.className="btn btn-sm btn-primary";
				sct_itm_up.type=type="submit";
				sct_itm_up.id="btn_sct_up_" + row_id;
				sct_itm_up.name="btn_sct_up_" + row_id;
				
				//var sct_itm_up_func = new Function("moveUp.call(this);");
				var sct_itm_up_func = new Function("moveUp('"+ tbl_id + "','" + row_id + "');");
				sct_itm_up.onclick=sct_itm_up_func;
				
				// Create a down button.btn_udt_sec_
				var sct_itm_dwn=document.createElement("button");
				sct_itm_dwn.className="btn btn-sm btn-primary";
				sct_itm_dwn.type=type="submit";
				sct_itm_dwn.id="btn_sct_dwn_" + row_id;
				sct_itm_dwn.name="btn_sct_dwn_" + row_id;
				
				var sct_itm_dwn_func = new Function("moveDown('"+ tbl_id + "','" + row_id + "');");
				sct_itm_dwn.onclick=sct_itm_dwn_func;
				
				// Add aicon of the button.
				var spn_up = document.createElement("span");
				var spn_dwn = document.createElement("span");
				spn_dwn.className="glyphicon glyphicon-chevron-down";
				spn_up.className="glyphicon glyphicon-chevron-up";
				
				// Append the button to the form group.
				sct_itm_up.appendChild(spn_up);
				sct_itm_dwn.appendChild(spn_dwn);
				frm_sec_mv.appendChild(sct_itm_up);
				frm_sec_mv.appendChild(sct_itm_dwn);
				sct_cll_ctr.appendChild(frm_sec_mv);
				
				// Create a button for editing the section.
				var sct_cll_edt=row.insertCell(5);
				sct_cll_edt.style.textAlign="center";
				sct_cll_edt.style.verticalAlign="middle";
				
				// Create a button group for control menue.
				var frm_sec_ctr = document.createElement("div");
				frm_sec_ctr.className="btn-group-vertical";
				
				// Create a button for editing the section.
				var sct_itm_edt=document.createElement("P");
				sct_itm_edt.id="btn_sct_edt_"  + row_id;
				sct_itm_edt.name="btn_sct_edt_"  + row_id;
				
				// Create a button for deleting the section.
				var sct_itm_dll=document.createElement("button");
				sct_itm_dll.className="btn btn-sm btn-danger";
				sct_itm_dll.type=type="submit";
				sct_itm_dll.id="btn_sct_dll_"  + row_id;
				sct_itm_dll.name="btn_sct_dll_" + row_id;
				
				// Define the function of deleting the section.
				var sct_itm_dll_func = new Function("deleteRow('" + prj_id + "','" + row_id + "','" + tbl_id + "');");
				sct_itm_dll.onclick=sct_itm_dll_func;
				
				var spn_edt = document.createElement("span");
				spn_edt.style.color="red";
				spn_edt.innerHTML="未確定";
				
				var spn_dll = document.createElement("span");
				spn_dll.className="glyphicon glyphicon-remove";
				spn_dll.innerHTML=" 章の削除";
				
				sct_itm_edt.appendChild(spn_edt);
				sct_itm_dll.appendChild(spn_dll);
				frm_sec_ctr.appendChild(sct_itm_edt);
				frm_sec_ctr.appendChild(sct_itm_dll);
				sct_cll_edt.appendChild(frm_sec_ctr);
				
				var btn_update_id = '<?php echo "btn_udt_sec_".$rep_id; ?>';
				var btn_update = document.getElementById(btn_update_id);
				btn_update.style.display = "block";
			}
			
			function deleteRow(prj_id, sec_id, tbl_id){
				var tbl_sec=document.getElementById(tbl_id);
				var row_num;
				for (var i=1;i<tbl_sec.rows.length;i++){
					if (tbl_sec.rows[i].id==sec_id){
						row_num = i;
					}
				}
				
				var diag_del_con = confirm("この章を削除しますか？");
				if (diag_del_con === true) {
					// Delete the row.
					tbl_sec.deleteRow(row_num);
					
					var rep_form = document.createElement("form");
					document.body.appendChild(rep_form);
					
					var inp_prj_id = document.createElement("input");
					inp_prj_id.setAttribute("type", "hidden");
					inp_prj_id.setAttribute("id", "prj_id");
					inp_prj_id.setAttribute("name", "prj_id");
					inp_prj_id.setAttribute("value", prj_id);
					
					var inp_sec_id = document.createElement("input");
					inp_sec_id.setAttribute("type", "hidden");
					inp_sec_id.setAttribute("id", "sec_id");
					inp_sec_id.setAttribute("name", "sec_id");
					inp_sec_id.setAttribute("value", sec_id);
					
					rep_form.appendChild(inp_prj_id);
					rep_form.appendChild(inp_sec_id);
					
					rep_form.setAttribute("action", "delete_section.php");
					rep_form.setAttribute("method", "post");
					rep_form.submit();
					
					return false;
				}
			}
			
			function updateSection(prj_id, rep_id, tbl_id){
				var tbl_sec = document.getElementById(tbl_id);
				var sec_id = [];
				var sec_nam = [];
				var sec_cdt = [];
				var sec_mdt = [];
				var sec_mem = [];
				var sec_ord = [];
				
				for (var i=1;i<tbl_sec.rows.length;i++){
					sec_id[i-1] = tbl_sec.rows[i].id;
					sec_nam[i-1] = tbl_sec.rows[i].cells[0].innerHTML;
					sec_cdt[i-1] = tbl_sec.rows[i].cells[1].innerHTML;
					sec_mdt[i-1] = tbl_sec.rows[i].cells[2].innerHTML;
					sec_mem[i-1] = tbl_sec.rows[i].cells[3].innerHTML;
					sec_ord[i-1] = i-1;
				}
				
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
				
				var inp_sec_id = document.createElement("input");
				inp_sec_id.setAttribute("type", "hidden");
				inp_sec_id.setAttribute("id", "sec_id");
				inp_sec_id.setAttribute("name", "sec_id");
				inp_sec_id.setAttribute("value", sec_id);
				
				var inp_sec_nam = document.createElement("input");
				inp_sec_nam.setAttribute("type", "hidden");
				inp_sec_nam.setAttribute("id", "sec_nam");
				inp_sec_nam.setAttribute("name", "sec_nam");
				inp_sec_nam.setAttribute("value", sec_nam);
				
				var inp_sec_cdt = document.createElement("input");
				inp_sec_cdt.setAttribute("type", "hidden");
				inp_sec_cdt.setAttribute("id", "sec_cdt");
				inp_sec_cdt.setAttribute("name", "sec_cdt");
				inp_sec_cdt.setAttribute("value", sec_cdt);
				
				var inp_sec_mdt = document.createElement("input");
				inp_sec_mdt.setAttribute("type", "hidden");
				inp_sec_mdt.setAttribute("id", "sec_mdt");
				inp_sec_mdt.setAttribute("name", "sec_mdt");
				inp_sec_mdt.setAttribute("value", sec_mdt);
				
				var inp_sec_mem = document.createElement("input");
				inp_sec_mem.setAttribute("type", "hidden");
				inp_sec_mem.setAttribute("id", "sec_mem");
				inp_sec_mem.setAttribute("name", "sec_mem");
				inp_sec_mem.setAttribute("value", sec_mem);
				
				var inp_sec_ord = document.createElement("input");
				inp_sec_ord.setAttribute("type", "hidden");
				inp_sec_ord.setAttribute("id", "sec_ord");
				inp_sec_ord.setAttribute("name", "sec_ord");
				inp_sec_ord.setAttribute("value", sec_ord);
				
				rep_form.appendChild(inp_prj_id);
				rep_form.appendChild(inp_rep_id);
				rep_form.appendChild(inp_sec_id);
				rep_form.appendChild(inp_sec_nam);
				rep_form.appendChild(inp_sec_cdt);
				rep_form.appendChild(inp_sec_mdt);
				rep_form.appendChild(inp_sec_mem);
				rep_form.appendChild(inp_sec_ord);
				
				rep_form.setAttribute("action", "update_section.php");
				rep_form.setAttribute("method", "post");
				rep_form.submit();
				
				return false;
			}
			
			function moveUp(tbl_id, row_id){
				rowUp(tbl_id, row_id);
				
				var btn_update_id = '<?php echo "btn_udt_sec_".$rep_id; ?>';
				var btn_update = document.getElementById(btn_update_id);
				btn_update.style.display = "block";
			}
			
			function moveDown(tbl_id, row_id){
				rowDown(tbl_id, row_id);
				
				var btn_update_id = '<?php echo "btn_udt_sec_".$rep_id; ?>';
				var btn_update = document.getElementById(btn_update_id);
				btn_update.style.display = "block";
			}
			
			function backToMyPage(uuid) {
				window.location.href = "main.php?uuid=" + uuid;
				return false;
			}
			
			function moveToSection(uuid, prj_id, rep_id){
				window.location.href = "section.php?uuid=" + uuid + "&prj_id=" + prj_id + "&rep_id=" + rep_id;
			}
			
			
		</script>
	</body>
</html>