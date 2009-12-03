<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Symfony friendly wrapper for all the Lucene hits.
 *
 * This implemenets the appropriate interfaces so you can still access it as an array
 * and loop through it.
 *
 * @package    sfLucenePlugin
 * @subpackage Results
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id: sfLuceneResults.class.php 24784 2009-12-02 09:58:03Z rande $
 */
class sfLuceneFacetsResults extends sfLuceneResults 
{
  
  protected $facets_fields = null;
  
  
  public function getFacetQueries()
  {
    
    return $this->getFacetsField('facet_queries');
  }
  
  public function getFacetFields()
  {

    return $this->getFacetsField('facet_fields');
  }

  public function getFacetQuery($name)
  {
    $facets =  $this->getFacetQueries();
    
    if(!$facets || !isset($facets[$name]))
    {
      
      return null;
    }
    
    return $facets[$name];
  }
  
  public function getFacetField($name)
  {
    $facets =  $this->getFacetFields();
    
    if(!$facets || !isset($facets[$name]))
    {
      
      return null;
    }
    
    return $facets[$name];
  }
  
  public function getFacetsField($facet_field_name)
  {
    // The underline library convert the json into a stdClass object
    // There is no other choice for now, sorry for this code ...
    if($this->facets_fields == null)
    {
      $json = json_decode($this->results->getRawResponse(), true);
      
      $this->facets_fields = $json['facet_counts'];
    }
    
    if(!array_key_exists($facet_field_name, $this->facets_fields))
    {
      
      return null;
    }
   
    return $this->facets_fields[$facet_field_name];
  }
}