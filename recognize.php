<?php

//************************************************
// 1. First, we need to build an XML string to send
// to the API. In the example below, the sring is
// built in a very straight forward way.
//************************************************
// Lets start with adding to our string input URL and file type, which is
// the file you want to process

$filename = 'http://192.227.159.91/test/IMG_20130504_150259.jpg';

$inputURL = '<InputURL>' . $filename . '</InputURL>';

$inputTYPE = '<InputType>JPG</InputType>';



// If you want to build a page to where the result would
// be proccessed, you need to specify the Notify URL
// Uncomment the next line if you want to use the feature
// and dont forget to add it to a job string down below
//$notifyURL = '<NotifyURL>http://www.example.com/ocrNotify.php</NotifyURL>';
//(Optional)Next step is to add cleanup options.

$cleanup = '<CleanupSettings>';

$cleanup .='<Deskew>false</Deskew>';

$cleanup.='<RemoveGarbage>true</RemoveGarbage>';

$cleanup.='<RemoveTexture>false</RemoveTexture>';

$cleanup .='<RotationType>Automatic</RotationType>';

$cleanup .='</CleanupSettings>';



// (Optional)After clean up options, we are going to indicate
// OCR setting for your file

$settings = '<OCRSettings>';

$settings.='<PrintType>Print;Typewriter</PrintType>'; //I have indicated multiple print types separated by ';'

$settings.='<OCRLanguage>English;Danish</OCRLanguage>'; //and again, multiple items separated by ';'\

$settings.='<SpeedOCR>true</SpeedOCR>';

$settings.='<AnalysisMode>TextAggressive</AnalysisMode>';

$settings.='<LookForBarcodes>false</LookForBarcodes>';

$settings.='</OCRSettings>';



// (Optional) You can also specify a format is which you want to receive
// you processed file in

$output = '<OutputSettings>';

$output.='<ExportFormat>Text;PDF</ExportFormat>';

$output.='</OutputSettings>';



// Now create a job with all the settings you have specified

$job = '<Job>' . $inputURL . $inputTYPE . $cleanup . $settings . $output . '</Job>';





//****************************************************
// 2. Now that we have made our upload string with all
// the settings we desire, we are going to create an
// xml request using curl.
//****************************************************
// First, we are going to define POST URL

$key = 'tfVu6sIbwlwcguw6VTOU9WyJkYlymuSs';

define('XML_REQUEST', $job); //this is the actual request to be sent

define('XML_POST_URL', 'http://svc.webservius.com/v1/wisetrend/wiseocr/submit?wsvKey=' . $key); //this is where we want it to be sent
// Then we will initialize handle and set request options

$header[] = 'Content-type: text/xml';

$header[] = 'Connection: close';



// Now, we will use defined settings above to create an xml
// request using curl

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, XML_POST_URL);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($ch, CURLOPT_TIMEOUT, 4);

curl_setopt($ch, CURLOPT_POSTFIELDS, XML_REQUEST);

curl_setopt($ch, CURLOPT_HTTPHEADER, $header);



// After we have created the request we will
// execute it (Optional) and also time the transaction

$start = array_sum(explode(' ', microtime()));

$result = curl_exec($ch); //executes our xml request

$stop = array_sum(explode(' ', microtime()));

$totalTime = $stop - $start;



// It is always a good practice to check for errors
// In my case, i just output them for visual review/debugging

if (curl_errno($ch)) {

    $result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
} else {

    $returnCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE); //checks for url errors

    switch ($returnCode) {

        case 404:

            $result = 'ERROR -> 404 Not Found';

            break;

        default:

            break;
    }
}



// Once the execution is done close the handle

curl_close($ch);



// (Optional) Output the results and time

echo '</br>';

echo 'Total time for request: ' . $totalTime . "\n";

echo "Submit Result" . $result;





//*****************************************************
// 3. After we have sucessfuly sent the request over to
// the API, we need to receive a result. Usually, result
// is available several seconds later, se we would need
// to query the API with received job ID. Or, you can
// build a webpage that will alert you once the links
// to the new generated files are ready.
//*****************************************************
//Let's start by reading the xml we received as a result
// of executing curl xml request

$dom = new DOMDocument;

$dom->loadXML($result);

$xml = simplexml_import_dom($dom); // we are using simplexml here

$jobURL = $xml->JobURL; // a job URL has been obtained
// Now that we have URL for our pending job,
// we will querry it untill we recieve a status
// indicating that the job is done.

$i = 0;

$status = 2;

while ($status > 0) { //status = 0, means we are done.
    $reader = new XMLReader();  //we are using XMLReader function

    $reader->open($jobURL);

    while ($reader->read()) { // if we have more than one URI, this while will take care of it.
        if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'Status') { //find status xml element
            $reader->read(); // go to its value

            if ($reader->value == 'Finished') { //if the status is finished, we will read one more time to receive URI(s)
                $status = $status - 1; // indicates that we are ready to read URI, since the job is finished
            }
        }

        if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'Uri' && $status == 1) { //This is where the link to the new file(s) is located
            $reader->read();

            $url[$i] = $reader->value; // we are creating array, if necessary, for storing URI

            $i++;
        }
    }

    sleep(3); // wait 3 seconds before doing another querry
}

$reader->close(); // close the xml reader
// (Optional) Output the URI of files you have requested
// If you requested more or less filetypes, change output accordingly

echo '<br/> JobTXT: ' . $url[0];

echo '<br/> JobPDF: ' . $url[1];
?>