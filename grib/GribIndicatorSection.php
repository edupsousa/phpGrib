<?php
/**
 * GribIndicatorSection class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribSection.php');

/**
 * GribIndicatorSection represent the Indicator Section of a GRIB message.
 */
class GribIndicatorSection extends GribSection
{
	/**
	 * @var string The GRIB indicator string 'GRIB'
	 */
	public $gribIndicator;
	
	/**
	 * @var int Length of the entire GRIB message, this section included
	 */
	public $messageLength;
	
	/**
	 * @var int Number of the GRIB message edition
	 */
	public $editionNumber;
}
