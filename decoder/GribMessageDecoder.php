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

		$gribMessage = new GribMessage();
		
		$rawIndicatorSection = self::_getRawSectionFromMessage($rawData, $currentPosition, 8);
		self::decodeIndicatorSection($rawIndicatorSection, $gribMessage);
		
		if ($gribMessage->messageLength != $rawDataLength)
			throw new GribDecoderException('', GribDecoderException::MESSAGE_LENGHT_MISMATCH);
		
		$rawProductDefinitionSection = self::_getRawSectionFromMessage($rawData, $currentPosition);
		self::decodeProductDefinitionSection($rawProductDefinitionSection, $gribMessage);
		
		if ($gribMessage->hasGDS) {
			$rawGridDescriptionSection = self::_getRawSectionFromMessage($rawData, $currentPosition);
			self::decodeGridDescriptionSection($rawGridDescriptionSection, $gribMessage);
		} else {
			$gridDescriptionSection = null;
		}
		
		if ($gribMessage->hasBMS) {
			throw new GribDecoderException('BMS decoder not implemented!');
		}
		
		$rawBinaryDataSection = self::_getRawSectionFromMessage($rawData, $currentPosition);
		self::decodeBinaryDataSection($rawBinaryDataSection, $gribMessage);
		
		return $gribMessage;
	}

	/**
	 * Decode a binary string containing the Indicator Section (IS) from a
	 * GRIB Message. Return a GribIndicatorSection on success or throw a
	 * GribDecoderException on error.
	 * 
	 * @param string $rawData The binary string to decode
	 * @param GribMessage $message The GRIB message
	 * @return void
	 * @throws GribDecoderException
	 */
	protected static function decodeIndicatorSection($rawData, &$message)
	{
		if (substr($rawData, 0, 4) != 'GRIB')
			throw new GribDecoderException('', GribDecoderException::INDICATOR_NOT_FOUND);
		
		$message->messageLength = self::_getUInt($rawData, 4, 3);
		$message->messageVersion = self::_getUInt($rawData, 7, 1);
		
		if ($message->messageVersion != 1)
			throw new GribDecoderException('', GribDecoderException::UNSUPPORTED_GRIB_VERSION);
	}
	
	/**
	 * Decode a binary string containing the Product Definition Section (PDS) from a
	 * GRIB Message. Return a GribProductDefinitionSection on success or throw a
	 * GribDecoderException on error.
	 * 
	 * @param string $rawData The binary string to decode
	 * @param GribMessage $message
	 * @return GribProductDefinitionSection The Product Definition Section representation
	 * @throws GribDecoderException
	 */
	protected static function decodeProductDefinitionSection($rawData, &$message)
	{	
		$message->parameterTableVersion = self::_getUInt($rawData, 3, 1);
		$message->originCenterId = self::_getUInt($rawData, 4, 1);
		$message->originProcessId = self::_getUInt($rawData, 5, 1);
		$message->gridId = self::_getUInt($rawData, 6, 1);
		
		$message->hasGDS = self::_isFlagSet(128, $rawData, 7);
		$message->hasBMS = self::_isFlagSet(64, $rawData, 7);
		
		$message->parameterId = self::_getUInt($rawData, 8, 1);
		$message->levelTypeId = self::_getUInt($rawData, 9, 1);
		$message->levelValue = self::_getUInt($rawData, 10, 2);
		
		$century = self::_getUInt($rawData, 24, 1);
		$year = self::_getUInt($rawData, 12, 1);
		$month = self::_getUInt($rawData, 13, 1);
		$day = self::_getUInt($rawData, 14, 1);
		$hour = self::_getUInt($rawData, 15, 1);
		$minute = self::_getUInt($rawData, 16, 1);
		$message->referenceTime = mktime($hour,$minute,0,$month,$day,($century-1)*100+$year);
		
		$message->timeUnit = self::_getUInt($rawData, 17, 1);
		$message->timePeriod1 = self::_getUInt($rawData, 18, 1);
		$message->timePeriod2 = self::_getUInt($rawData, 19, 1);
		$message->timeRangeIndicator = self::_getUInt($rawData, 20, 1);
		
		$message->avgNumberIncluded = self::_getUInt($rawData, 21, 2);
		$message->avgNumberMissing = self::_getUInt($rawData, 23, 1);
		
		$message->originSubcenterId = self::_getUInt($rawData, 25, 1);
		$message->decimalScaleFactor = self::_getSignedInt($rawData, 26, 2);
	}

	/**
	 * Decode a binary string containing the Binary Data Section (BDS).
	 * Return a GribBinaryDataSection on success or throw a
	 * GribDecoderException on error.
	 * 
	 * @param string $rawData The binary string to decode
	 * @return GribBinaryDataSection The Binary Data Section representation
	 * @throws GribDecoderException
	 */
	protected static function decodeBinaryDataSection($rawData, &$message)
	{
		
		$isHarmonicCoefficients = self::_isFlagSet(128, $rawData, 3);
		$isComplexPacking = self::_isFlagSet(64, $rawData, 3);
		
		if ($isHarmonicCoefficients || $isComplexPacking)
			throw new GribDecoderException('',GribDecoderException::UNSUPPORTED_PACKING);
		
		$message->dataIsInteger = self::_isFlagSet(32, $rawData, 3);
		$message->unusedBytes = (self::_getUInt(3, 3, 1)) & 15;
		$message->binaryScaleFactor = self::_getSignedInt($rawData, 4, 2);
		$message->referenceValue = self::_getSingle($rawData, 6);
		$message->pointDataLength = self::_getUInt($rawData, 10, 1);
		$message->rawData = substr($rawData, 11);
	}
	
	protected static function decodeGridDescriptionSection($rawData, &$message)
	{
		$message->gridRepresentationType = self::_getUInt($rawData, 5, 1);
		
		//Plate Carree (0) grid
		if ($message->gridRepresentationType == 0) {
			$message->gridDescription = self::decodeLatLonGridDescription(substr($rawData, 6));
		} else {
			throw new GribDecoderException('',GribDecoderException::UNSUPPORTED_GRID);
		}
	}
	
	protected static function decodeLatLonGridDescription($rawData)
	{
		$description = new GribLatLonGridDescription();
		
		$description->longitudePoints = self::_getUInt($rawData, 0, 2);
		$description->latitudePoints = self::_getUInt($rawData, 2, 2);
		
		$description->latitudeFirstPoint = self::_getSignedInt($rawData, 4, 3);
		$description->longitudeFirstPoint = self::_getSignedInt($rawData, 7, 3);
		
		$description->latitudeLastPoint = self::_getSignedInt($rawData, 11, 3);
		$description->longitudeLastPoint = self::_getSignedInt($rawData, 14, 3);
		
		$description->incrementsGiven = self::_isFlagSet(128, $rawData, 10);
		$description->useOblateSpheroidFigure = self::_isFlagSet(64, $rawData, 10);
		
		$description->windComponentsAsGrid = self::_isFlagSet(8, $rawData, 10);
		
		$description->longitudinalIncrement = self::_getUInt($rawData, 17, 2);
		if ($description->longitudinalIncrement == 65535)
			$description->longitudinalIncrement = false;
		
		$description->latitudinalIncrement = self::_getUInt($rawData, 19, 2);
		if ($description->latitudinalIncrement == 65535)
			$description->latitudinalIncrement = false;
		
		$description->scanToWest = self::_isFlagSet(128, $rawData, 21);
		$description->scanToNorth = self::_isFlagSet(64, $rawData, 21);
		$description->scanLatitudeConsecutive = self::_isFlagSet(32, $rawData, 21);
		
		return $description;
	}
}