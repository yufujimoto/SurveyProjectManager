<?php
    function getExifDateTime($exf_val){
        // Initialyze the return value.
        $date_time = "NULL";
        
        // Separate the entry by space.
        $array = explode(" ",$exf_val);
        
        if(count($array)===2){
            // Fomrmat the text string for PostgreSQL.
            $date = str_replace(":", "-", $array[0]);
            $time = $array[1];
            
            // Get the result.
            $date_time =$date." ".$time;
        }
        
        // Return the result.
        return $date_time;
    }
    
    function getFNumber($exf_val){
        // Initialyze the return value.
        $fnum = "NULL";
        
        // Separate the entry by slash.
        $array = explode("/", $exf_val);
        
        if(count($array)===2){
            $fnum = $array[0]/$array[1];
        }
        
        // Return the result.
        return $fnum;
    }
    
    function getFocalLength($exf_val){
        // Initialyze the return value.
        $flen = "NULL";
        
        // Separate the entry by slash.
        $array = explode("/", $exf_val);
        
        if(count($array)===2){
            $flen = $array[0]/$array[1];
        }
        
        // Return the result.
        return $flen;
    }
    
    function getMaxAperture($exf_val){
        // Initialyze the return value.
        $maxa = "NULL";
        
        // Separate the entry by slash.
        $array = explode("/", $exf_val);
        
        if(count($array)===2){
            $maxa = round($array[0]/$array[1],2);
        }
        
        // Return the result.
        return $maxa;
    }
    
    function getFlash($exf_val){
        // Initialize the return value.
        $flash = "NULL";
        
        // Format the text strint for PostgreSQL.
        $exf_val = str_pad(strtoupper(dechex($exf_val)), 4, '0', STR_PAD_LEFT);
        
        // Find the value.
        if($exf_val==="0000") {$flash = "Flash did not fire";}
        elseif($exf_val==="0001") {$flash = "Flash fired";}
        elseif($exf_val==="0005") {$flash = "Strobe return light not detected";}
        elseif($exf_val==="0007") {$flash = "Strobe return light detected";}
        elseif($exf_val==="0009") {$flash = "Flash fired, compulsory flash mode";}
        elseif($exf_val==="000D") {$flash = "Flash fired, compulsory flash mode, return light not detected";}
        elseif($exf_val==="000F") {$flash = "Flash fired, compulsory flash mode, return light detected";}
        elseif($exf_val==="0010") {$flash = "Flash did not fire, compulsory flash mode";}
        elseif($exf_val==="0018") {$flash = "Flash did not fire, auto mode";}
        elseif($exf_val==="0019") {$flash = "Flash fired, auto mode";}
        elseif($exf_val==="001D") {$flash = "Flash fired, auto mode, return light not detected";}
        elseif($exf_val==="001F") {$flash = "Flash fired, auto mode, return light detected";}
        elseif($exf_val==="0020") {$flash = "No flash function";}
        elseif($exf_val==="0041") {$flash = "Flash fired, red-eye reduction mode";}
        elseif($exf_val==="0045") {$flash = "Flash fired, red-eye reduction mode, return light not detected";}
        elseif($exf_val==="0047") {$flash = "Flash fired, red-eye reduction mode, return light detected";}
        elseif($exf_val==="0049") {$flash = "Flash fired, compulsory flash mode, red-eye reduction mode";}
        elseif($exf_val==="004D") {$flash = "Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected";}
        elseif($exf_val==="004F") {$flash = "Flash fired, compulsory flash mode, red-eye reduction mode, return light detected";}
        elseif($exf_val==="0059") {$flash = "Flash fired, auto mode, red-eye reduction mode";}
        elseif($exf_val==="005D") {$flash = "Flash fired, auto mode, return light not detected, red-eye reduction mode";}
        elseif($exf_val==="005F") {$flash = "Flash fired, auto mode, return light detected, red-eye reduction mode";}
        else{$flash = "Unknown Flash";}
        
        // Return the result.
        return $flash;
    }
    
    function getMeteringMode($exf_val){
        // Initialize the return value.
        $mtrmd = "NULL";
        
        // Find the value.
        if($exf_val===0) { $mtrmd = "Unknown";}
        elseif($exf_val===1) { $mtrmd = "Average";}
        elseif($exf_val===2) { $mtrmd = "CenterWeightedAverage";}
        elseif($exf_val===3) { $mtrmd = "Spot";}
        elseif($exf_val===4) { $mtrmd = "MultiSpot";}
        elseif($exf_val===5) { $mtrmd = "Pattern";}
        elseif($exf_val===6) { $mtrmd = "Partial";}
        elseif($exf_val===255) { $mtrmd = "Other";}
        else{ $mtrmd = "Unknown Metering Mode";}
        
        // Return the result.
        return $mtrmd;
    }
    
    function getLightSource($exf_val){
        // Initialize the return value.
        $lgtsrc = "NULL";
        
        // Find the value.
        if($exf_val===0) { $lgtsrc = "Unknown";}
        elseif($exf_val===1) { $lgtsrc = "Daylight";}
        elseif($exf_val===2) { $lgtsrc = "Fluorescent";}
        elseif($exf_val===3) { $lgtsrc = "Tungsten (incandescent light)";}
        elseif($exf_val===4) { $lgtsrc = "Flash";}
        elseif($exf_val===9) { $lgtsrc = "Fine weather";}
        elseif($exf_val===10) { $lgtsrc = "Cloudy weather";}
        elseif($exf_val===11) { $lgtsrc = "Shade";}
        elseif($exf_val===12) { $lgtsrc = "Daylight fluorescent (D 5700 - 7100K)";}
        elseif($exf_val===13) { $lgtsrc = "Day white fluorescent (N 4600 - 5400K)";}
        elseif($exf_val===14) { $lgtsrc = "Cool white fluorescent (W 3900 - 4500K)";}
        elseif($exf_val===15) { $lgtsrc = "White fluorescent (WW 3200 - 3700K)";}
        elseif($exf_val===17) { $lgtsrc = "Standard light A";}
        elseif($exf_val===18) { $lgtsrc = "Standard light B";}
        elseif($exf_val===19) { $lgtsrc = "Standard light C";}
        elseif($exf_val===20) { $lgtsrc = "D55";}
        elseif($exf_val===21) { $lgtsrc = "D65";}
        elseif($exf_val===22) { $lgtsrc = "D75";}
        elseif($exf_val===23) { $lgtsrc = "D50";}
        elseif($exf_val===24) { $lgtsrc = "ISO studio tungsten";}
        elseif($exf_val===255) { $lgtsrc = "Other light source";}
        else{ $lgtsrc = "Unknown light source";}
        
        // Return the result.
        return $lgtsrc;
    }
    
    function getExposureProgram($exf_val){
        // Initialize the return value.
        $exprg = "NULL";
        
        // Find the value.
        if($exf_val===0) { $exprg = "Not defined";}
        elseif($exf_val===1) { $exprg = "Manual";}
        elseif($exf_val===2) { $exprg = "Normal program";}
        elseif($exf_val===3) { $exprg = "Aperture priority";}
        elseif($exf_val===4) { $exprg = "Shutter priority";}
        elseif($exf_val===5) { $exprg = "Creative program (biased toward depth of field)";}
        elseif($exf_val===6) { $exprg = "Action program (biased toward fast shutter speed)";}
        elseif($exf_val===7) { $exprg = "Portrait mode (for closeup photos with the background out of focus)";}
        elseif($exf_val===8) { $exprg = "Landscape mode (for landscape photos with the background in focus)";}
        else{ $exprg = "Unknown";}
        
        // Return the result.
        return $exprg;
    }
    
    function getColorSpace($exf_val){
        // Initialize the return value.
        $colsp = "NULL";
        
        // Find the value.
        if($exf_val===1) { $colsp = "sRGB";}
        elseif($exf_val===65535) { $colsp = "Uncalibrated";}
        else{ $colsp = "Unknown Color Space";}
        
        // Return the result.
        return $colsp;
    }
    
    function getYCbCr($exf_val){
        // Initialize the return value.
        $ycbcr = "NULL";
        
        // Find the value.
        if($exf_val===1) { $ycbcr = "Centered";}
        elseif($exf_val===2) { $ycbcr = "Cosited";}
        else{ $ycbcr = "Unknown YCbCr Positioning";}
        
        // Return the result.
        return $ycbcr;
    }
    
    function getCompressedBitsPerPixel($exf_val){
        // Initialize the return value.
        $btperpx = "NULL";
        
        // Separate the entry by space.
        $array = explode("/", $exf_val);
        
        if(count($array)===2){
            $btperpx = round($array[0]/$array[1],2);
        }
        
        // Return the result.
        return $btperpx;
    }
    
    function getResolution($exf_val){
        // Initialize the return value.
        $reso = "NULL";
        
        // Separate the entry by space.
        $array = explode("/", $exf_val);
        
        if(count($array)===2){
            $reso = round($array[0]/$array[1],2);
        }
        
        // Return the result.
        return $reso;
    }
    
    function getResolutionUnit($exf_val){
        // Initialize the return value.
        $exf_rsu= "NULL";
        
        // Find the value.
        if($exf_val===1) { $exf_rsu = "No absolute unit of measurement";}
        elseif($exf_val===2) { $exf_rsu = "Inch";}
        elseif($exf_val===3) { $exf_rsu = "Centimeter";}
        else{ $exf_rsu = "Unknown";}
        
        // Return the result.
        return $exf_rsu;
    }
?>