<?php
/**
 * Read the topography parameter message from GRIB file and
 * generate a map representation from the message values
 * the first for loop looks for the max and min values in
 * the GRIB message.
 * The second for loop generates the image.
 */

require_once('../decoder/GribFileDecoder.php');

$messages = GribFileDecoder::loadFile(
    'example.grb',
    array(
        'parameters'=>array(132), //Topography
    ));


if (!sizeof($messages))
    die('Topography parameter not found in file!');

$message = $messages[0];

$image = array();

$max = $message->getDataAt(0);
$min = $max;

for ($i=0;$i<$message->gridDescription->latitudePoints*$message->gridDescription->longitudePoints;$i++) {
    list($lat,$lon) = $message->getPointAt($i);
    $y = $message->gridDescription->latitudePoints - ($lat - $message->gridDescription->latitudeFirstPoint) / $message->gridDescription->latitudinalIncrement;
    $x = ($lon - $message->gridDescription->longitudeFirstPoint) / $message->gridDescription->longitudinalIncrement;
    $data = floor($message->getDataAt($i));

    $image[$x][$y] = $data;

    if ($data > $max) $max = $data;
    if ($data < $min) $min = $data;
}

$img = imagecreatetruecolor($message->gridDescription->longitudePoints, $message->gridDescription->latitudePoints);
$white = imagecolorallocate($img, 255,255,255);
for ($i=0;$i<256;$i++) {
    $colors[$i] = imagecolorallocate($img,$i,$i,$i);
}
imagefill($img,0,0,$white);
$max = $max - $min;
foreach ($image as $x => &$row) {
    foreach ($row as $y => &$cell) {
        $cell = (int) ((($cell - $min) / $max) * 255);
        imagesetpixel($img,$x,$y,$colors[$cell]);
    }
}

imagepng($img, 'topoMap.png');
imagedestroy($img);
