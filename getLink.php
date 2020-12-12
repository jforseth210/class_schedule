<?php
    $class=$_POST["class"];
    $fileContents = file_get_contents("courses.json");
    $readArray = json_decode($fileContents, true);
    $periods = array_keys($readArray); 
    for ($period = 0; $period <= sizeof($readArray) -1; $period++){
        
       
        for ($course = 0; $course <= sizeof($readArray[$periods[$period]]) -1; $course++){
            if ($readArray[$periods[$period]][$course]["Name"] == $class){
                echo $readArray[$periods[$period]][$course]["Video Call"];  
            }
        }    
    }
?>
