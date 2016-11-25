<?php
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	require "lib/guid.php";
    require "lib/config.php";
	
	header("Content-Type: text/html; charset=UTF-8");
	
	$prj_id= $_REQUEST['uuid'];
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
	    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	    <meta http-equiv="Content-Script-Type" content="text/javascript">
	    <meta http-equiv="Content-Style-Type" content="text/css">
	    <meta name="description" content="">
	    <meta name="Yu Fujimoto" content="">
	    <link rel="icon" href="../favicon.ico">
	    <title>プロジェクトの管理</title>
	    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
	    <link href="../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
	    <link href="../theme.css" rel="stylesheet">
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
							echo "<li><a href='project.php'>プロジェクトの管理</a></li>";
							echo "<li><a href='member.php'>メンバーの管理</a></li>";
						}
				  ?>
			      <li><a href="logout.php">ログアウト</a></li>
			    </ul>
		      </div><!--/.nav-collapse -->
		    </div>
	    </div>
		
		<!-- Main containts -->
		<div id="container" class="container" style="padding-top: 30px">
			<!-- Page Header -->
			<div class="row"><table class='table'>
				<thead style="text-align: center">
					<!-- Main Label of CSV uploader -->
					<tr style="background-color:#343399; color:#ffffff;"><td colspan=2><h2>統合体の管理</h2></td></tr>
					<!-- Operating menues -->
					<tr><td colspan=7 style="text-align: left">
						<button class="btn btn-sm btn-success" type="submit" value="add_consolidation" onclick="addNewConsolidation();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 新規統合体の追加</button>
						<!-- <button class="btn btn-sm btn-success" type="submit" value="view_selection" onclick="importConsolidationByCsv();"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> 統合体のインポート</button> -->
						<!-- <button class="btn btn-sm btn-success" type="submit" value="view_selection" onclick="ExportConsolidationByCsv();"><span class="glyphicon glyphicon-download" aria-hidden="true"></span> 統合体のエクスポート</button> -->
						<button id="del_row" class="btn btn-sm btn-danger" type="submit" value="delete" onclick="deleteSelectedConsolidation();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> 選択した統合体の削除</button>
					</td></tr>
				</thead>
			</table></div>
			
			<?php
				// Connect to the DB.
				$dbconn = pg_connect("host=".DBHOST." port=".DBPORT." dbname=".DBNAME." user=".DBUSER." password=".DBPASS) or die('Connection failed: ' . pg_last_error());
				
				// Get a list of registered project.
				// Create a SQL query string.
				$sql_select_consolidation = "SELECT * FROM consolidation WHERE prj_id = '".$prj_id."' ORDER by id";
				
				// Excute the query and get the result of query.
				$result_select_consolidation = pg_query($dbconn, $sql_select_consolidation);
				if (!$result_select_consolidation) {
					// Print the error messages and exit routine if error occors.
					echo "An error occurred in DB query.\n";
					exit;
				}
				
				// Fetch rows of projects. 
				$rows_consolidation = pg_fetch_all($result_select_consolidation);
				$row_count = 0 + intval(pg_num_rows($result_select_consolidation));
				
				// Create section Label and show the total number of the registered project
				echo "<h3>" . $row_count ."件の統合体が登録されています。</h3>\n";
			?>
			<!-- Members list -->
			<div class="row">
				<table id="consolidation" class='table table-striped'>
				<thead style="text-align: center">
					<tr><td colspan="3" style="width: 200px">名称</td><td>場所</td><td style="width: 100px">開始時期</td><td style="width: 100px">終了時期</td><td style="width: 300px">備考</td><td>操作パネル</td></tr>
				</thead>
				<?php
					echo "<form id='selection'>\n";
					foreach ($rows_consolidation as $row){
						$con_uuid = $row['uuid'];
						$con_nam = $row['name'];
						$con_fim = $row['faceimage'];
						$con_gnm = $row['geographic_name'];
						$con_beg = $row['estimated_period_beginning'];
						$con_end = $row['estimated_period_ending'];
						$con_dsc = $row['descriptions'];
						
						echo "\t\t\t\t\t<tr style='text-align: center;'><td style='vertical-align: middle;'><input type='radio' name='member' value='" .$con_uuid. "' /></td>";
						if($con_fim != ""){
							echo "<td style='vertical-align: middle;'><a href='project_consolidations_view.php?uuid=" .$con_uuid. "'><img height=96 src='avatar_consolidation_face.php?uuid=" .$con_uuid."' alt='img'/></a></td>";
						} else {
							echo "<td style='vertical-align: middle;'><a href='project_consolidations_view.php?uuid=" .$con_uuid. "'><img height=96 src='images/noimage.jpg' alt='img'/></a></td>";
						}
						echo "<td style='vertical-align: middle;'>". $con_nam. "</td>";
						echo "<td style='vertical-align: middle;'>". $con_gnm. "</td>";
						echo "<td style='vertical-align: middle;'>". $con_beg. "</td>";
						echo "<td style='vertical-align: middle;'>". $con_end. "</td>";
						echo "<td style='text-align:left; vertical-align: middle;'>". $con_dsc. "</td>";
						echo "<td style='vertical-align: top;'>\n";
						echo '<a class="btn btn-primary" style="cursor: pointer; width: 150px; style="background-color: green" onclick=moveToEditConsolidation("'.$con_uuid.'");>統合体の編集</a><br />';
						echo '<a class="btn btn-primary" style="cursor: pointer; width: 150px; style="background-color: green" onclick=moveToMaterials("'.$con_uuid.'");>資料細目の編集</a>'."</td></tr>\n";
					}
					echo "\t\t\t\t</form>\n";
					
					// Close the connection to the database.
					pg_close($dbconn);
				;?>
			</table></div>
		</div>
		
		<!-- Javascripts -->
		<script language="JavaScript" type="text/javascript">
			function addNewConsolidation() {
				window.location.href = "add_consolidation.php?uuid=<?php echo $prj_id; ?>";
				return false;
			}
			
			function importConsolidationByCsv() {
				window.location.href = "project_consolidations_add_csv.php";
				
				return false;
			}
			
			function ExportConsolidationByCsv() {
				window.location.href = "project_consolidations_export_csv.php";
				return false;
			}
			
			function moveToMaterials(){
				window.location.href = "material.php?uuid=<?php echo $con_uuid; ?>";
				return false;
			}
			
			function deleteSelectedConsolidation() {
				var table=document.getElementById("consolidation");
				var selection = document.getElementById('selection');
				var rowCount=table.rows.length;
				
				if (rowCount == 2) {
					alert("Cannot delete this member!! Number of member in a project should be more than one member!!");
				} else {
					try{
						for (var i = 0, length = selection.length; i < length; i++) {
							var checked = selection[i].checked;
							
							if (checked) {
								// Get a project id of the selected item.
								prj_id = "<?php echo $prj_id;?>";
								con_uuid = selection[i].value;
								
								// Get the selected row and delete the row. 
								table.deleteRow(i+1);
								
								// Send the member id to the PHP script to drop selected project from DB.
								window.location.href = "delete_consolidation.php?uuid=" + con_uuid + "&prj_id=" + prj_id;
								
								// only one radio can be logically checked, don't check the rest
								break;
							}
						}
					} catch(e){ alert(e); }
				}
			}
			
			function handleSelectedConsolidation() {
				var selection = document.getElementById('selection');
				
				try{
					for (var i = 0, length = selection.length; i < length; i++) {
						var checked = selection[i].checked;
						
						if (checked) {
							// Get a project id of the selected item.
							con_uuid = selection[i].value;
							
							// Send the project id to the PHP script to drop selected project from DB.
							return con_uuid;
						}
					}
				} catch(e){ alert(e); }
			}
		</script>
    </body>
</html>