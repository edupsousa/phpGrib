<?php
/**
 * GribFileParser class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribParser.php');

class GribFileParser extends GribParser
{
	const MESSAGE_IDENTIFICATOR_LENGHT = 4;
	const MESSAGE_SIZE_FIELD_LENGHT = 3;
	const GRIB_VERSION_FIELD_LENGHT = 1;
	
	public static function getMessages($path)
	{
		$handle = fopen($path,'rb');
		if (!$handle)
			throw new GribParserException('',  GribParserException::UNABLE_TO_OPEN_FILE);
		
		$messages = array();
		while (self::_fileHasGribMessage($handle)) {
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
				$messages[] = GribMessageParser::parse($gribMessage);
		}
		fclose($handle);
		return $messages;
	}
	
	protected static function _fileHasGribMessage($handle)
	{
		if (self::_readStringFromFile($handle, self::MESSAGE_IDENTIFICATOR_LENGHT) == 'GRIB')
			return true;
		return false;
	}
	
	protected static function _readStringFromFile($handle, $length)
	{
		return fread($handle, $length);
	}
	
	protected static function _readMessageSizeFromFile($handle)
	{
		$raw = fread($handle, self::MESSAGE_SIZE_FIELD_LENGHT);
		$data = unpack('H6size',$raw);
		return hexdec($data['size']);
	}
}
