<?php
/**
 * GribMessage class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribSection.php');
require_once('GribIndicatorSection.php');
require_once('GribProductDefinitionSection.php');
require_once('GribGridDescriptionSection.php');
require_once('GribBinaryDataSection.php');

/**
 * GribMessage is the PHP representation of a GRIB message.
 */
class GribMessage extends GribSection
{
	/**
	 * @var GribIndicatorSection
	 */
	public $indicatorSection;
	
	/**
	 * @var GribProductDefinitionSection
	 */
	public $productDefinitionSection;
	
	/**
	 * @var GribGridDescriptionSection
	 */
	public $gridDescriptionSection;
	
	/**
	 * @var null
	 * @todo Implement the Bitmap section decoding
	 */
	public $bitmapSection;
	
	/**
	 * @var GribBinaryDataSection
	 */
	public $binaryDataSection;
}