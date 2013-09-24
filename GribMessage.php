<?php
require_once('GribSection.php');
require_once('GribIndicatorSection.php');
require_once('GribProductDefinitionSection.php');
require_once('GribGridDescriptionSection.php');
require_once('GribBinaryDataSection.php');

class GribMessage extends GribSection
{
	public $indicatorSection;
	public $productDefinitionSection;
	public $gridDescriptionSection;
	public $bitmapSection;
	
	/**
	 *
	 * @var GribBinaryDataSection 
	 */
	public $binaryDataSection;
}