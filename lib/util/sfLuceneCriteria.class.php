<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides a clean way to search the index, mimicking Propel Criteria.
 *
 * Usage example: <code>
 * $c = sfLuceneCriteria::newInstance()->add('the cool dude')->addField('sfl_category', array('Forum', 'Blog'));
 * </code>

 *
 * @package    sfLucenePlugin
 * @subpackage Utilities
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id$
 */

class sfLuceneCriteria
{
  protected 
    $query = null,
    $sorts = array(),
    $scoring = null,
    $limit = 10,
    $offset = 0;

  const 
    TYPE_AND = 'AND',
    TYPE_OR  = 'OR';
  
  public function __construct($search)
  {
    $this->search = $search;
    $this->query = '';
  }

  public function setLimit($limit)
  {
    $this->limit = $limit;
  }
  
  public function getLimit()
  {
    return $this->limit;
  }
  
  public function setOffset($offset)
  {
    $this->offset = $offset;
  }
  
  public function getOffset()
  {
    return $this->offset;
  }
  
  /**
   * Simply provides a way to do one line method chaining
   * 
   * @return sfLuceneCriteria
   */
  static public function newInstance(sfLucene $search)
  {
    return new self($search);
  }

  /**
   * Adds a subquery to the query itself.  It accepts either a string which will
   * be parsed or a Zend query.
   * 
   * @return sfLuceneCriteria
   */
  public function add($query, $type = sfLuceneCriteria::TYPE_AND)
  {

    if(strlen($this->query) != 0)
    {
      $this->query .= ' '. $type;
    }
    
    if($query instanceof sfLuceneCriteria)
    {
      $this->query .=  ' ('.$query->getQuery().')';
    }
    else
    {
      $this->query .= ' '.$query;
    }
    
    return $this;
  }
  
  public function addString($query, $type = sfLuceneCriteria::TYPE_AND)
  {
    
    return $this->add($query, $type);
  }

  /**
   * Adds a range subquery
   * 
   * @return sfLuceneCriteria
   */
  public function addRange($start = null, $stop = null, $field = null, $inclusive = true, $type = sfLuceneCriteria::TYPE_AND)
  {
    
    if ($stop == null && $start == null)
    {
      throw new sfLuceneException('You must specify at least a start or stop in a range query.');
    }

    if($inclusive)
    {
      $query = $field . ':['.$start.' TO '.$stop.']';
    }
    else
    {
      $query = $field . ':{'.$start.' TO '.$stop.'}';
    }
    
    return $this->add($query, $type);
  }

  /**
   * Adds a proximity query to restrict by distance from longitude and latitude.
   *
   * This method will do a pretty good calculation to restrict the results to
   * fall under a certain distance from an origin point.
   *
   * This method is not restricted to one particular unit, except you must be
   * consistent!  This means you can use miles or kilometers (or centimeters)
   * and you can use degrees North or degrees South.
   *
   * The average radius of Earth is 3962 mi or 6378.1 km.
   *
   * @param float $latitude The origin latitude in degrees
   * @param float $longitude The origin longitude in degrees
   * @param int $proximity The maximun proximity in any unit.
   * @param int $radius The average radius of Earth in the same unit as $proximity
   * @param string $latitudeField The field to search under for latitudes.
   * @param string $longitudeField The field to search under for longitude.
   * @param mixed $type The type of restraint
   * 
   * @return sfLuceneCriteria
   */
  public function addProximity($latitude, $longitude, $proximity, $radius = 6378.1, $latitudeField = 'latitude', $longitudeField = 'longitude')
  {
    if ($radius <= 0)
    {
      throw new sfLuceneException('Radius must be greater than 0');
    }
    elseif ($proximity <= 0)
    {
      throw new sfLuceneException('Proximity must be greater than 0');
    }

    $perLatitude = M_PI * $radius / 180;

    $latitudeChange = $proximity / $perLatitude;
    $north = $latitude + $latitudeChange;
    $south = $latitude - $latitudeChange;

    $longitudeChange = $proximity / (cos(deg2rad($latitude)) * $perLatitude);
    $east = $longitude + $longitudeChange;
    $west = $longitude - $longitudeChange;

    $latitudeLower = min($north, $south);
    $latitudeUpper = max($north, $south);

    $longitudeLower = min($east, $west);
    $longitudeUpper = max($east, $west);

    $subquery = $this->getNewCriteria();
    
    $subquery->addRange($latitudeLower, $latitudeUpper, $latitudeField, true);
    $subquery->addRange($longitudeLower, $longitudeUpper, $longitudeField, true);

    return $this->add($subquery);
  }

  /**
   * 
   * @param string $field
   * @param interger $type
   * 
   * @return sfLuceneCriteria
   */
  public function addAscendingSortBy($field, $type = SORT_REGULAR)
  {
    return $this->addSortBy($field, SORT_ASC, $type);
  }

  /**
   * 
   * @param string $field
   * @param interger $type
   * 
   * @return sfLuceneCriteria
   */
  public function addDescendingSortBy($field, $type = SORT_REGULAR)
  {
    return $this->addSortBy($field, SORT_DESC, $type);
  }

  /**
   * 
   * @param string $field
   * @param interger $type
   * 
   * @return sfLuceneCriteria
   */  
  public function addSortBy($field, $order = SORT_ASC, $type = SORT_REGULAR)
  {
    
    throw new sfEception(__CLASS__.'::'.__FUNCTION__.' not implemented');
    
    //$this->sorts[] = array('field' => $field, 'order' => $order, 'type' => $type);

    return $this;
  }

  /**
   * Sets the scoring algorithm for this query.
   * @param null|Zend_Search_Lucene_Search_Similarity $algorithm An instance of the algorithm to use (null for default)
   * 
   * @return sfLuceneCriteria
   */
  public function setScoringAlgorithm($algorithm)
  {
    
    throw new sfEception(__CLASS__.'::'.__FUNCTION__.' not implemented');
    
    $this->scoring = $algorithm;

    return $this;
  }

  /**
   * Returns a Zend_Search_Lucene query that can be fed directly to Lucene
   */
  public function getQuery()
  {
    return $this->query;
  }

  public function getSorts()
  {
    return $this->sorts;
  }

  public function getScoringAlgorithm()
  {
    return $this->scoring;
  }

  /**
   * .
   *
   * @return sfLuceneCriteria
   */
  public function getNewCriteria()
  {
    return new self($this->search);
  }
  
  public static function sanitize($keyword)
  {
    $keyword = trim($keyword);
    if(strlen($keyword) == 0)
    {
      return false;
    }
    
    $keyword = str_replace('"', '\"', $keyword);

    return '"'.$keyword.'"';
  }
}
