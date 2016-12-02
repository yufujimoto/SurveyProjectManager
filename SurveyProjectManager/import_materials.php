<?php
    $con_id = $_REQUEST["uuid"]
?>
<!DOCTYPE html>
<html>
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
		
		<!-- Import external scripts for Bootstrap CSS -->
		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
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
        
        <script type="text/javascript">
			function importCsv(){
				var dummy;
				var form = document.getElementById('form_csv');
				var filename = document.getElementById('name_csv');
				var input = document.getElementById('input_csv');
				
				form.action = "parse_materials_csv.php?uuid=" + "<?php echo $con_id; ?>";
				form.target = "iframe_csv";
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
		
		<!-- Main containts -->
		<div id="container" class="container" style="padding-top: 30px">
			<!-- Main Label of CSV uploader -->
			<div class="row"><table class='table'>
				<thead style="text-align: center">
					<tr style="background-color:#343399; color:#ffffff;"><td colspan=2><h4>Import members from csv</h4></td></tr>
				</thead>
			</table></div>
			
			<!-- Main interface for CSV uploader. -->
			<div class="row"><table class='table' style="border: hidden">
				<tr>
					<form id="form_csv" method="post" enctype="multipart/form-data">
						<td style='text-align: center; vertical-align: middle; width: 200px'>Choose a CSV file</td>
						<td>
							<div class="input-group">
								<span class="input-group-btn"><span class="btn btn-primary btn-file">Browse&hellip;
								<input id="input_csv" type="file" name="csv_members" size="50" accept=".txt,.TXT,.csv,.CSV"></span></span>
								<input id="name_csv" type="text" class="form-control" readonly value="">
							</div>
						</td>
						<td><input name="btn-upload" id="btn-upload" class="btn btn-md btn-success" type="submit" value="Upload" onclick="importCsv();"/></td>
					</form>
				</tr>
				<tr><td colspan=4>
					<iframe style="border: 0; width: 100%; height: 400px" name="iframe_csv" src="parse_materials_csv.php" />
				</td></tr>
			</table></div>
		</div>
    </body>
</html>