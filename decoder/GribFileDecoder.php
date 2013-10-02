<?php
/**
 * GribFileDecoder class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribDecoder.php');

/**
 * GribFileDecoder is used to decode a file containing GRIB Messages.
 */
class GribFileDecoder extends GribDecoder
{
	const MESSAGE_IDENTIFICATOR_LENGHT = 4;
	const MESSAGE_SIZE_FIELD_LENGHT = 3;
	const GRIB_VERSION_FIELD_LENGHT = 1;
	
	/**
	 * Retrieve a array of GribMeessage objects from the file 
	 * specified by the path.
	 * 
	 * @param string $path The path of the file to retrieve GRIB messages
	 * @param array|null $filter (Optional) If set, filter the messages returned by this function.
	 * Example: To return only messages with parameter 76 and 52 (Cloud Water and Relative Humidity), 
	 * with level type 100 (Isobaric Level) and level value 1000 (1000mb level) use:
	 * $filter = array(
	 * 		'parameters'=>array(76),
	 * 		'levelTypes'=>array(100),
	 * 		'levelValues'=>array(1000),
	 * );
	 * @return GribMessage[] A array containing all GRIB messages from the 
	 * file as GribMessage objects
	 * @throws GribDecoderException
	 */
	public static function loadFile($path, $filter = null)
	{
		$handle = fopen($path,'rb');
		if (!$handle)
			throw new GribDecoderException('',  GribDecoderException::UNABLE_TO_OPEN_FILE);
		
		$messages = array();
		while ($message = self::readMessage($handle)) {
			if ($filter) {
				if (isset($filter['parameters']) &&
				!in_array($message->productDefinitionSection->parameterId,$filter['parameters']))
					continue;
				
				if (isset($filter['levelTypes']) &&
				!in_array($message->productDefinitionSection->typeOfLayerOrLevel,$filter['levelTypes']))
					continue;
				
				if (isset($filter['levelValues']) &&
				!in_array($message->productDefinitionSection->layerOrLevelValue,$filter['levelValues']))
					continue;
			}
			$messages[] = $message;
		}
		
		fclose($handle);
		return $messages;
	}
	
	/**
	 * Read a GribMessage from the file at current position.
	 * 
	 * @param resource $handle The handle resource created with fopen
	 * @return GribMessage A representation from the GRIB message read from file
	 * @throws GribDecoderException
	 */
	public static function readMessage($handle)
	{
		if (self::_fileHasGribMessage($handle)) {
			$messageSize = self::_readMessageSizeFromFile($handle);
			$gribVersion = ord(self::_readStringFromFile($handle, self::GRIB_VERSION_FIELD_LENGHT));
			
			if ($gribVersion != 1)
				throw new GribDecoderException('', GribDecoderException::UNSUPPORTED_GRIB_VERSION);

			/*
			 * Rewind file pointer to the beginning of Indicator Section 
			 * to read entire GRIB message for decoding.
			 */
			fseek($handle,
				-(self::MESSAGE_IDENTIFICATOR_LENGHT + 
				  self::MESSAGE_SIZE_FIELD_LENGHT +
				  self::GRIB_VERSION_FIELD_LENGHT
				),
				SEEK_CUR);
			$gribMessage = self::_readStringFromFile($handle, $messageSize);
			return GribMessageDecoder::decode($gribMessage);
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
