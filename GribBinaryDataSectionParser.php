<?php
/**
 * GribBinaryDataSectionParser class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribParser.php');

/**
 * GribBinaryDataSectionParser is used to parse the Binary Data Section
 * from a binary string.
 */
class GribBinaryDataSectionParser extends GribParser
{
	public static function parse($rawData)
	{
		$section = new GribBinaryDataSection();
		$section->sectionLength = self::_getUInt($rawData, 0, 3);
		
		$isHarmonicPacking = self::_isFlagSet(128, $rawData, 3);
		$isComplexPacking = self::_isFlagSet(64, $rawData, 3);
		
		if (!$isHarmonicPacking && !$isComplexPacking) {
			$section->packingFormat = GribBinaryDataSection::SIMPLE_PACKING;
		} else if ($isHarmonicPacking && !$isComplexPacking) {
			$section->packingFormat = GribBinaryDataSection::HARMONIC_SIMPLE_PACKING;
		} else if ($isComplexPacking && !$isHarmonicPacking) {
			$section->packingFormat = GribBinaryDataSection::COMPLEX_PACKING;
		} else {
			throw new GribParserException('Invalid packing method! Harmonic complex?');
		}
		
		$section->originalDataWereInteger = self::_isFlagSet(32, $rawData, 3);
		$section->hasAdditionalFlags = self::_isFlagSet(16, $rawData, 3);
		$section->unusedBytesAtEnd = (self::_getUInt(3, 3, 1)) & 15;
		$section->binaryScaleFactor = self::_getSignedInt($rawData, 4, 2);
		$section->referenceValue = self::_getSingle($rawData, 6);
		$section->datumPackingBits = self::_getUInt($rawData, 10, 1);
		
		if ($section->packingFormat == GribBinaryDataSection::SIMPLE_PACKING) {
			$section->rawBinaryData = substr($rawData, 11);
		} else if ($section->packingFormat == GribBinaryDataSection::HARMONIC_SIMPLE_PACKING) {
			throw new GribParserException('Harmonic packing parser not implemented!');
		} else {
			throw new GribParserException('Complex packing parser not implemented!');
		}
		
		return $section;
	}
}