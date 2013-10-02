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
	
	/**
	 * @var int Number of data points along latitude
	 */
	public $pointsAlongLatitude;
	
	/**
	 * @var int Number of data points along longitude
	 */
	public $pointsAlongLongitude;
	
	/**
	 * @var int The first point latitude
	 */
	public $latitudeFirstPoint;
	
	/**
	 * @var int The first point longitude 
	 */
	public $longitudeFirstPoint;
	
	/**
	 * @var int Direction increment flag, 0 - Not given, 1 - Given
	 */
	public $directionIncrementGiven;
	
	/**
	 * @var int Earth Model flag, 0 - Spherical, 1 - Spheroid
	 */
	public $earthModel;
	
	/**
	 * @var int Components direction flag: 
	 * 0 - U and V components are resolved relative to easterly and 
	 * northely directions
	 * 1 - U and V components resolved relative to defined grid in
	 * direction of increasing x and y coordinates respectively.
	 */
	public $componentsDirection;
	
	/**
	 * @var int The last point latitude
	 */
	public $latitudeLastPoint;
	
	/**
	 * @var int The last point longitude
	 */
	public $longitudeLastPoint;
	
	/**
	 * @var int Longitudinal increment
	 */
	public $longitudinalIncrement;
	
	/**
	 * @var int Latitudinal increment
	 */
	public $latitudinalIncrement;
	
	/**
	 * @var int If this flag is set, the points are scanned in -i direction
	 */
	public $scanNegativeI;
	
	/**
	 * @var int If this flag is set, the points are scanned in -j direction
	 */
	public $scanNegativeJ;
	
	/**
	 * @var int If set 1 points in J direction are consecutive.
	 */
	public $scanJConsecutive;
}