<?php
/**
 * GribParser class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */


require_once('GribMessage.php');
require_once('GribFileParser.php');
require_once('GribMessageParser.php');
require_once('GribIndicatorSectionParser.php');
require_once('GribProductDefinitionSectionParser.php');
require_once('GribGridDescriptionSectionParser.php');
require_once('GribBinaryDataSectionParser.php');


/**
 * GribParserException extends the PHP Exception class for throwing
 * exceptions related to Grib parsing.
 */
class GribParserException extends Exception
{
	const UNABLE_TO_OPEN_FILE = 0x1;
	const UNSUPPORTED_GRIB_VERSION = 0x2;
	const MESSAGE_TOO_SHORT = 0x3;
	const INDICATOR_NOT_FOUND = 0x4;
	const MESSAGE_LENGHT_MISMATCH = 0x5;
}

/**
 * GribParser is the base class for Grib parsing classes. It contains static
 * functions to obtain and convert data from binary strings used by its child
 * classes.
 */
class GribParser
{
	protected static function _getRawSectionFromMessage($message, &$currentPosition, $length = false)
	{
		if ($length === false)
			$length = self::_getNextSectionLength($message, $currentPosition);
		
		$raw = substr($message, $currentPosition, $length);
		$currentPosition += $length;
		return $raw;
	}
	
	protected static function _getNextSectionLength($message, $currentPosition)
	{
		return self::_getUInt($message, $currentPosition, 3);
	}
	
	protected static function _isFlagSet($flag, $string, $position)
	{
		$byte = ord(substr($string, $position, 1));
		return (($byte & $flag) == $flag);
	}
	
	protected static function _getUInt($string, $start, $length)
	{
		$value = unpack('H*', substr($string, $start, $length));
		return hexdec($value[1]);
	}
	
	protected static function _getSignedInt($string, $start, $length)
	{
		$uInt = self::_getUInt($string, $start, $length);
		$signal = $uInt & (1 << (8 * $length)-1);
		$value = $uInt & ~((1 << (8 * $length)-1));
		return ($signal ? -$value : $value);
	}
	
	protected static function _getSingle($string, $position)
	{
		$A = self::_getSignedInt($string, $position, 1);
		$B = self::_getUInt($string, $position+1, 3);
		return pow(2, -24) * $B * pow(16, $A-64);
	}
}