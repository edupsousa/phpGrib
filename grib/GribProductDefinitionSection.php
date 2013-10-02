<?php
/**
 * GribProductDefinitionSection class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribSection.php');

/**
 * GribProductDefinitionSection represents the Product Definition Section (PDS)
 * from a GRIB message.
 */
class GribProductDefinitionSection extends GribSection
{
	/**
	 * @var int Length of the PDS
	 */
	public $sectionLength;
	
	/**
	 * @var int Version of the parameter table used
	 */
	public $parameterTableVersion;
	
	/**
	 * @var int Identification of the originating center
	 */
	public $centerIdentification;
	
	/**
	 * @var int ID of the processe originating the message (set by the center)
	 */
	public $generatingProcessId;
	
	/**
	 * @var int Grid identification
	 */
	public $gridIdentification;
	
	/**
	 * @var int Indicate if a Grid Definition Section is present
	 */
	public $hasGDS;
	
	/**
	 * @var int Indicate if the message has a Bitmap Section
	 */
	public $hasBMS;
	
	/**
	 * @var int ID of the parameter packed in this message
	 */
	public $parameterId;
	
	/**
	 * @var int Type of the layer or level used in this message
	 */
	public $typeOfLayerOrLevel;
	
	/**
	 * @var int Value indicating the layer or level
	 */
	public $layerOrLevelValue;
	
	/**
	 * @var int Year of the message
	 */
	public $year;
	
	/**
	 * @var int Month of the message
	 */
	public $month;
	
	/**
	 * @var int Day of the message
	 */
	public $day;
	
	/**
	 * @var int Hour of the message
	 */
	public $hour;
	
	/**
	 * @var int Minute of the message
	 */
	public $minute;
	
	/**
	 * @var int Identification of the time unit utilized
	 */
	public $forecastTimeUnit;
	
	/**
	 * @var int Period of time
	 */
	public $periodOfTime1;
	
	/**
	 * @var int Time interval between successive analyses
	 */
	public $periodOfTime2;
	
	/**
	 * @var int Time range indicator
	 */
	public $timeRangeIndicator;
	
	/**
	 * @var int Number included in the average when time range 
	 * indicate a average or accumulation
	 */
	public $numberIncluded;
	
	/**
	 * @var int Number missing in the average when time range 
	 * indicate a average or accumulation
	 */
	public $numberMissing;
	
	/**
	 * @var int Century of initial reference time
	 */
	public $century;
	
	/**
	 * @var int Identification of the sub-center
	 */
	public $subcenterIdentification;
	
	/**
	 * @var int The decimal scale factor (D)
	 */
	public $decimalScaleFactor;
	
	/**
	 * @var string Reserved for originating center usage
	 */
	public $reserved;
}