<?php
/**
 * GribMessage class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/MIT The MIT License
 */

require_once('GribGridDescription.php');

/**
 * GribMessage is the PHP representation of a GRIB message.
 * 
 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/ Documentation on GRIB v1
 */
class GribMessage
{
	/**
	 * @var int Length of the GRIB message in bytes.
	 */
	public $messageLength;
	
	/**
	 * @var int The version or edition of the GRIB message, currently
	 * only version 1 is supported.
	 */
	public $messageVersion;
	
	/**
	 * @var int Version of the parameter table.
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/table2.html
	 */
	public $parameterTableVersion;
	
	/**
	 * @var int Identification of the center of origin for the message
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/table0.html
	 */
	public $originCenterId;
	
	/**
	 * @var int Identification of the generator process.
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/tablea.html
	 */
	public $originProcessId;
	
	/**
	 * @var int Identification of the GRID utilized.
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/tableb.html
	 */
	public $gridId;
	
	/**
	 * @var bool TRUE if the message contains a Grid Description Section
	 */
	public $hasGDS;
	
	/**
	 * @var bool TRUE if the message contains a Bitmap Section
	 */
	public $hasBMS;
	
	/**
	 * @var int Identification of the parameter and units contained 
	 * in this message as described in Table 2
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/table2.html
	 */
	public $parameterId;
	
	/**
	 * @var int Identification of the layer or level type used in this
	 * message.
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/table3.html
	 */
	public $levelTypeId;
	
	/**
	 * @var int Value of the level or layer in height, pressure, etc.
	 */
	public $levelValue;
	
	/**
	 * @var int Timestamp of the reference (initial) time for the forecast
	 * or start of time period for averaging or accumulation analyses.
	 */
	public $referenceTime;
	
	/**
	 * @var Identification of the time unit.
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/table4.html
	 */
	public $timeUnit;
	
	/**
	 * @var int P1 - Period of Time (in units given by the property $timeUnit).
	 */
	public $timePeriod1;
	
	/**
	 * @var int P2 - Period of Time or time interval between sucessive analyses.
	 */
	public $timePeriod2;
	
	/**
	 * @var int Time range indicator
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/table5.html
	 */
	public $timeRangeIndicator;
	
	/**
	 * @var int Number included in average whe $timeRangeIndicator indicates
	 * a average or accumulation.
	 */
	public $avgNumberIncluded;
	
	/**
	 * @var int Number missing from averages or accumulation.
	 */
	public $avgNumberMissing;
	
	/**
	 * @var int Identificator of the origin sub-center
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/tablec.html
	 */
	public $originSubcenterId;
	
	/**
	 * @var int The decimal scale factor
	 */
	public $decimalScaleFactor;
	
	/**
	 * @var bool Indicates when the original data in integer.
	 */
	public $dataIsInteger;
	
	/**
	 * @var int Number of unused bytes at end of Binary Data Section.
	 */
	public $unusedBytes;
	
	/**
	 * @var int The binary scale factor used to unpack data.
	 */
	public $binaryScaleFactor;
	
	/**
	 * @var float The reference (base) value used to unpack data.
	 */
	public $referenceValue;
	
	/**
	 * @var int Number of bits used to store each point in packed data.
	 */
	public $pointDataLength;
	
	/**
	 * @var string The raw packed data extracted from Binary Data Section. 
	 */
	public $rawData;
	
	/**
	 * @var int The grid type described in Grid Description Section
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/section2.html
	 */
	public $gridRepresentationType;
	
	/**
	 * @var GribGridDescription The grid description extracted from Grid
	 * Description Section.
	 * @link http://www.nco.ncep.noaa.gov/pmb/docs/on388/tabled.html
	 */
	public $gridDescription;
	
	/**
	 * Get data at specified index from the raw binary data.
	 * Data is returned unpacked. Currently this function only
	 * supports the simple packing algorithm.
	 * 
	 * @param int $index Index of the data
	 * @return float The unpacked data as a float point value
	 */
	public function getDataAt($index)
	{
		/*
		 * If zero bits are need for packing so all points got the same value
		 * of the reference value.
		 */
		if ($this->pointDataLength == 0)
			return $this->referenceValue;
		
		$bitIndex = $index*$this->pointDataLength;
		$valueStart = floor($bitIndex/8);
		$byteLength = ceil(($bitIndex+$this->pointDataLength)/8)-$valueStart;
		$maskOffset = $bitIndex % 8;
		$mask = (pow(2,$this->pointDataLength)-1) << $maskOffset;
		
		$valueData = unpack('C*', substr($this->rawData, $valueStart, $byteLength));
		
		foreach ($valueData as $key => $byteValue) {
			if ($key == 1) {
				$intValue = $byteValue;
			} else {
				$intValue = ($intValue << 8) + $byteValue;
			}
		}
		$packedValue = ($mask & $intValue) >> $maskOffset;
		
		
		return ($this->referenceValue + ($packedValue * pow(2,  $this->binaryScaleFactor)));
	}
	
	/**
	 * Return the coordinates for a point at given index.
	 * 
	 * @param int $index The index for a point in the grid.
	 * @return int[] A array containing the latitude/longitude for the
	 * given index.
	 */
	public function getPointAt($index)
	{
		if (!$this->gridDescription)
			throw new Exception('Grid description not found!');
		
		if ($this->gridRepresentationType != 0)
			throw new Exception('Sorry! Only equidistant grids are supported right now!');
		
		
		return $this->gridDescription->getPointCoordinates($index);
	}
}