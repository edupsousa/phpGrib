<?php
/**
 * GribMessage class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribGridDescription.php');

/**
 * GribMessage is the PHP representation of a GRIB message.
 */
class GribMessage
{
	
	public $messageLength;
	public $messageVersion;
	
	public $parameterTableVersion;
	public $originCenterId;
	public $originProcessId;
	public $gridId;
	public $hasGDS;
	public $hasBMS;
	public $parameterId;
	public $levelTypeId;
	public $levelValue;
	public $referenceTime;
	public $timeUnit;
	public $timePeriod1;
	public $timePeriod2;
	public $timeRangeIndicator;
	public $avgNumberIncluded;
	public $avgNumberMissing;
	public $originSubcenterId;
	public $decimalScaleFactor;
	
	public $dataIsInteger;
	public $unusedBytes;
	public $binaryScaleFactor;
	public $referenceValue;
	public $pointDataLength;
	public $rawData;
	
	public $gridRepresentationType;
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
	
	public function getPointAt($index)
	{
		if (!$this->gridDescription)
			throw new Exception('Grid description not found!');
		
		if ($this->gridRepresentationType != 0)
			throw new Exception('Sorry! Only equidistant grids are supported right now!');
		
		
		return $this->gridDescription->getPointCoordinates($index);
	}
}