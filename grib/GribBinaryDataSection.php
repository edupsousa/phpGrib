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

		/**
		 * @todo Optimize unpacking algorithm to allow use of partial bytes
		 */
		if ($this->datumPackingBits % 8)
			throw new Exception('Currently we only suport unpacking multiples of 8 bits');

		$charsToGet = (int)$this->datumPackingBits / 8;
		$bytePosition = $index * $charsToGet;
		
		$hexData = unpack('H*', substr($this->rawBinaryData, $bytePosition, $charsToGet));
		$packedValue = hexdec($hexData[1]);
		
		return ($this->referenceValue + ($packedValue * pow(2,  $this->binaryScaleFactor)));
	}
}