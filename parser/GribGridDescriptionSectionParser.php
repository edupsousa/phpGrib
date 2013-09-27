<?php
/**
 * GribGridDescriptionSectionParser class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribParser.php');

/**
 * GribGridDescriptionSectionParser is used to parse the Grid Description 
 * Section (GDS) from a binary string.
 */
class GribGridDescriptionSectionParser extends GribParser
{
	
	/**
	 * Parse a binary string containing the Grid Description Section (GDS) from a
	 * GRIB Message. Return a GribGridDescriptionSection on success or throw a
	 * GribParserException on error.
	 * 
	 * @param string $rawData The binary string to parse
	 * @return GribGridDescriptionSection The Grid Description Section representation
	 * @throws GribParserException
	 */
	public static function parse($rawData)
	{
		$section = new GribGridDescriptionSection();
		$section->sectionLength = self::_getUInt($rawData, 0, 3);
		$section->numberOfVerticalCoordinateParameters = self::_getUInt($rawData, 3, 1);
		$section->pvOrPl = self::_getUInt($rawData, 4, 1);
		$section->dataRepresentationType = self::_getUInt($rawData, 5, 1);
		
		if ($section->dataRepresentationType == 0) {
			$gridDescription = substr($rawData, 6);
			$section->gridDescription = self::_parseLatLonGridDescription($gridDescription);
		} else {
			throw new GribParserException('Not implemented!!');
		}
		
		return $section;
	}
	
	/**
	 * Parse a latitude/longitude grid description from a binary string.
	 * 
	 * @param string $rawData The binary data containing a latitude/longitude
	 * grid description
	 * @return GribLatLonGridDescription A object representing the latitude/longitude
	 * grid description.
	 */
	protected static function _parseLatLonGridDescription($rawData)
	{
		$description = new GribLatLonGridDescription();
		
		$description->pointsAlongLatitude = self::_getUInt($rawData, 0, 2);
		$description->pointsAlongLongitude = self::_getUInt($rawData, 2, 2);
		
		$description->latitudeFirstPoint = self::_getSignedInt($rawData, 4, 3);
		$description->longitudeFirstPoint = self::_getSignedInt($rawData, 7, 3);
		
		$description->directionIncrementGiven = self::_isFlagSet(128, $rawData, 10);
		$description->earthModel = (self::_isFlagSet(64, $rawData, 10) ?
			GribLatLonGridDescription::EARTH_SPHEROID : 
			GribLatLonGridDescription::EARTH_SPHERICAL);
		
		$description->componentsDirection = (self::_isFlagSet(8, $rawData, 10) ?
			GribLatLonGridDescription::DIRECTION_BY_GRID : 
			GribLatLonGridDescription::DIRECTION_NORTH_EAST);
		
		$description->latitudeLastPoint = self::_getSignedInt($rawData, 11, 3);
		$description->longitudeLastPoint = self::_getSignedInt($rawData, 14, 3);
		
		$description->longitudinalIncrement = self::_getUInt($rawData, 17, 2);
		$description->latitudinalIncrement = self::_getUInt($rawData, 19, 2);
		
		$description->scanNegativeI = self::_isFlagSet(128, $rawData, 21);
		$description->scanNegativeJ = self::_isFlagSet(64, $rawData, 21);
		$description->scanJConsecutive = self::_isFlagSet(32, $rawData, 21);
		
		return $description;
	}
}