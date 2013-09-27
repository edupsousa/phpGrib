<?php
/**
 * GribGridDescription class file
 * 
 * @author Eduardo P de Sousa <edupsousa@gmail.com>
 * @copyright Copyright (c) 2013, Eduardo P de Sousa
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */

/**
 * GribGridDescription is the base class used to describe the 
 * grid used in the GRIB message.
 */
class GribGridDescription
{
	
}

/**
 * GribLatLonGridDescription is used to describe a 
 * latitude/longitude based grid
 */
class GribLatLonGridDescription extends GribGridDescription
{
	const EARTH_SPHERICAL = 0;
	const EARTH_SPHEROID = 1;
	
	const DIRECTION_NORTH_EAST = 0;
	const DIRECTION_BY_GRID = 1;
	
	public $pointsAlongLatitude;
	public $pointsAlongLongitude;
	
	public $latitudeFirstPoint;
	public $longitudeFirstPoint;
	
	public $directionIncrementGiven;
	public $earthModel;
	public $componentsDirection;
	
	public $latitudeLastPoint;
	public $longitudeLastPoint;
	
	public $longitudinalIncrement;
	public $latitudinalIncrement;
	
	public $scanNegativeI;
	public $scanNegativeJ;
	public $scanJConsecutive;
}