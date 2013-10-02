<?php
/**
 * GribBinaryDataSection class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribSection.php');

/**
 * GribBinaryDataSection is used to represent the Binary Data Section (BDS) from
 * a GRIB message. It contains the section fields, raw packaged data and functions 
 * to unpack them.
 */
class GribBinaryDataSection extends GribSection
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
	
	/**
	 * Get data at specified index from the raw binary data.
	 * Data is returned unpacked. Currently this function only
	 * supports the simple packing algorithm.
	 * 
	 * @param integer $index Index of the data
	 * @return float The unpacked data as a float point value
	 */
	public function getDataAt($index)
	{
		if ($this->packingFormat != self::SIMPLE_PACKING)
			throw new Exception ('Complex and harmonic unpacking not implemented!');
		
		/*
		 * If zero bits are need for packing so all points got the same value
		 * of the reference value.
		 */
		if ($this->datumPackingBits == 0)
			return $this->referenceValue;
		
		$bitIndex = $index*$this->datumPackingBits;
		$valueStart = floor($bitIndex/8);
		$byteLength = ceil(($bitIndex+$this->datumPackingBits)/8)-$valueStart;
		$maskOffset = $bitIndex % 8;
		$mask = (pow(2,$this->datumPackingBits)-1) << $maskOffset;
		
		$valueData = unpack('C*', substr($this->rawBinaryData, $valueStart, $byteLength));
		
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
}