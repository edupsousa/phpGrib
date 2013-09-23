<?php

class GribBinaryDataSection
{
	const SIMPLE_PACKING = 0;
	const HARMONIC_SIMPLE_PACKING = 1;
	const COMPLEX_PACKING = 2;
	
	public $sectionLength;
	public $packingFormat;
	public $originalDataWereInteger;
	public $hasAdditionalFlags;
	public $unusedBytesAtEnd;
	public $binaryScaleFactor;
	public $referenceValue;
	public $datumPackingBits;
	public $harmonicCoefficientRealPart;
	public $rawBinaryData;
}

class GribIndicatorSection
{
	public $gribIndicator;
	public $messageLength;
	public $editionNumber;
}

class GribProductDefinitionSection
{
	public $sectionLength;
	public $parameterTableVersion;
	public $centerIdentification;
	public $generatingProcessId;
	public $gridIdentification;
	public $hasGDS;
	public $hasBMS;
	public $parameterAndUnits;
	public $typeOfLayerOrLevel;
	public $layerOrLevelValue;
	public $year;
	public $month;
	public $day;
	public $hour;
	public $minute;
	public $forecastTimeUnit;
	public $periodOfTime1;
	public $periodOfTime2;
	public $timeRangeIndicator;
	public $numberIncluded;
	public $numberMissing;
	public $century;
	public $subcenterIdentification;
	public $decimalScaleFactor;
	public $reserved;
}

class GribGridDescriptionSection
{
	public $sectionLength;
	public $numberOfVerticalCoordinateParameters;
	public $pvOrPl;
	public $dataRepresentationType;
	public $gridDescription;
}

class GribGridDescription
{
	
}

class GribLatLonGridDescription extends GribGridDescription
{
	const EARTH_SPHERICAL = 0;
	const EARTH_SPHEROID = 1;
	
	const DIRECTION_NORTH_EAST = 0;
	const DIRECTION_BY_GRID = 1;
	
	public $pointsAlongLatitude;
	public $pointsAlongLongitude;
	
	public $latitudeFirstPoint;
	public $longitudeFirstPoint;
	
	public $directionIncrementGiven;
	public $earthModel;
	public $componentsDirection;
	
	public $latitudeLastPoint;
	public $longitudeLastPoint;
	
	public $longitudinalIncrement;
	public $latitudinalIncrement;
	
	public $scanNegativeI;
	public $scanNegativeJ;
	public $scanJConsecutive;
}

class GribMessage
{
	public $indicatorSection;
	public $productDefinitionSection;
	public $gridDescriptionSection;
	public $bitmapSection;
	public $binaryDataSection;
}