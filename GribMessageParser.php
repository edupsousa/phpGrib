<?php
/**
 * GribMessageParser class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribParser.php');

class GribMessageParser extends GribParser
{
	public static function parse($rawData)
	{
		$currentPosition = 0;
		$rawDataLength = strlen($rawData);
		if ($rawDataLength < 8)
			throw new GribParserException('', GribParserException::MESSAGE_TOO_SHORT);

		$rawIndicatorSection = self::_getRawSectionFromMessage($rawData, $currentPosition, 8);
		$indicatorSection = GribIndicatorSectionParser::parse($rawIndicatorSection);
		
		if ($indicatorSection->messageLength != $rawDataLength)
			throw new GribParserException('', GribParserException::MESSAGE_LENGHT_MISMATCH);
		
		$rawProductDefinitionSection = self::_getRawSectionFromMessage($rawData, $currentPosition);
		$productDefinitionSection = GribProductDefinitionSectionParser::parse($rawProductDefinitionSection);
		
		if ($productDefinitionSection->hasGDS) {
			$rawGridDescriptionSection = self::_getRawSectionFromMessage($rawData, $currentPosition);
			$gridDescriptionSection = GribGridDescriptionSectionParser::parse($rawGridDescriptionSection);
		} else {
			$gridDescriptionSection = null;
		}
		
		if ($productDefinitionSection->hasBMS) {
			throw new GribParserException('BMS parser not implemented!');
		}
		
		$rawBinaryDataSection = self::_getRawSectionFromMessage($rawData, $currentPosition);
		$binaryDataSection = GribBinaryDataSectionParser::parse($rawBinaryDataSection);
		
		$rawDataSection = new GribMessage();
		$rawDataSection->indicatorSection = $indicatorSection;
		$rawDataSection->productDefinitionSection = $productDefinitionSection;
		$rawDataSection->gridDescriptionSection = $gridDescriptionSection;
		$rawDataSection->binaryDataSection = $binaryDataSection;
		
		return $rawDataSection;
	}
}