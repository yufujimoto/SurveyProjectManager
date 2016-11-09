<?php
    session_start();
    
    // Check session status.
    if (!isset($_SESSION["USERNAME"])) {
      header("Location: logout.php");
      exit;
    }
	
	if ($_SESSION["USERTYPE"] != "Administrator") {
		header("Location: main.php");
	}
	
	require 'lib/guid.php';
    require "lib/config.php";
	
	header("Content-Type: text/html; charset=UTF-8");
	
	// Open the connection to DB
	$err = $_GET['err'];
	$uuid = uniqid('php_');
	$img = "uploads/".$uuid.".jpg";
	$tmg = "uploads/thumbnail_".$uuid.".jpg";
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
		<script>
			function doOnLoad(){
				refreshAvatar(id="<?php echo $uuid;?>",h=600,w=800,target="project");
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
		<div class="container" style="margin: 0 auto; padding-top: 30px;">
			<!-- Page Header -->
			<div class="row"><table class='table'>
				<thead style="text-align: center">
					<!-- Main Label of CSV uploader -->
					<tr style="background-color:#343399; color:#ffffff;"><td><h2>プロジェクトの登録</h2></td></tr>
				</thead>
			</table></div>

			<!-- Avatar -->
			<div class="row">
				<table class='table table' style="border: hidden">
					<!-- iFrame for showing Avatar -->
					<tr style="text-align: center"><td colspan="2">
							<iframe name="iframe_avatar" style="width: 610px; height: 410px; border: hidden; border-color: #999999;" src="avatar_uploaded.php"></iframe>
					</td></tr>
					<tr><form id="form_avatar" method="post" enctype="multipart/form-data">
						<td style="width: auto">
							<div class="input-group">
								<span class="input-group-btn">
									<span class="btn btn-primary btn-file">Browse&hellip;
										<input id="input_avatar" type="file" name="avatar" size="50" accept=".jpg,.JPG,.jpeg,.JPEG" />
									</span>
								</span>
								<input id="name_avatar" type="text" class="form-control" readonly value=""/></div>
						</td>
						<td style="width: 100px">
							<input name="btn-upload" id="btn-upload" class="btn btn-md btn-success" type="submit" value="アップロード" onclick='refreshAvatar(id="<?php echo $uuid;?>",h=400,w=600,target="project");'/>
						</td>
					</form></tr>
				</table>
			</div>
			
			<!-- Project view -->
			<div class="row"><form action="insert_project.php" method="post">
				<table class='table table'>
					<tr style="background-color: #343399; color: #ffffff">
						<td ><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> プロジェクト情報</td>
						<td style="color: red"><?php echo $err; ?></td>
					</tr>
					<tr>
						<td style='width: 200px; text-align: center; vertical-align: middle'>課題名<span style="color: red"></span></td>
						<td><input class="form-control"  type='text' name="title"></td>
					</tr>
					<tr>
						<td style='width: 200px; text-align: center; vertical-align: middle'>プロジェクト名<span style="color: red"></span></td>
						<td><input class="form-control"  type='text' name="name"></td>
					</tr>
					<tr>
						<td style='text-align: center; vertical-align: middle'>期間と次数</td>
						<td><div class="row">
							<div class="form-group col-lg-4"><div class="input-group"><span class="input-group-addon" id="basic-addon1">開始:</span><input class="form-control" type="text" name="date_from" placeholder="YYYY-MM-DD" id="date_from" onclick="setSens('date_to', 'max');" ></div></div>
							<div class="form-group col-lg-4"><div class="input-group"><span class="input-group-addon" id="basic-addon1">終了:</span><input class="form-control" type='text' name="date_to" placeholder="YYYY-MM-DD" id="date_to" onclick="setSens('date_from', 'min');" ></div></div>
							<div class="form-group col-lg-4"><div class="input-group">
								<span class="input-group-addon" id="basic-addon1">調査次数:</span>
								<select class="combobox input-large form-control" name="phase" style='text-align: center'>
									<?php
										for ($i = 1; $i <= 20; $i++) {
											echo "\t\t\t\t\t\t\t<option value='". $i ."'>" . $i . "</option>\n";
										}
									?>
								</select></div>
							</div>
						</div></td>
					</tr>
					
					<tr><td style='text-align: center; vertical-align: middle'>プロジェクト紹介</td><td><textarea class="form-control" style='resize: none;'rows='10' name='intro'></textarea></td></tr>
					<tr><td style='text-align: center; vertical-align: middle'>調査原因</td><td><textarea class="form-control" style='resize: none;'rows='10' name='cause'></textarea></td></tr>
					<tr><td style='text-align: center; vertical-align: middle'>特記事項</td><td><textarea class="form-control" style='resize: none;'rows='10' name='desc'></textarea></td></tr>
				</table>
				<!-- Update button -->
				<hr />
				<table id="submission" class="table" style="border: hidden; padding: 0px; margin: 0px"><tr><td style="text-align: right;">
					<button class="btn btn-md btn-success" type="submit" value="registeration"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> プロジェクトの登録</button>
				</td></tr></table>
				<input type="hidden" name="prj_fimg" value="<?php echo $uuid;?>.jpg">
			</form>
		</div>
	</body>
</html>