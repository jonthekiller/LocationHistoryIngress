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

$coordinatestableParis= array();

$numremove = 0;
$numkeep = 0;
$numduplicate = 0;

$numkeepParis = 0;
$numkeepoutParis = 0;

//Open the file with XMLReader parser (for performance)
$reader = new XMLReader();
$reader->open($file);

$itemIterator = new XMLElementIterator($reader, 'gxcoord');

$numberParis = 0;
$numberoutParis = 0;

//Loop the file
foreach ($itemIterator as $item)
{
   $xml     = $item->getSimpleXMLElement();

   $i++;
        //Log each 10000 to see the progress
     if(($i % 10000) == 0)
     {
        echo "Line ".$i." at " . date("H:i:s") ." with " .$numkeepParis. "/" .$numberParis. " points at Paris and " .$numkeepoutParis."/".$numberoutParis." points outside Paris\n";
     }
        $coordinates = explode(' ', $xml);

        //If Point at Paris
        if ($coordinates[1] < 48.70 || $coordinates[1] > 49.4 || $coordinates[0] < 1.90 || $coordinates[0] > 2.55)
//      if ($coordinates[1] < 48.65 || $coordinates[1] > 49.45 || $coordinates[0] < 1.85 || $coordinates[0] > 2.60)
        {
                $location = 0;
                $numberoutParis++;
        }else{
                $location = 1;
                $numberParis++;
        }

        if ($location == 0)
        {
            if (count($coordinatestable) == 0)
            {
                $coordinatestable[] = array($coordinates[0],$coordinates[1]);
                $Data.="<Placemark><Point><coordinates>".$coordinates[0].",".$coordinates[1]."</coordinates></Point></Placemark>";
                $numkeep++;
                $numkeepoutParis++;
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
                        //If < 20m, then remove
                    if ($distance < 20)
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
                    $numkeepoutParis++;
                }
            }
        }else{

        if (count($coordinatestableParis) == 0)
        {
                $coordinatestableParis[] = array($coordinates[0],$coordinates[1]);
                $Data.="<Placemark><Point><coordinates>".$coordinates[0].",".$coordinates[1]."</coordinates></Point></Placemark>";
                $numkeep++;
                $numkeepParis++;
        }else{
                $duplicate =0;
                //Compare the new line with older to remove duplicate points
                foreach ($coordinatestableParis as $coordinatetableParis)
                {
                    $theta = $coordinatetableParis[1] - $coordinates[1];
                    $dist = sin(deg2rad($coordinatetableParis[0])) * sin(deg2rad($coordinates[0])) +  cos(deg2rad($coordinatetableParis[0])) * cos(deg2rad($coordinates[0])) * cos(deg2rad($theta));
                    $dist = acos($dist);
                    $dist = rad2deg($dist);
                    $miles = $dist * 60 * 1.1515;
                    $distance = ($miles * 1.609344*1000);
                        //If < 50m, then remove
                    if ($distance < 50)
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
                    $coordinatestableParisadd= array();
                    $coordinatestableParisadd[] = array($long,$lat);
                    $coordinatestableParis = array_merge($coordinatestableParis,$coordinatestableParisadd);
                    $Data.="<Placemark><Point><coordinates>".$coordinates[0].",".$coordinates[1]."</coordinates></Point></Placemark>";
                    $numkeep++;
                    $numkeepParis++;
                }
            }

        }
}


//Finish the generated file
$Data.= '</Document></kml>';

echo "Number in Paris: ". $numberParis . "     Number out Paris: ".$numberoutParis."\n";

//Name of the output file
$OutputFile = "./finished/".$file2;
$Open = fopen ($OutputFile, "w"); //Use "w" to start a new output file from zero. If you want to increment an existing file, use "a".

fwrite ($Open, $Data);


//Close the file stream
fclose ($Open);

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

//Keep the total number of points for update mode
$countfile = "./finished/".$file2.".count";
$opencount = fopen ($countfile, "w");
fwrite ($opencount, $total);
fclose ($opencount);


?>

