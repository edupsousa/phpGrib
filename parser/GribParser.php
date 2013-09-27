<?php
/**
 * GribParser class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */


require_once(dirname(__FILE__) . '/../grib/GribMessage.php');
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
 * GribParser is the base class for all GRIB parsing classes. It contains static
 * functions to obtain and convert data from binary strings used by its child
 * classes.
 */
class GribParser
{
	/**
	 * This function is used to retrieve a GRIB message section as a binary 
	 * string.
	 * The section is retrieved from the $message parameter starting from the
	 * pointer $currentPosition. If the optional $length parameter is not set
	 * the section length is retrieved from 3 bytes starting at $currentPosition
	 * as a unsigned integer.
	 * After retrieving the section the $currentPosition pointer is updated to the 
	 * position after the retrieved section.
	 * 
	 * @param string $message A binary string containing the entire GRIB message
	 * @param integer $currentPosition A pointer to the current position of the 
	 * message being retrieved
	 * @param integer $length (Optional) The length of the section to retrieve
	 * @return string A binary string containing the section data
	 */
	protected static function _getRawSectionFromMessage($message, &$currentPosition, $length = false)
	{
		if ($length === false)
			$length = self::_getNextSectionLength($message, $currentPosition);
		
		$raw = substr($message, $currentPosition, $length);
		$currentPosition += $length;
		return $raw;
	}
	
	/**
	 * Retrieve the length of the next section in the GRIB message.
	 * The length is retrieved as a unsigned integer from 3 bytes starting
	 * in the $currentPosition
	 * 
	 * @param string $message The binary GRIB message
	 * @param integer $currentPosition A pointer to the current position of the 
	 * message
	 * @return integer The length of the section
	 */
	protected static function _getNextSectionLength($message, $currentPosition)
	{
		return self::_getUInt($message, $currentPosition, 3);
	}
	
	/**
	 * Check if a bit flag is set
	 * 
	 * @param integer $flag The bit value to check
	 * @param string $string The string where containing the flag
	 * @param integer $position The position of the byte containing the flag 
	 * on the $string
	 * @return boolean True if the flag is set, False if not
	 */
	protected static function _isFlagSet($flag, $string, $position)
	{
		$byte = ord(substr($string, $position, 1));
		return (($byte & $flag) == $flag);
	}
	
	/**
	 * Retrieve a unsigned integer from the binary string at position of $start
	 * parameter, the length of the integer is machine independent given by 
	 * the $length parameter.
	 * 
	 * @param string $string The binary string
	 * @param integer $start Where to find the value
	 * @param integer $length The byte length of the value
	 * @return integer The value retrieved from the binary string
	 */
	protected static function _getUInt($string, $start, $length)
	{
		$value = unpack('H*', substr($string, $start, $length));
		return hexdec($value[1]);
	}
	
	/**
	 * Retrieve a signed integer from a binary string.
	 * 
	 * @param string $string The binary string
	 * @param integer $start Position of the first byte
	 * @param integer $length Byte length of the signed integer
	 * @return integer Return a signed integer value
	 */
	protected static function _getSignedInt($string, $start, $length)
	{
		$uInt = self::_getUInt($string, $start, $length);
		$signal = $uInt & (1 << (8 * $length)-1);
		$value = $uInt & ~((1 << (8 * $length)-1));
		return ($signal ? -$value : $value);
	}
	
	/**
	 * Retrieve a single precision float point from a binary string.
	 * 
	 * @param string $string The binary string
	 * @param integer $position The position of the first byte to retrieve
	 * @return float A single precision float point
	 */
	protected static function _getSingle($string, $position)
	{
		$A = self::_getSignedInt($string, $position, 1);
		$B = self::_getUInt($string, $position+1, 3);
		return pow(2, -24) * $B * pow(16, $A-64);
	}
}