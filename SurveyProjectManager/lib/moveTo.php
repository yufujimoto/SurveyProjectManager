<?php
    // Include external scripts file.
    include_once "config.php";
    
    function moveToLocal($local_page, $data){
        // Get the URL with protocol return to. 
        $url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["SERVER_NAME"]."/".FULLPATH."/".$local_page;
        
        // Use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'=> 'Cookie: '.$_SERVER['HTTP_COOKIE']."\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        
        // Convert array to stream_context. 
        $context  = stream_context_create($options);
        
        // Open the URL with file get contents function.
        $result = file_get_contents($url, false, $context);
        echo $result;
    }
?>