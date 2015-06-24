<?php
/**
 * Read the land/sea mask parameter message from GRIB file and
 * generate a map representation from the message values
 */

require_once('../decoder/GribFileDecoder.php');

$messages = GribFileDecoder::loadFile(
    'example.grb',
    array(
        'parameters'=>array(81), //Land/Sea flag
    ));


if (!sizeof($messages))
    die('Land/Sea parameter not found in file!');

$message = $messages[0];

$img = imagecreatetruecolor($message->gridDescription->longitudePoints, $message->gridDescription->latitudePoints);

$black = imagecolorallocate($img, 0,0,0);
$white = imagecolorallocate($img, 255,255,255);

for ($i=0;$i<$message->gridDescription->latitudePoints*$message->gridDescription->longitudePoints;$i++) {
    list($lat,$lon) = $message->getPointAt($i);
    $lat = $message->gridDescription->latitudePoints - ($lat - $message->gridDescription->latitudeFirstPoint) / $message->gridDescription->latitudinalIncrement;
    $lon = ($lon - $message->gridDescription->longitudeFirstPoint) / $message->gridDescription->longitudinalIncrement;
    $data = floor($message->getDataAt($i));

    if ($data) {
        imagesetpixel($img, $lon, $lat, $white);
    }
}

imagepng($img, 'landSeaMap.png');
imagedestroy($img);