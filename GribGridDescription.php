<?php

class GribGridDescription
{
	
}

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