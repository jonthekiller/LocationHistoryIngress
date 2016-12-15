<?php

//error_reporting(0);
require('xmlreader-iterators.php');

$file = "process/".$argv[1];
$file2 = $argv[1];
$starttime = time();

echo "Start Compression at " . date("H:i:s") ."\n";

echo "Remove Useless Parts\n";

//Remove : character to simplify the parsing
$xml = file_get_contents($file);
$xml = str_replace("gx:Track", "gxTrack", $xml);
$xml = str_replace("gx:coord", "gxcoord", $xml);
file_put_contents($file,$xml);

$maps = simplexml_load_file($file);

//Remove the date
unset($maps->Document->Placemark->gxTrack->when);

//Count the number of points
$l = count($maps->Document->Placemark->gxTrack->gxcoord);

$i=0;

$maps->asXML($file);

echo "Number of coordinates at origin: ".$l."\n";

//Initialize the generate file
$Data = '<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2"><Document>';

$coordinatestable= array();

$numremove = 0;
$numkeep = 0;
$numduplicate = 0;

$count = 0;

//Take number of lines already done
$countfile = "./finished/".$file2.".count";
$count = file_get_contents($countfile);
echo "Number already done: ".$count . "\n";

//Open the file with XMLReader parser (for performance)
$reader = new XMLReader();
$reader->open($file);

$itemIterator = new XMLElementIterator($reader, 'gxcoord');

//Loop the file
foreach ($itemIterator as $item)
{
   $xml     = $item->getSimpleXMLElement();

   $i++;
        //Log each 10000 to see the progress
     if(($i % 10000) == 0)
     {
        echo "Line ".$i." at " . date("H:i:s") ."\n";
     }
        //Loop only new lines
        if ($i < ($l - $count))
        {
            $coordinates = explode(' ', $xml);
                //First line
            if (count($coordinatestable) == 0)
            {
                $coordinatestable[] = array($coordinates[0],$coordinates[1]);
                $Data.="<Placemark><Point><coordinates>".$coordinates[0].",".$coordinates[1]."</coordinates></Point></Placemark>";
                $numkeep++;
            }else{
                $duplicate =0;
                //Compare the new line with older to remove duplicate points
                foreach ($coordinatestable as $coordinatetable)
                {
                    $theta = $coordinatetable[1] - $coordinates[1];
                    $dist = sin(deg2rad($coordinatetable[0])) * sin(deg2rad($coordinates[0])) +  cos(deg2rad($coordinatetable[0])) * cos(deg2rad($coordinates[0])) * cos(deg2rad($theta));
                    $dist = acos($dist);
                    $dist = rad2deg($dist);
                    $miles = $dist * 60 * 1.1515;
                    $distance = ($miles * 1.609344*1000);
                        //If < 10m, then remove
                    if ($distance < 10)
                    {
                            $numduplicate++;
                            $duplicate =1;
                            break;
                    }
                    $long = $coordinates[0];
                    $lat = $coordinates[1];
                }
                //If not a duplicate, add the line and update the compare table
                if ($duplicate == 0)
                {
                    $coordinatestableadd= array();
                    $coordinatestableadd[] = array($long,$lat);
                    $coordinatestable = array_merge($coordinatestable,$coordinatestableadd);
                    $Data.="<Placemark><Point><coordinates>".$coordinates[0].",".$coordinates[1]."</coordinates></Point></Placemark>";
                    $numkeep++;
                }
            }
        }else{
                break;
        }

}


//Finish the generated file
$Data.= '</Document></kml>';


//Name of the output file
$OutputFile = "./finished/".$file2;
$Open = fopen ($OutputFile, "w"); //Use "w" to start a new output file from zero. If you want to increment an existing file, use "a".

fwrite ($Open, $Data);


//Close the file stream
fclose ($Open);


echo "Begin of merge\n";

//Merge the template and update file
$doc1 = new DOMDocument();
$doc1->load("./templates/".$file2);

$doc2 = new DOMDocument();
$doc2->load($OutputFile);

// get 'res' element of document 1
$res1 = $doc1->getElementsByTagName('Document')->item(0); //edited res - items

// iterate over 'item' elements of document 2
$items2 = $doc2->getElementsByTagName('Placemark');
for ($i = 0; $i < $items2->length; $i ++) {
    $item2 = $items2->item($i);

    // import/copy item from document 2 to document 1
    $item1 = $doc1->importNode($item2, true);

    // append imported item to document 1 'res' element
    $res1->appendChild($item1);

}
$doc1->save($OutputFile); //edited -added saving into xml file


//Generate time passed to generate the file

$endtime = time();
$diff = abs($endtime - $starttime);
$retour = array();

$tmp = $diff;
$retour['second'] = $tmp % 60;

$tmp = floor( ($tmp - $retour['second']) /60 );
$retour['minute'] = $tmp % 60;

$tmp = floor( ($tmp - $retour['minute'])/60 );
$retour['hour'] = $tmp % 24;

$tmp = floor( ($tmp - $retour['hour'])  /24 );
$retour['day'] = $tmp;

//Write the result in a file to send by mail when finished
$OutputFile = "./finished/".$file2.".txt";
$Open = fopen ($OutputFile, "w");

$total = $numkeep + $numremove + $numduplicate;

$Data = "File ready at " . date("H:i:s") ."<br>\n";
$Data .= "Number of points: " . $total . "<br>\n";
$Data .= "Number of kept: " . $numkeep . " : " . number_format($numkeep/$total*100,1) . "%<br>\n";
$Data .= "Number of removed: " . $numremove . " : " . number_format($numremove/$total*100,1) . "%<br>\n";
$Data .= "Number of duplicated: " . $numduplicate . " : " . number_format($numduplicate/$total*100,1) . "%<br>\n";
$Data .= "It tooks " . $retour['hour'] . " hours " . $retour['minute'] . " minutes " . $retour['second'] . " seconds.";

echo $Data;

fwrite ($Open, $Data);
fclose ($Open);


?>

