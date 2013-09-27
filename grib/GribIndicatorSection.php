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
	public $gribIndicator;
	public $messageLength;
	public $editionNumber;
}
