<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides a clean way to search the index, mimicking Propel Criteria.
 *
 * Usage example: <code>
 *  TODO
 * </code>
 *
 *
 * @package    sfLucenePlugin
 * @subpackage Utilities
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id:$
 */
class sfLuceneFacetsCriteria extends sfLuceneCriteria
{
  
  public function __construct()
  {
    parent::__construct();
    
    $this->addParam('facet', 'true');
  }
  
  /**
   * Add a facet field
   * 
   * @param string $name
   * @return sfLuceneFacetsCriteria
   */
  public function addFacetField($name, $reset = false)
  {
    $this->addParam('facet.field', $name, $reset);
    
    return $this;
  }
  
  /**
   * Add a facet query
   * 
   * @param string $name
   * @return sfLuceneFacetsCriteria
   */
  public function addFacetQuery($name, $reset = false)
  {
    $this->addParam('facet.query', $name, $reset);
    
    return $this;
  }
  
  /**
   * Simply provides a way to do one line method chaining
   * 
   * @return sfLuceneCriteria
   */
  static public function newInstance()
  {
    return new self;
  }
}