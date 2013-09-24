<?php
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