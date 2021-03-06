<?php
	// Start the session.
    session_start();
	
	// Load external libraries.
	require "lib/guid.php";
    require "lib/config.php";
	
	header("Content-Type: text/html; charset=UTF-8");
	
	// Get parameters from post.
	$err = $_REQUEST['err'];
	
	// Generate unique ID for saving temporal files.
	$tmp_name = uniqid($_SESSION["USERNAME"]."_");
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<title>Add Member</title>
		
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
				refreshAvatar(id="<?php echo $tmp_name;?>",h=400,w="",target="consolidation");
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
			<div class="row">
				<table class="table">
					<thead style="text-align: center">
						<!-- Main Label of CSV uploader -->
						<tr style="background-color:#343399; color:#ffffff;">
							<td>
								<h2>メンバー登録フォーム</h2>
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
			
			<!-- Registration Form -->
			<!-- Avatar -->
			<div class="row">
				<table class='table table' style="border: hidden">
					<!-- iFrame for showing Avatar -->
					<tr>
						<td style="width: 150px;">
							<iframe id="iframe_avatar"
									name="iframe_avatar"
									style="width: 145px; height: 135px; border: solid; border-color: #999999;"
									src="avatar_uploaded.php">
							</iframe>
						</td>
						<form id="form_avatar" method="post" enctype="multipart/form-data">
						<td style="vertical-align: bottom">
							<div class="input-group">
								<span class="input-group-btn">
									<span class="btn btn-primary btn-file">Browse&hellip;
										<input id="input_avatar" type="file" name="avatar" size="50" accept=".jpg,.JPG,.jpeg,.JPEG" />
									</span>
								</span>
								<input id="name_avatar" type="text" class="form-control" readonly value=""/>
							</div>
						</td>
						<td style="width: 100px; vertical-align: bottom">
							<input name="btn-upload"
								   id="btn-upload"
								   class="btn btn-md btn-success"
								   type="submit"
								   value="アップロード"
								   onclick='refreshAvatar(id="<?php echo $tmp_name;?>",h=128,w=128,target="member");'/>
						</td>
					</form></tr>
				</table>
			</div>
				
			<!-- Member Profile -->
			<div class="row"><form action="insert_member.php" method="post">
				<!-- Acount Information -->
				<table class='table table'>
					<tr style="background-color:#343399; color:#ffffff;">
						<td colspan="9"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> アカウント情報</td>
					</tr>
					<tr>
						<td style='text-align: center; vertical-align: middle'>アカウント</td>
						<td><input class="form-control" type='text' name="mem_unm" placeholder="ユーザー名"/></td>
						<td><input class="form-control" type='password' name="mem_pwd" placeholder="パスワード" /></td>
						<td style='text-align: center; vertical-align: middle;'>役割</td>
						<td>
							<select class="combobox input-large form-control" name="mem_typ" style='text-align: center'>
							<option value="Administrator">管理者</option>
							<option value="Standard">標準</option>
							<option value="Part Time">アルバイト</option>
							</select>
						</td>
					</tr>
				</table>
				
				<!-- Name -->
				<table class='table table'>
					<tr style="background-color:#343399; color:#ffffff;">
						<td colspan="9"><span class="glyphicon glyphicon-star" aria-hidden="true"></span> ユーザープロファイル</td></tr>
					<tr>
						<td style="text-align: center; vertical-align: middle">氏　　名</td>
						<td><input class="form-control" type="text" name="mem_snm" placeholder="氏" /></td>
						<td><input class="form-control" type='text' name="mem_fnm" placeholder="名" /></td>
						<td style='text-align: center; vertical-align: middle'>役　　職</td>
						<td>
							<select class="combobox input-large form-control" name="mem_apt" style='text-align: center'>
								<option value="" disabled selected>以下から選択してください</option>
								<option value="Professor">教授</option>
								<option value="Assistant Professor">准教授</option>
								<option value="Lecturer">講師</option>
								<option value="Researcher">研究者</option>
								<option value="curator">学芸員</option>
								<option value="Engineer">技術者</option>
								<option value="Teacher">教師</option>
								<option value="Student">学生</option>
								<option value="Administrative Director">理事長</option>
								<option value="Director">代表</option>
								<option value="President">学長・館長</option>
								<option value="Division Chief">部長</option>
								<option value="Section Chief">課長</option>
								<option value="Unit Chief">係長</option>
								<option value="Part-time Worker">パート・アルバイト</option>
								<option value="Other">その他</option>
							</select>
						</td>
						<td style='text-align: center; vertical-align: middle'>生年月日</td>
						<td><select class="combobox input-large form-control" name="mem_bdy_y" style='text-align: center'>
							<option value="1900" disabled selected>年</option>
							<?php
								for ($i = 0; $i <= 100; $i++) {
									$year = 2015 - $i;
									echo "<option value='". $year ."'>" . $year . "</option>";
								}
							?>
						</select></td>
						<td><select id="birthday_year" class="combobox input-large form-control" name="mem_bdy_m" style='text-align: center'>
							<option value="01" disabled selected>月</option>
							<?php
								for ($i = 1; $i <= 12; $i++) {
									$month = str_pad($i, 2, "0", STR_PAD_LEFT);
									echo "<option value='". $i ."'>" . $month . "</option>";
								}
							?>
						</select></td>
						<td><select class="combobox input-large form-control" name="mem_bdy_d" style='text-align: center'>
							<option value="01" disabled selected>日</option>
							<?php
								for($i = 1; $i <= 31; $i++) {
									$day = str_pad($i, 2, "0", STR_PAD_LEFT);
									echo "<option value='". $day ."'>" . $day . "</option>";
								}
							?>
						</select></td>
					</tr>
				</table>
				
				<table class='table table'>
					<!-- Contact Address -->
					<tr style="background-color:#343399; color:#ffffff;">
						<td colspan="15"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> 連絡先</td>
					</tr>
					<tr>
						<td rowspan="2" style='text-align: center; vertical-align: middle'>住　　所</td>
						<td colspan="4"><input class="form-control" type='text' name="mem_zip" placeholder="郵便番号"/></td>
						<td colspan="4"><input class="form-control" type='text' name="mem_adm" placeholder="都道府県"/></td>
						<td colspan="5"><input class="form-control" type='text' name="mem_cty" placeholder="市町村"/></td>
					</tr>
					<tr>
						<td colspan="15"><input class="form-control" type='text' name="mem_add" placeholder="住所"/></td>

					</tr>
					<tr>
						<td style='text-align: center; vertical-align: middle'>電話番号</td>
						<td colspan="4"><input class="form-control" type='text' name="mem_phn" placeholder="電話番号"/></td>
						<td colspan="4"><input class="form-control" type='text' name="mem_mph" placeholder="携帯電話" /></td>
						<td style='text-align: center; vertical-align: middle'>PCメール</td>
						<td colspan="5"><input class="form-control" type='email' name="mem_eml" placeholder="example@mail.com"/></td>					
					</tr>
				</table>
				
				<!-- Affiliation -->
				<table class='table table'>
					<tr style="background-color:#343399; color:#ffffff;"><td colspan="12"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> 所属に関する情報</td></tr>
						<td style='text-align: center; vertical-align: middle'>所　　属</td>
						<td colspan="4"><input class="form-control" type='text' name="org_nam" placeholder="組織名"/></td>
						<td colspan="4"><input class="form-control" type='text' name="org_sec" placeholder="部署名"/></td>
						<td style='text-align: center; vertical-align: middle'>連絡先</td><td colspan="3"><input class="form-control" type='text' name="org_phn" placeholder="電話番号"/></td>
					</tr>
					<tr>
						<td rowspan="2" style='text-align: center; vertical-align: middle'>住　　所</td>
						<td colspan="3"><input class="form-control" type='text' name="org_zip" placeholder="郵便番号"/></td>
						<td colspan="4"><input class="form-control" type='text' name="org_adm" placeholder="都道府県"/></td>
						<td colspan="4"><input class="form-control" type='text' name="org_cty" placeholder="市町村名"/></td>
					</tr>
					<tr><td colspan=11><input class="form-control" type='text' name="org_add" placeholder="住所"/></tr>
					<tr><td colspan=12 style="text-align: right">
					<button class="btn btn-md btn-success" type="submit" value="registeration">
						<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> このユーザー追加する
					</button></td></tr>
				</table>
				<input type="hidden" name="mem_avt" value="<?php echo $tmp_name;?>.jpg">
			</form>
		</div></div>
	</body>
</html>