<?php 
    //echo $_POST['photo'];
    
//    $fp = fopen('fotos/photo.png', 'w') or die("Error leyendo photo.png");
//    fwrite($fp, $_POST['photo']);
//    fclose($fp);
    
    preg_match('#^data:[\w/]+(;[\w=]+)*,([\w+/=%]+)$#', $_POST["photo"], $data);
    
    $time = time();
    $filename = "output_$time.png";
    
    $fh = fopen("fotos/$filename", 'w+') or die("can't open file");
    fwrite($fh, base64_decode($data[2]));
    fclose($fh);
    
    echo "done";
?>