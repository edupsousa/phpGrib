<?php
/**
 * GribBinaryDataSection class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribSection.php');

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
	
	public function getDataAt($index)
	{
		if ($this->packingFormat != self::SIMPLE_PACKING)
			throw new Exception ('Complex and harmonic unpacking not implemented!');

		$charsToGet = ceil($this->datumPackingBits / 8);
		$bitPosition = $index * $this->datumPackingBits;
		
		$hexData = unpack('H*', substr($this->rawBinaryData, floor($bitPosition / 8), $charsToGet));
		$intData = hexdec($hexData[1]);
		
		$bitsToSet = (pow(2, $this->datumPackingBits) - 1) << ($bitPosition % 8);
		$packedValue = $intData & $bitsToSet;
		
		return ($this->referenceValue + ($packedValue * pow(2,  $this->binaryScaleFactor)));
	}
}