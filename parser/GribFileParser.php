<?php
/**
 * GribFileParser class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribParser.php');

/**
 * GribFileParser is used to parse a file containing GRIB Messages.
 */
class GribFileParser extends GribParser
{
	const MESSAGE_IDENTIFICATOR_LENGHT = 4;
	const MESSAGE_SIZE_FIELD_LENGHT = 3;
	const GRIB_VERSION_FIELD_LENGHT = 1;
	
	/**
	 * Retrieve a array of GribMeessage objects from the file 
	 * specified by the path.
	 * 
	 * @param string $path The path of the file to retrieve GRIB messages
	 * @return GribMessage[] A array containing all GRIB messages from the 
	 * file as GribMessage objects
	 * @throws GribParserException
	 */
	public static function loadFile($path)
	{
		$handle = fopen($path,'rb');
		if (!$handle)
			throw new GribParserException('',  GribParserException::UNABLE_TO_OPEN_FILE);
		
		$messages = array();
		while ($messages[] = self::readMessage($handle));
		array_pop($messages);
		
		fclose($handle);
		return $messages;
	}
	
	/**
	 * Read a GribMessage from the file at current position.
	 * 
	 * @param resource $handle The handle resource created with fopen
	 * @return GribMessage A representation from the GRIB message read from file
	 * @throws GribParserException
	 */
	public static function readMessage($handle)
	{
		if (self::_fileHasGribMessage($handle)) {
			$messageSize = self::_readMessageSizeFromFile($handle);
			$gribVersion = ord(self::_readStringFromFile($handle, self::GRIB_VERSION_FIELD_LENGHT));
			
			if ($gribVersion != 1)
				throw new GribParserException('', GribParserException::UNSUPPORTED_GRIB_VERSION);

			/*
			 * Rewind file pointer to the beginning of Indicator Section 
			 * to read entire GRIB message for parsing.
			 */
			fseek($handle,
				-(self::MESSAGE_IDENTIFICATOR_LENGHT + 
				  self::MESSAGE_SIZE_FIELD_LENGHT +
				  self::GRIB_VERSION_FIELD_LENGHT
				),
				SEEK_CUR);
			$gribMessage = self::_readStringFromFile($handle, $messageSize);
			return GribMessageParser::parse($gribMessage);
		}
		return false;
	}
	
	/**
	 * Check if there`s a GRIB message at the current file position.
	 * 
	 * @param resource $handle The file handle to check
	 * @return boolean TRUE if a GRIB message is found, FALSE if not. 
	 */
	protected static function _fileHasGribMessage($handle)
	{
		if (self::_readStringFromFile($handle, self::MESSAGE_IDENTIFICATOR_LENGHT) == 'GRIB')
			return true;
		return false;
	}
	
	/**
	 * Retrieve a string at the current position from the given file handle.
	 * 
	 * @param resource $handle The file handle resource
	 * @param integer $length The string length to retrieve
	 * @return string A string retrieved from the file
	 */
	protected static function _readStringFromFile($handle, $length)
	{
		return fread($handle, $length);
	}
	
	/**
	 * Retrieve the GRIB message length from the file.
	 * 
	 * @param resource $handle The file handle.
	 * @return integer The message length
	 */
	protected static function _readMessageSizeFromFile($handle)
	{
		$raw = fread($handle, self::MESSAGE_SIZE_FIELD_LENGHT);
		$data = unpack('H6size',$raw);
		return hexdec($data['size']);
	}
}
