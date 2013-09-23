<?php
require_once('GribParser.php');

class GribIndicatorSectionParser extends GribParser
{
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