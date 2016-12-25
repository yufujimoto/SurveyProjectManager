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
	
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get parameters from post.
	$err = $_REQUEST["err"];
	$uuid = uniqid($_SESSION["USERNAME"]."_");
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
		
		<!-- Import modal CSS -->
		<link href="lib/modal.css" rel="stylesheet" />
		
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
								<h2>プロジェクトの登録</h2>
							</td>
						</tr>
						<tr>
							<td style="text-align: left">
									<button id="btn_add_mat"
											name="btn_add_mat"
											class="btn btn-sm btn-default"
											type="submit" value="add_material"
											onclick="backToProject()">
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> プロジェクトの管理に戻る</span>
									</button>
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
				<table class='table table' style="border: hidden">
					<!-- iFrame for showing Avatar -->
					<tr style="text-align: center">
						<td colspan="2">
							<iframe id="iframe_avatar" 
									name="iframe_avatar"
									style="width: 610px; height: 410px; border: hidden; border-color: #999999;"
									src="avatar_uploaded.php?target=project&hight=400&width=600">
							</iframe>
						</td>
					</tr>
					<tr>
						<form id="form_avatar" method="post" enctype="multipart/form-data">
							<td style="width: auto">
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
										   readonly value=""/>
								</div>
							</td>
							<td style="width: 100px">
								<input id="btn-upload" 
									   name="btn-upload"
									   class="btn btn-md btn-success"
									   type="submit"
									   value="アップロード"
									   onclick='refreshAvatar(id="<?php echo $uuid;?>",h=400,w=600,target="project");'/>
							</td>
						</form>
					</tr>
				</table>
			</div>
			
			<!-- Project view -->
			<div class="row">
				<form action="insert_project.php" method="post">
					<table class='table table'>
						<!------------------------
						   Project Infrormation
						------------------------->
						<tr style="background-color: #343399; color: #ffffff">
							<td colspan="2">
								<span class="glyphicon glyphicon-info-sign" aria-hidden="true"> プロジェクト情報</span>
							</td>
						</tr>
						<!-- Project title -->
						<tr>
							<td style='width: 200px; text-align: center; vertical-align: middle'>課題名</td>
							<td><input class="form-control"  type='text' name="title"></td>
						</tr>
						<!-- Project name -->
						<tr>
							<td style='width: 200px; text-align: center; vertical-align: middle'>プロジェクト名</td>
							<td><input class="form-control"  type='text' name="name"></td>
						</tr>
						<!-- Period and phase of the project -->
						<tr>
							<td style='text-align: center; vertical-align: middle'>期間と次数</td>
							<td>
								<div class="row">
									<!-- The date of started -->
									<div class="form-group col-lg-4">
										<div class="input-group">
											<span class="input-group-addon" id="basic-addon1">開始:</span>
											<input class="form-control"
												   type="text"
												   name="date_from"
												   placeholder="YYYY-MM-DD"
												   id="date_from"
												   onclick="setSens('date_to', 'max');" >
										</div>
									</div>
									<!-- The date of finished -->
									<div class="form-group col-lg-4">
										<div class="input-group">
											<span class="input-group-addon" id="basic-addon1">終了:</span>
											<input class="form-control"
												   type='text'
												   name="date_to"
												   placeholder="YYYY-MM-DD"
												   id="date_to"
												   onclick="setSens('date_from', 'min');" >
										</div>
									</div>
									<!-- The phase of the project -->
									<div class="form-group col-lg-4">
										<div class="input-group">
											<span class="input-group-addon" id="basic-addon1">調査次数:</span>
											<select class="combobox input-large form-control" name="phase" style='text-align: center'>
												<?php
													for ($i = 1; $i <= 20; $i++) {
														echo "\t\t\t\t\t\t\t<option value='". $i ."'>" . $i . "</option>\n";
													}
												?>
											</select>
										</div>
									</div>
								</div>
							</td>
						</tr>
						
						<!-- Descriptions about the project -->
						<tr>
							<td style='text-align: center; vertical-align: middle'>プロジェクト紹介</td>
							<td>
								<textarea class="form-control" style='resize: none;'rows='10' name='intro'></textarea>
							</td>
						</tr>
						<tr>
							<td style='text-align: center; vertical-align: middle'>調査原因</td>
							<td>
								<textarea class="form-control" style='resize: none;'rows='10' name='cause'></textarea>
							</td>
						</tr>
						<tr>
							<td style='text-align: center; vertical-align: middle'>特記事項</td>
							<td>
								<textarea class="form-control" style='resize: none;'rows='10' name='desc'></textarea>
							</td>
						</tr>
					</table>
					
					<!-- Update button -->
					<hr />
					<table id="submission" class="table" style="border: hidden; padding: 0px; margin: 0px">
						<tr>
							<td style="text-align: right;">
								<button class="btn btn-md btn-success" type="submit" value="registeration">
									<span class="glyphicon glyphicon-plus" aria-hidden="true"> プロジェクトの登録</span>
								</button>
							</td>
						</tr>
					</table>
					<input type="hidden" name="prj_fimg" value="<?php echo $uuid;?>.jpg">
				</form>
			</div>
		</div>
		<script language="JavaScript" type="text/javascript">
			function backToProject() {
				window.location.href = "project.php";
				return false;
			}
		</script>
	</body>
</html>