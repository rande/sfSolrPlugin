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
 * Symfony friendly wrapper for all the Lucene hits.
 *
 * This implemenets the appropriate interfaces so you can still access it as an array
 * and loop through it.
 *
 * @package    sfLucenePlugin
 * @subpackage Results
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneResults implements Iterator, Countable, ArrayAccess
{
  protected
    $results = array(),
    $pointer = 0,
    $search;

  /**
  * Constructor.  Weeds through the results.
  */
  public function __construct(sfLuceneResponse $response, sfLucene $search)
  {
    $this->results = $response;
    $this->search = $search;
  }

  /**
  * Gets a result instance for the result.
  */
  protected function getInstance($result)
  {
    
    return sfLuceneResult::getInstance($result, $this->search);
  }

  /**
   * Hook for sfMixer
   */
  public function __call($method, $arguments)
  {
    $event = $this->search->getEventDispatcher()->notifyUntil(new sfEvent($this, 'sf_lucene_results.method_not_found', array('method' => $method, 'arguments' => $arguments)));

    if (!$event->isProcessed())
    {
      throw new sfLuceneResultsException(sprintf('Call to undefined method %s::%s.', __CLASS__, $method));
    }

    return $event->getReturnValue();
  }

  public function getSearch()
  {
    
    return $this->search;
  }

  public function getRawResult()
  {
    return $this->results;
  }
  
  public function current()
  {

    return $this->getInstance($this->getRawResult()->response->docs[$this->pointer]);
  }

  public function key()
  {
    
    return $this->pointer;
  }

  public function next()
  {
    $this->pointer++;
  }

  public function rewind()
  {
    $this->pointer = 0;
  }

  public function valid()
  {
    
    return isset($this->getRawResult()->response->docs[$this->pointer]);
  }

  public function count()
  {

    return $this->getRawResult()->response->numFound;
  }

  public function offsetExists($offset)
  {
    
    return isset($this->getRawResult()->response->docs[$offset]);
  }

  public function offsetGet($offset)
  {
    
    return $this->getInstance($this->getRawResult()->response->docs[$offset]);
  }

  public function offsetSet($offset, $set)
  {
    $this->getRawResult()->response->docs[$offset] = $set;
  }

  public function offsetUnset($offset)
  {
    unset($this->getRawResult()->response->docs[$offset]);
  }

  public function toArray()
  {
    $response = $this->getRawResult()->response;
    
    if(!$response)
    {
      
      return array();
    }
    
    return $this->getRawResult()->response->docs;
  }
  
}