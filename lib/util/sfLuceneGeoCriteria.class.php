<?php

/*
 * This file is part of the sfLucenePlugin package
 * (c) 2010 - Julien Lirochon <julien@lirochon.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class sfLuceneGeoCriteria extends sfLuceneFacetsCriteria
{
  const
    UNIT_KILOMETERS = 1,
    UNIT_MILES      = 2
  ;

  const DISTANCE_FIELD = 'geo_distance';
  const MILES_PER_KILOMETERS = 0.621371192;


  protected $unit = self::UNIT_KILOMETERS;


  public function __construct($unit = self::UNIT_KILOMETERS)
  {
    parent::__construct();

    $this->unit = self::UNIT_KILOMETERS;
  }

  public static function getUnitRatio($unit = self::UNIT_KILOMETERS)
  {
    return ($unit == self::UNIT_KILOMETERS) ? self::MILES_PER_KILOMETERS : 1;
  }

  /**
   * Limit results to those inside the circle. Distance between circle's center
   * and the result is computed and stored in 'geo_distance' field.
   * 
   * WARNING : localsolr must be enabled (see search.yml)
   *
   * @author  Julien Lirochon <julien@lirochon.net>
   * @param   $latitude   latitude of the circle's center
   * @param   $longitude  longitude of the circle's center
   * @param   $radius     radius of the circle
   * @return  void
   */
  public function addGeoCircle($latitude, $longitude, $radius)
  {
    // sets query type
    $this->setParam('qt', 'geo');

    $this->addParam('lat', $latitude);
    $this->addParam('long', $longitude);
    $this->addParam('radius', $radius * self::getUnitRatio($this->unit));
  }

  public function addAscendingSortByDistance()
  {
    return $this->addSortBy(self::DISTANCE_FIELD, SORT_ASC);
  }

  public function addDescendingSortByDistance()
  {
    return $this->addSortBy(self::DISTANCE_FIELD, SORT_DESC);
  }

  public function addSortByDistance($order = SORT_ASC)
  {
    return $this->addSortBy(self::DISTANCE_FIELD, $order);
  }

  public function sortByDistance($order = SORT_ASC)
  {
    return $this->sortBy(self::DISTANCE_FIELD, $order);
  }
}
