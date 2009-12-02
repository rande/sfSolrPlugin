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
    $params = array(),
    $path = null,
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

  public function setPath($path)
  {

    $this->path = $path;
  }

  public function getPath()
  {

    return $this->path;
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
   * be parsed or a sfLuceneCriteria object.
   * 
   * @return sfLuceneCriteria
   */
  public function add($query, $type = sfLuceneCriteria::TYPE_AND, $force = false)
  {
    
    if($query instanceof sfLuceneCriteria)
    {
      if($this === $query)
      {
        
        throw new sfException('Cannot add itself as a subquery');
      }
      
      $query = '('.$query->getQuery().')';
    }
    else if(is_object($query))
    {
      
      throw new sfException('Wrong object type');
    }
    else // string ...
    {
      
      if($query !== Apache_Solr_Service::escape($query) && !$force)
      {

        throw new sfException('Invalid terms : '.$query.' != '.Apache_Solr_Service::escape($query));
      }
    }
    
    $this->query = strlen($this->query) == 0 ? $query : $this->query.' '.$type.' '.$query;
    
    return $this;
  }
  
  public function addString($query, $type = sfLuceneCriteria::TYPE_AND)
  {
    
    return $this->addSane($query, $type);
  }

  /**
   * Add a subquery to the query itself. The phrase will be automatically sanitized
   *
   * @param string $phrase
   * @return sfLuceneCriteria
   *
   */
  public function addSane($phrase, $type = sfLuceneCriteria::TYPE_AND)
  {

    return $this->add(self::sanitize($phrase), $type, true );
  }
  
  public function addPhrase($phrase, $type = sfLuceneCriteria::TYPE_AND)
  {
    
    return $this->addSane($phrase, $type);
  }
  
  public function addWildcard($phrase, $type = sfLuceneCriteria::TYPE_AND)
  {
    
    return $this->add(self::sanitize($phrase), $type, true );
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

    $start = $start === null ? '*' : $start;
    $stop  = $stop  === null ? '*' : $stop;
    
    if($inclusive)
    {
      $query = ($field ? $field . ':' : '') . '['.$start.' TO '.$stop.']';
    }
    else
    {
      $query = ($field ? $field . ':' : ''). '{'.$start.' TO '.$stop.'}';
    }
    
    return $this->add($query, $type, true);
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
  public function addProximity($latitude, $longitude, $proximity, $radius = 6378.1, $type = sfLuceneCriteria::TYPE_AND, $latitudeField = 'latitude', $longitudeField = 'longitude')
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
    
    
    // round to 10 ...
    $latitudeLower = round($latitudeLower, 10);
    $latitudeUpper = round($latitudeUpper, 10);
    $longitudeLower = round($longitudeLower, 10);
    $longitudeUpper = round($longitudeUpper, 10);
    
    $subquery = $this->getNewCriteria();
    
    $subquery->addRange($latitudeLower, $latitudeUpper, $latitudeField, true);
    $subquery->addRange($longitudeLower, $longitudeUpper, $longitudeField, true);

    return $this->add($subquery, $type, true);
  }


  /**
   *
   * @param string $field
   * @param string $value
   *
   * @return sfLuceneCriteria
   */
  public function addFiltering($field, $value)
  {

    $this->addParam('fq', sprintf("%s:%s", $field, $value));

    return $this;
  }



  /**
   * return filtering fields, which is used to separated the user queries
   * and the logic sub selection
   *
   * @return array of filter
   */
  public function getFiltering()
  {

    return isset($this->params['fq']) ? $this->params['fq'] : array();
  }


  /**
   *
   * Add a parameter to the solr query, a parameter is an solr option
   *  - fq : filtering option
   *  - qt : the query handler name
   *
   * Any parameters will be appended to the query string
   *
   * @param string $name
   * @param string $value
   * @param boolean $reset
   *
   */
  public function addParam($name, $value, $reset = false)
  {

    if(!array_key_exists($name, $this->params) || $reset)
    {
      $this->params[$name] = array();
    }

    $this->params[$name][] = $value;

    return $this;
  }

  public function setParam($name, $value)
  {

    return $this->addParam($name, $value, true);
  }

  /**
   *
   * Extra parameters send to solr
   *
   * @return array
   *
   */
  public function getParams()
  {

    return $this->params;
  }

  public function getParam($name, $default = null)
  {

    return isset($this->params[$name]) ? $this->params[$name] : $default;
  }
  
  /**
   * 
   * @param string $field
   * @param interger $type
   * 
   * @return sfLuceneCriteria
   */
  public function addAscendingSortBy($field)
  {

    return $this->addSortBy($field, SORT_ASC);
  }

  /**
   * 
   * @param string $field
   * @param interger $type
   * 
   * @return sfLuceneCriteria
   */
  public function addDescendingSortBy($field)
  {
    
    return $this->addSortBy($field, SORT_DESC);
  }

  /**
   * 
   * @param string $field
   * @param interger $type
   * 
   * @return sfLuceneCriteria
   */  
  public function addSortBy($field, $order = SORT_ASC)
  {

    $add_sort = sprintf("%s %s", $field, $order == SORT_ASC ? 'asc' : 'desc');
    $sort = $this->getParam('sort', array());

    $this->setParam('sort', count($sort) > 0 ? $sort[0] . ", " . $add_sort : $add_sort);

    return $this;
  }

  /**
   * Returns a string query that can be fed directly to Lucene
   *
   * @return string 
   */
  public function getQuery()
  {
    return $this->query;
  }

  public function getSorts()
  {
    return $this->getParam('sort', array());
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

    return Apache_Solr_Service::phrase($keyword);
  }
}
