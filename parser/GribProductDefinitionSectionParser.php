<?php
/**
 * GribProductDefinitionSectionParser class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribParser.php');

/**
 * GribProductDefinitionSectionParser is used to parse the Product Definition
 * Section from a binary string.
 */
class GribProductDefinitionSectionParser extends GribParser
{
	const FLAG_HAS_GDS = 128;
	const FLAG_HAS_BMS = 64;
	
	/**
	 * Parse a binary string containing the Product Definition Section (PDS) from a
	 * GRIB Message. Return a GribProductDefinitionSection on success or throw a
	 * GribParserException on error.
	 * 
	 * @param string $rawData The binary string to parse
	 * @return GribProductDefinitionSection The Product Definition Section representation
	 * @throws GribParserException
	 */
	public static function parse($rawData)
	{
		$section = new GribProductDefinitionSection();
		$section->sectionLength = self::_getUInt($rawData, 0, 3);
		$section->parameterTableVersion = self::_getUInt($rawData, 3, 1);
		$section->centerIdentification = self::_getUInt($rawData, 4, 1);
		$section->generatingProcessId = self::_getUInt($rawData, 5, 1);
		$section->gridIdentification = self::_getUInt($rawData, 6, 1);
		$section->hasGDS = self::_isFlagSet(self::FLAG_HAS_GDS, $rawData, 7);
		$section->hasBMS = self::_isFlagSet(self::FLAG_HAS_BMS, $rawData, 7);
		$section->parameterId = self::_getUInt($rawData, 8, 1);
		$section->typeOfLayerOrLevel = self::_getUInt($rawData, 9, 1);
		$section->layerOrLevelValue = self::_getUInt($rawData, 10, 2);
		$section->year = self::_getUInt($rawData, 12, 1);
		$section->month = self::_getUInt($rawData, 13, 1);
		$section->day = self::_getUInt($rawData, 14, 1);
		$section->hour = self::_getUInt($rawData, 15, 1);
		$section->minute = self::_getUInt($rawData, 16, 1);
		$section->forecastTimeUnit = self::_getUInt($rawData, 17, 1);
		$section->periodOfTime1 = self::_getUInt($rawData, 18, 1);
		$section->periodOfTime2 = self::_getUInt($rawData, 19, 1);
		$section->timeRangeIndicator = self::_getUInt($rawData, 20, 1);
		$section->numberIncluded = self::_getUInt($rawData, 21, 2);
		$section->numberMissing = self::_getUInt($rawData, 23, 1);
		$section->century = self::_getUInt($rawData, 24, 1);
		$section->subcenterIdentification = self::_getUInt($rawData, 25, 1);
		$section->decimalScaleFactor = self::_getSignedInt($rawData, 26, 2);
		
		if ($section->sectionLength > 28)
			$section->reserved = substr($rawData, 28);
		
		return $section;
	}
}