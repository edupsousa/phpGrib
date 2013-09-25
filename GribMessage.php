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
 * GribMessage is the PHP representation of a binary GRIB message.
 */
class GribMessage extends GribSection
{
	/**
	 * @var GribIndicatorSection store the indicator section from grib message
	 */
	public $indicatorSection;
	
	/**
	 * @var GribProductDefinitionSection store the product definition section 
	 * from Grib message 
	 */
	public $productDefinitionSection;
	
	/**
	 * @var GribGridDescriptionSection store the grid description section 
	 * from Grib message 
	 */
	public $gridDescriptionSection;
	
	/**
	 * @var null will store the bitmap section in future implementations
	 */
	public $bitmapSection;
	
	/**
	 * @var GribBinaryDataSection stores the binary data section from Grib 
	 * message
	 */
	public $binaryDataSection;
}