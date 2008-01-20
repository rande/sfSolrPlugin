<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
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
 * This class is not meant to reinvent the entire Zend Lucene's API, but rather
 * provide a simpler way to search.  It is possible to combine queries built with
 * the Zend API too.
 *
 * @package    sfLucenePlugin
 * @subpackage Utilities
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */

class sfLuceneCriteria
{
  protected $query = null;

  protected $sorts = array();

  protected $scoring = null;

  protected $search = null;

  public function __construct(sfLucene $search)
  {
    sfLuceneToolkit::loadZend();

    $this->query = new Zend_Search_Lucene_Search_Query_Boolean();
    $this->search = $search;
  }

  /**
   * Simply provides a way to do one line method chaining
   */
  static public function newInstance(sfLucene $search)
  {
    return new self($search);
  }

  /**
   * Adds a subquery to the query itself.  It accepts either a string which will
   * be parsed or a Zend query.
   */
  public function add($query, $type = true)
  {
    if (is_string($query))
    {
      $this->addString($query, null, $type);
    }
    else
    {
      if ($query instanceof self)
      {
        if ($query === $this)
        {
          throw new sfLuceneException('You cannot add an instance to itself');
        }

        $query = $query->getQuery();
      }

      if (!($query instanceof Zend_Search_Lucene_Search_Query))
      {
        throw new sfLuceneException('Invalid query given (must be instance of Zend_Search_Lucene_Search_Query)');
      }

      $this->query->addSubquery($query, $type);
    }

    return $this;
  }

  /**
   * Adds a string that is parsed into Zend API queries
   * @param string $query The query to parse
   * @param string $encoding The encoding to parse query as
   */
  public function addString($query, $encoding = null, $type = true)
  {
    $this->search->configure(); // setup query parser

    $this->add(Zend_Search_Lucene_Search_QueryParser::parse($query, $encoding), $type);

    return $this;
  }

  /**
  * This does a sane add on the current query.  The query parser tends to throw a lot
  * of exceptions even in normal conditions, so we need to intercept them and then fall back
  * into a reduced state mode should the user have entered invalid syntax.
  */
  public function addSane($query, $type = true, $fatal = false)
  {
    try
    {
      return $this->add($query, $type);
    }
    catch (Zend_Search_Lucene_Search_QueryParserException $e)
    {
      if (!is_string($query))
      {
        if ($fatal)
        {
          throw $e;
        }
        else
        {
          return $this;
        }
      }

      try
      {
        $replacements = array('+', '-', '&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', '\\', ' and ', ' or ', ' not ');

        $query = ' ' . $query . ' ';
        $query = str_replace($replacements, '', $query);
        $query = trim($query);

        return $this->add($query, $type);
      }
      catch (Zend_Search_Lucene_Search_QueryParserException $e)
      {
        if ($fatal)
        {
          throw $e;
        }
        else
        {
          return $this;
        }
      }
    }
  }

  /**
   * Adds a field to the search query.
   * @param mixed $values The values to search on
   * @param string $field The field to search under (null for all)
   * @param bool $matchAll If true, it will match all.  False will match none.  Null is neutral.
   * @param bool $type The type of subquery to add.
   */
  public function addField($values, $field = null, $matchAll = null, $type = true)
  {
    if (is_array($values))
    {
      $query = $this->getNewCriteria();

      foreach($values as $value)
      {
        $term = new Zend_Search_Lucene_Index_Term($value, $field);
        $qterm = new Zend_Search_Lucene_Search_Query_Term($term);

        $query->add($qterm, $matchAll);
      }
    }
    elseif (is_string($values))
    {
      $term = new Zend_Search_Lucene_Index_Term($values, $field);
      $query = new Zend_Search_Lucene_Search_Query_Term($term);
    }
    else
    {
      throw new sfLuceneException('Unknown field value type');
    }

    return $this->add($query, $type);
  }

  /**
  * Adds a multiterm query.
  */
  public function addMultiTerm($values, $field = null, $matchType = null, $type = true)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    $query = new Zend_Search_Lucene_Search_Query_MultiTerm();

    foreach ($values as $value)
    {
      $query->addTerm(new Zend_Search_Lucene_Index_Term($value, $field), $matchType);
    }

    return $this->add($query, $type);
  }


  /**
  * Adds a wildcard field.
  *   ? is used as single character wildcard
  *   * is used as a multi character wildcard.
  */
  public function addWildcard($value, $field = null, $type = true)
  {
    $pattern = new Zend_Search_Lucene_Index_Term($value, $field);
    $query = new Zend_Search_Lucene_Search_Query_Wildcard($pattern);

    return $this->add($query, $type);
  }

  /**
  * Adds a phrase query
  */
  public function addPhrase($keywords, $field = null, $slop = 0, $type = true)
  {
    $query = new Zend_Search_Lucene_Search_Query_Phrase(array_values($keywords), array_keys($keywords), $field);
    $query->setSlop($slop);

    return $this->add($query, $type);
  }

  /**
   * Adds a range subquery
   */
  public function addRange($start = null, $stop = null, $field = null, $inclusive = true, $type = true)
  {
    if ($start)
    {
      $start = new Zend_Search_Lucene_Index_Term($start, $field);
    }
    else
    {
      $start = null;
    }

    if ($stop)
    {
      $stop = new Zend_Search_Lucene_Index_Term($stop, $field);
    }
    else
    {
      $stop = null;
    }

    if ($stop == null && $start == null)
    {
      throw new sfLuceneException('You must specify at least a start or stop in a range query.');
    }

    $query = new Zend_Search_Lucene_Search_Query_Range($start, $stop, $inclusive);

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
   */
  public function addProximity($latitude, $longitude, $proximity, $radius = 6378.1, $latitudeField = 'latitude', $longitudeField = 'longitude', $type = true)
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
    $subquery->addRange($latitudeLower, $latitudeUpper, $latitudeField, true, true);
    $subquery->addRange($longitudeLower, $longitudeUpper, $longitudeField, true, true);

    return $this->add($subquery, $type);
  }

  public function addAscendingSortBy($field, $type = SORT_REGULAR)
  {
    return $this->addSortBy($field, SORT_ASC, $type);
  }

  public function addDescendingSortBy($field, $type = SORT_REGULAR)
  {
    return $this->addSortBy($field, SORT_DESC, $type);
  }

  public function addSortBy($field, $order = SORT_ASC, $type = SORT_REGULAR)
  {
    $this->sorts[] = array('field' => $field, 'order' => $order, 'type' => $type);

    return $this;
  }

  /**
   * Sets the scoring algorithm for this query.
   * @param null|Zend_Search_Lucene_Search_Similarity $algorithm An instance of the algorithm to use (null for default)
   */
  public function setScoringAlgorithm($algorithm)
  {
    if ($algorithm != null && !($algorithm instanceof Zend_Search_Lucene_Search_Similarity))
    {
      throw new sfLuceneException('Scoring algorithm must either be null (for default) or an instance of Zend_Search_Lucene_Search_Similarity');
    }

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

  public function getNewCriteria()
  {
    return new self($this->search);
  }
}
