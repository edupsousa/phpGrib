<?php
/*
 * Describe all GRIB messages contained in the example file.
 */

require_once '../decoder/GribFileDecoder.php';
require_once '../tables/GribParametersCPTEC.php';

//Load all messages in GRIB file (no filter)
$messages = GribFileDecoder::loadFile('example.grb');

foreach ($messages as $index => $message) {
    $levelType = $message->levelTypeId;
    $levelValue = $message->levelValue;
    $parameterId = $message->parameterId;
    $parameterDescription =
        "(" . $parameterId . " " .
        GribParametersCPTEC::getParameterAbbreviation($parameterId) . ") " .
        GribParametersCPTEC::getParameterDescription($parameterId);

    echo "Message {$index} contains parameter: {$parameterDescription} at level {$levelValue} of type {$levelType}.\n";

}

