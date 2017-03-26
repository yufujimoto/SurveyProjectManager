<?php
	$config['img_path'] = '/project/SurveyProjectManager/uploads'; // Relative to domain name
	$config['upload_path'] = $_SERVER['SERVER_NAME'].$config['img_path']; // Physical path. [Usually works fine like this]
	
	echo $config['upload_path'];
?>