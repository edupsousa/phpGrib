<?php
require_once('GribMessage.php');

class GribParserException extends Exception
{
	const UNABLE_TO_OPEN_FILE = 0x1;
	const UNSUPPORTED_GRIB_VERSION = 0x2;
	const MESSAGE_TOO_SHORT = 0x3;
	const INDICATOR_NOT_FOUND = 0x4;
	const MESSAGE_LENGHT_MISMATCH = 0x5;
}

class GribParser
{
	const MESSAGE_IDENTIFICATOR_LENGHT = 4;
	const MESSAGE_SIZE_FIELD_LENGHT = 3;
	const GRIB_VERSION_FIELD_LENGHT = 1;
	
	const FLAG_HAS_GDS = 128;
	const FLAG_HAS_BMS = 64;
	
	public function parseFile($path)
	{
		$handle = fopen($path,'rb');
		if (!$handle)
			throw new GribParserException('',  GribParserException::UNABLE_TO_OPEN_FILE);
		
		$messages = array();
		while ($this->_fileHasGribMessage($handle)) {
			$messageSize = $this->_readMessageSizeFromFile($handle);
			$gribVersion = ord($this->_readStringFromFile($handle, self::GRIB_VERSION_FIELD_LENGHT));
			
			if ($gribVersion != 1)
				throw new GribParserException('', GribParserException::UNSUPPORTED_GRIB_VERSION);

				/*
				 * Rewind file pointer to the beginning of Indicator Section 
				 * to read entire GRIB message for parsing.
				 */
				fseek($handle,
					-(self::MESSAGE_IDENTIFICATOR_LENGHT + 
					  self::MESSAGE_SIZE_FIELD_LENGHT +
					  self::GRIB_VERSION_FIELD_LENGHT
					),
					SEEK_CUR);
				$gribMessage = $this->_readStringFromFile($handle, $messageSize);
				$messages[] = $this->parseMessage($gribMessage);
		}
		fclose($handle);
		return $messages;
	}
	
	public function parseMessage($message)
	{
		$currentPosition = 0;
		$messageLength = strlen($message);
		if ($messageLength < 8)
			throw new GribParserException('', GribParserException::MESSAGE_TOO_SHORT);

		$rawIndicatorSection = $this->_getRawSectionFromMessage($message, $currentPosition, 8);
		$indicatorSection = $this->_parseIndicatorSection($rawIndicatorSection);
		
		if ($indicatorSection->messageLength != $messageLength)
			throw new GribParserException('', GribParserException::MESSAGE_LENGHT_MISMATCH);
		
		$rawProductDefinitionSection = $this->_getRawSectionFromMessage($message, $currentPosition);
		$productDefinitionSection = $this->_parseProductDefinitionSection($rawProductDefinitionSection);
		
		if ($productDefinitionSection->hasGDS) {
			$rawGridDescriptionSection = $this->_getRawSectionFromMessage($message, $currentPosition);
			$gridDescriptionSection = $this->_parseGridDescriptionSection($rawGridDescriptionSection);
		} else {
			$gridDescriptionSection = null;
		}
		
		if ($productDefinitionSection->hasBMS) {
			throw new GribParserException('BMS parser not implemented!');
		}
		
		$rawBinaryDataSection = $this->_getRawSectionFromMessage($message, $currentPosition);
		$binaryDataSection = $this->_parseBinaryDataSection($rawBinaryDataSection);
		
		$messageSection = new GribMessage();
		$messageSection->indicatorSection = $indicatorSection;
		$messageSection->productDefinitionSection = $productDefinitionSection;
		$messageSection->gridDescriptionSection = $gridDescriptionSection;
		$messageSection->binaryDataSection = $binaryDataSection;
		
		return $messageSection;
	}
	
	protected function _parseBinaryDataSection($rawData)
	{
		$section = new GribBinaryDataSection();
		$section->sectionLength = $this->_substringToUInt($rawData, 0, 3);
		
		$isHarmonicPacking = $this->_isFlagSet(128, $rawData, 3);
		$isComplexPacking = $this->_isFlagSet(64, $rawData, 3);
		
		if (!$isHarmonicPacking && !$isComplexPacking) {
			$section->packingFormat = GribBinaryDataSection::SIMPLE_PACKING;
		} else if ($isHarmonicPacking && !$isComplexPacking) {
			$section->packingFormat = GribBinaryDataSection::HARMONIC_SIMPLE_PACKING;
		} else if ($isComplexPacking && !$isHarmonicPacking) {
			$section->packingFormat = GribBinaryDataSection::COMPLEX_PACKING;
		} else {
			throw new GribParserException('Invalid packing method! Harmonic complex?');
		}
		
		$section->originalDataWereInteger = $this->_isFlagSet(32, $rawData, 3);
		$section->hasAdditionalFlags = $this->_isFlagSet(16, $rawData, 3);
		$section->unusedBytesAtEnd = ($this->_substringToUInt(3, 3, 1)) & 15;
		$section->binaryScaleFactor = $this->_substringToSignedInt($rawData, 4, 2);
		$section->referenceValue = $this->_substringToSingle($rawData, 6);
		$section->datumPackingBits = $this->_substringToUInt($rawData, 10, 1);
		
		if ($section->packingFormat == GribBinaryDataSection::SIMPLE_PACKING) {
			$section->rawBinaryData = substr($rawData, 11);
		} else if ($section->packingFormat == GribBinaryDataSection::HARMONIC_SIMPLE_PACKING) {
			throw new GribParserException('Harmonic packing parser not implemented!');
		} else {
			throw new GribParserException('Complex packing parser not implemented!');
		}
		
		return $section;
	}
	
	protected function _parseGridDescriptionSection($rawData)
	{
		$section = new GribGridDescriptionSection();
		$section->sectionLength = $this->_substringToUInt($rawData, 0, 3);
		$section->numberOfVerticalCoordinateParameters = $this->_substringToUInt($rawData, 3, 1);
		$section->pvOrPl = $this->_substringToUInt($rawData, 4, 1);
		$section->dataRepresentationType = $this->_substringToUInt($rawData, 5, 1);
		
		if ($section->dataRepresentationType == 0) {
			$gridDescription = substr($rawData, 6);
			$section->gridDescription = $this->_parseLatLonGridDescription($gridDescription);
		} else {
			throw new GribParserException('Not implemented!!');
		}
		
		return $section;
	}
	
	protected function _parseLatLonGridDescription($rawData)
	{
		$description = new GribLatLonGridDescription();
		
		$description->pointsAlongLatitude = $this->_substringToUInt($rawData, 0, 2);
		$description->pointsAlongLongitude = $this->_substringToUInt($rawData, 2, 2);
		
		list($description->latitudeFirstPointIsSouth, $description->latitudeFirstPoint)
				= $this->_getLatLonWithHemisphere($rawData, 4);
		list($description->longitudeFirstPointIsWest, $description->longitudeFirstPoint)
				= $this->_getLatLonWithHemisphere($rawData, 7);
		
		$description->directionIncrementGiven = $this->_isFlagSet(128, $rawData, 10);
		$description->earthModel = ($this->_isFlagSet(64, $rawData, 10) ?
			GribLatLonGridDescription::EARTH_SPHEROID : 
			GribLatLonGridDescription::EARTH_SPHERICAL);
		
		$description->componentsDirection = ($this->_isFlagSet(8, $rawData, 10) ?
			GribLatLonGridDescription::DIRECTION_BY_GRID : 
			GribLatLonGridDescription::DIRECTION_NORTH_EAST);
		
		list($description->latitudeLastPointIsSouth, $description->latitudeLastPoint)
				= $this->_getLatLonWithHemisphere($rawData, 11);
		list($description->longitudeLastPointIsWest, $description->longitudeLastPoint)
				= $this->_getLatLonWithHemisphere($rawData, 14);
		
		$description->longitudinalIncrement = $this->_substringToUInt($rawData, 17, 2);
		$description->latitudinalIncrement = $this->_substringToUInt($rawData, 19, 2);
		
		$description->scanNegativeI = $this->_isFlagSet(128, $rawData, 21);
		$description->scanNegativeJ = $this->_isFlagSet(64, $rawData, 21);
		$description->scanJConsecutive = $this->_isFlagSet(32, $rawData, 21);
		
		return $description;
	}
	
	protected function _getLatLonWithHemisphere($string, $position)
	{
		$coord = $this->_substringToSignedInt($string, $position, 3);
		if ($coord < 0) 
			$flag = true;
		else
			$flag = false;
		
		return array($flag, abs($coord));
	}

	protected function _parseProductDefinitionSection($rawData)
	{
		$section = new GribProductDefinitionSection();
		$section->sectionLength = $this->_substringToUInt($rawData, 0, 3);
		$section->parameterTableVersion = $this->_substringToUInt($rawData, 3, 1);
		$section->centerIdentification = $this->_substringToUInt($rawData, 4, 1);
		$section->generatingProcessId = $this->_substringToUInt($rawData, 5, 1);
		$section->gridIdentification = $this->_substringToUInt($rawData, 6, 1);
		$section->hasGDS = $this->_isFlagSet(self::FLAG_HAS_GDS, $rawData, 7);
		$section->hasBMS = $this->_isFlagSet(self::FLAG_HAS_BMS, $rawData, 7);
		$section->parameterAndUnits = $this->_substringToUInt($rawData, 8, 1);
		$section->typeOfLayerOrLevel = $this->_substringToUInt($rawData, 9, 1);
		$section->layerOrLevelValue = $this->_substringToUInt($rawData, 10, 2);
		$section->year = $this->_substringToUInt($rawData, 12, 1);
		$section->month = $this->_substringToUInt($rawData, 13, 1);
		$section->day = $this->_substringToUInt($rawData, 14, 1);
		$section->hour = $this->_substringToUInt($rawData, 15, 1);
		$section->minute = $this->_substringToUInt($rawData, 16, 1);
		$section->forecastTimeUnit = $this->_substringToUInt($rawData, 17, 1);
		$section->periodOfTime1 = $this->_substringToUInt($rawData, 18, 1);
		$section->periodOfTime2 = $this->_substringToUInt($rawData, 19, 1);
		$section->timeRangeIndicator = $this->_substringToUInt($rawData, 20, 1);
		$section->numberIncluded = $this->_substringToUInt($rawData, 21, 2);
		$section->numberMissing = $this->_substringToUInt($rawData, 23, 1);
		$section->century = $this->_substringToUInt($rawData, 24, 1);
		$section->subcenterIdentification = $this->_substringToUInt($rawData, 25, 1);
		$section->decimalScaleFactor = $this->_substringToUInt($rawData, 26, 2);
		
		if ($section->sectionLength > 28)
			$section->reserved = substr($rawData, 28);
		
		return $section;
	}

	protected function _getRawSectionFromMessage($message, &$currentPosition, $length = false)
	{
		if ($length === false)
			$length = $this->_getNextSectionLength($message, $currentPosition);
		
		$raw = substr($message, $currentPosition, $length);
		$currentPosition += $length;
		return $raw;
	}
	
	protected function _getNextSectionLength($message, $currentPosition)
	{
		return $this->_stringToUInt(substr($message, $currentPosition, 3));
	}
	
	protected function _parseIndicatorSection($rawData)
	{
		$section = new GribIndicatorSection();
		
		$section->gribIndicator = substr($rawData, 0, 4);
		$section->messageLength = $this->_stringToUInt(substr($rawData, 4, 3));
		$section->editionNumber = $this->_stringToUInt(substr($rawData, 7, 1));
		
		if ($section->gribIndicator != 'GRIB')
			throw new GribParserException('', GribParserException::INDICATOR_NOT_FOUND);
		
		if ($section->editionNumber != 1)
			throw new GribParserException('', GribParserException::UNSUPPORTED_GRIB_VERSION);
		
		return $section;
	}
	
	protected function _isFlagSet($flag, $string, $position)
	{
		$byte = ord(substr($string, $position, 1));
		return (($byte & $flag) == $flag);
	}
	
	protected function _substringToUInt($string, $start, $length)
	{
		return $this->_stringToUInt(substr($string, $start, $length));
	}
	
	protected function _stringToUInt($string)
	{
		$string = unpack('H*value', $string);
		return hexdec($string['value']);
	}
	
	protected function _substringToSignedInt($string, $start, $length)
	{
		$uInt = $this->_substringToUInt($string, $start, $length);
		$signal = $uInt & (1 << (8 * $length)-1);
		$value = $uInt & ~((1 << (8 * $length)-1));
		return ($signal ? -$value : $value);
	}
	
	protected function _substringToSingle($string, $position)
	{
		
		$A = $this->_substringToSignedInt($string, $position, 1);
		$B = $this->_substringToUInt($string, $position+1, 3);
		return pow(2, -24) * $B * pow(16, $A-64);
	}

	protected function _fileHasGribMessage($handle)
	{
		if ($this->_readStringFromFile($handle, self::MESSAGE_IDENTIFICATOR_LENGHT) == 'GRIB')
			return true;
		return false;
	}
	
	protected function _readStringFromFile($handle, $length)
	{
		return fread($handle, $length);
	}
	
	protected function _readMessageSizeFromFile($handle)
	{
		$raw = fread($handle, self::MESSAGE_SIZE_FIELD_LENGHT);
		$data = unpack('H6size',$raw);
		return hexdec($data['size']);
	}
}