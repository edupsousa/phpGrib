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
	/**
	 * @var int Number of latitudinal data points
	 */
	public $latitudePoints;
	
	/**
	 * @var int Number of longitudinal data points
	 */
	public $longitudePoints;
	
	/**
	 * @var int Latitude of the first point
	 */
	public $latitudeFirstPoint;
	
	/**
	 * @var int Longitude of the first point
	 */
	public $longitudeFirstPoint;
	
	/**
	 * @var bool TRUE if latitude and longitude increments are given
	 */
	public $incrementsGiven;
	
	/**
	 * @var bool If TRUE use Oblate Spheroid Earth Figure with size as 
	 * determined by IAU in 1965: 6378.160 km, 6356.775 km, f = 1/297.0.
	 * If FALSE use Spheric Earth Figure with radius = 6367.47 km
	 */
	public $useOblateSpheroidFigure;
	
	/**
	 * @var bool If TRUE U and V wind components resolved relative to the
	 * grid latitude/longitude directions
	 * If FALSE U and V components are resolved relative to East and North
	 * directions.
	 */
	public $windComponentsAsGrid;
	
	/**
	 * @var int Latitude of the last data point
	 */
	public $latitudeLastPoint;
	
	/**
	 * @var int Longitude of the last data point
	 */
	public $longitudeLastPoint;
	
	/**
	 * @var int Longitudinal increment between points
	 */
	public $longitudinalIncrement;
	
	/**
	 * @var int Latitudinal increment between points
	 */
	public $latitudinalIncrement;
	
	/**
	 * @var bool If TRUE, points are stored from South to North (positive increment)
	 * if FALSE points are stored in North to South direction (negative increment)
.	 */
	public $scanToNorth;
	
	/**
	 * @var bool If TRUE, points are stored from East to West (negative increment)
	 * if FALSE points are stored in West to East direction (positive increment)
	 */
	public $scanToWest;
	
	/**
	 * @var bool If TRUE, points are stored consecutively by latitude,
	 * if FALSE points are stored consecutively by longitude. 
	 */
	public $scanLatitudeConsecutive;
	
	public function getPointCoordinates($index)
	{
		if (!$this->latitudinalIncrement || !$this->longitudinalIncrement) {
			throw new Exception('Latitude and Longitude increments not given!');
		}
		if ($this->scanLatitudeConsecutive) {
			throw new Exception('Implement latitude consecutive scan!');
		} else {
			$latitude = $this->latitudeFirstPoint + floor($index / $this->longitudePoints) * $this->latitudinalIncrement;
			$longitude = $this->longitudeFirstPoint + floor($index % $this->longitudePoints) * $this->longitudinalIncrement;
		}
		return array($latitude, $longitude);
	}
}