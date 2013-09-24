<?php
require_once('GribSection.php');

class GribProductDefinitionSection extends GribSection
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