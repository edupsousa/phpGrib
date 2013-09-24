<?php
/**
 * GribGridDescriptionSection class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

require_once('GribSection.php');
require_once('GribGridDescription.php');

class GribGridDescriptionSection extends GribSection
{
	public $sectionLength;
	public $numberOfVerticalCoordinateParameters;
	public $pvOrPl;
	public $dataRepresentationType;
	public $gridDescription;
}