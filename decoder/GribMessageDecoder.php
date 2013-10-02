<?php
/**
 * GribMessageDecoder class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribDecoder.php');

/**
 * GribMessageDecoder is used to decode a GRIB message from a binary string.
 */
class GribMessageDecoder extends GribDecoder
{
	/**
	 * Decode a binary string containing a encoded GRIB message and return a
	 * GribMessage object in case of sucessfull decoding.
	 * 
	 * @param string $rawData The binary string to decode
	 * @return GribMessage The GRIB message representation.
	 * @throws GribDecoderException
	 */
	public static function decode($rawData)
	{
		$currentPosition = 0;
		$rawDataLength = strlen($rawData);
		if ($rawDataLength < 8)
			throw new GribDecoderException('', GribDecoderException::MESSAGE_TOO_SHORT);

		$rawIndicatorSection = self::_getRawSectionFromMessage($rawData, $currentPosition, 8);
		$indicatorSection = GribIndicatorSectionDecoder::decode($rawIndicatorSection);
		
		if ($indicatorSection->messageLength != $rawDataLength)
			throw new GribDecoderException('', GribDecoderException::MESSAGE_LENGHT_MISMATCH);
		
		$rawProductDefinitionSection = self::_getRawSectionFromMessage($rawData, $currentPosition);
		$productDefinitionSection = GribProductDefinitionSectionDecoder::decode($rawProductDefinitionSection);
		
		if ($productDefinitionSection->hasGDS) {
			$rawGridDescriptionSection = self::_getRawSectionFromMessage($rawData, $currentPosition);
			$gridDescriptionSection = GribGridDescriptionSectionDecoder::decode($rawGridDescriptionSection);
		} else {
			$gridDescriptionSection = null;
		}
		
		if ($productDefinitionSection->hasBMS) {
			throw new GribDecoderException('BMS decoder not implemented!');
		}
		
		$rawBinaryDataSection = self::_getRawSectionFromMessage($rawData, $currentPosition);
		$binaryDataSection = GribBinaryDataSectionDecoder::decode($rawBinaryDataSection);
		
		$rawDataSection = new GribMessage();
		$rawDataSection->indicatorSection = $indicatorSection;
		$rawDataSection->productDefinitionSection = $productDefinitionSection;
		$rawDataSection->gridDescriptionSection = $gridDescriptionSection;
		$rawDataSection->binaryDataSection = $binaryDataSection;
		
		return $rawDataSection;
	}
}