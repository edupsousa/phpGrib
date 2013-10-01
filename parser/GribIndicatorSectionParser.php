<?php
/**
 * GribIndicatorSectionParser class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribParser.php');

/**
 * GribIndicatorSectionParser is used to parse the Indicator Section (IS)
 * from a binary string.
 */
class GribIndicatorSectionParser extends GribParser
{
	/**
	 * Parse a binary string containing the Indicator Section (IS) from a
	 * GRIB Message. Return a GribIndicatorSection on success or throw a
	 * GribParserException on error.
	 * 
	 * @param string $rawData The binary string to parse
	 * @return GribIndicatorSection The Indicator Section representation
	 * @throws GribParserException
	 */
	public static function parse($rawData)
	{
		$section = new GribIndicatorSection();
		
		$section->gribIndicator = substr($rawData, 0, 4);
		$section->messageLength = self::_getUInt($rawData, 4, 3);
		$section->editionNumber = self::_getUInt($rawData, 7, 1);
		
		if ($section->gribIndicator != 'GRIB')
			throw new GribParserException('', GribParserException::INDICATOR_NOT_FOUND);
		
		if ($section->editionNumber != 1)
			throw new GribParserException('', GribParserException::UNSUPPORTED_GRIB_VERSION);
		
		return $section;
	}
}