<?php
    /*
     * http://localhost/cdc/getname.php?documento=1140829923&tipo=1
     */
    $url = "http://siri.procuraduria.gov.co/cwebciddno/Consulta.aspx";
    
    $viewstate = urldecode("/wEPDwULLTEyNDY1MzQ5MzRkZDTUZLqD9RmYeeSrXMw8yUaAUEQs");
    $documento = urlencode($_REQUEST['documento']);
    $eventvalidation = urlencode("/wEWCQLahpnYCgKQubWRBwKcufmSBwKdufmSBwKeufmSBwKfufmSBwKYufmSBwK2lqGpAQKM54rGBqiIm1BPJHq3Sq1IIIkofyVOIqvh");
    $boton = urlencode("Generar consulta");
    $tipo = urlencode($_REQUEST['tipo']);
    
    $params = "tipoid=$tipo&idnum=$documento&Button1=$boton&__VIEWSTATE=$viewstate&__EVENTTARGET=&__EVENTARGUMENT=&__EVENTVALIDATION=$eventvalidation";
    
    $handler = curl_init();
    
    curl_setopt($handler, CURLOPT_RETURNTRANSFER , true);
    curl_setopt($handler, CURLOPT_URL, $url);
    curl_setopt($handler, CURLOPT_POST, 3);
    curl_setopt($handler, CURLOPT_POSTFIELDS, $params);
    $response = curl_exec ($handler);
    
//    echo $response;
    
    $values = array ();
    preg_match("/&nbsp; ([A-Z ]+).+<h3>(.+)<\/h3>/", $response, $values);
    $rs = json_encode(array (
        'documento' => $documento,
        'nombre' => $values[1],
        'antecedente' => $values[2]
    ));
    
    echo $rs;
    
//    curl_close($handler);
?>
