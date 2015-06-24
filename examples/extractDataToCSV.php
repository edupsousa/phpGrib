<?php
/**
 * Extract data from GRIB files filtering absolute temperature parameter
 * level type 100 (isobaric) and level values 1000,750,500,250
 * then prints data in CSV format
 */

require_once '../decoder/GribFileDecoder.php';

$messages = GribFileDecoder::loadFile('example.grb',
    array(
        'parameters'=>array(11), //Absolute temperature in Kelvin
        'levelTypes'=>array(100), //Isobaric level
        'levelValues'=>array(1000,750,500,250)
    ));

echo "lat;lon;";
foreach($messages as $message) {
    echo $message->levelValue . ";";
}
echo "\n";

for ($x = $messages[0]->gridDescription->longitudePoints-1; $x>=0; $x--) {
    for ($y = 0; $y < $messages[0]->gridDescription->latitudePoints; $y++) {
        $i = $x * $messages[0]->gridDescription->latitudePoints + $y;
        list($lat, $lon) = $messages[0]->getPointAt($i);
        echo "{$lat};{$lon};";
        foreach ($messages as $message) {
            echo number_format($message->getDataAt($i),2,'.','') . ";";
        }
        echo "\n";
    }
}