<?php
/**
 * GribGridDescriptionSection class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribGridDescription.php');

/**
 * GribGridDescriptionSection represents the Grid Description Section (GDS)
 * of a GRIB message and his fields.
 */
class GribGridDescriptionSection
{
	/**
	 * @var int Length of GDS section
	 */
	public $sectionLength;
	
	/**
	 * @var int Number of vertical coordinate parameters
	 */
	public $verticalCoordinateParameters;
	
	/**
	 * @var int Location in octets of the list of vertical parameters
	 * or points in each row
	 */
	public $pvOrPl;
	
	/**
	 * @var int Data representation type
	 */
	public $dataRepresentationType;
	
	/**
	 * @var GribGridDescription The grid description object
	 */
	public $gridDescription;
}