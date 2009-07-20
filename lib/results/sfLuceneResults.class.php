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
  protected $results = array();

  protected $pointer = 0;

  protected $search;

  /**
  * Constructor.  Weeds through the results.
  */
  public function __construct(Apache_Solr_Response $results, $search)
  {
    $this->results = $results;
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
    $event = $this->search->getEventDispatcher()->notifyUntil(new sfEvent($this, 'results.method_not_found', array('method' => $method, 'arguments' => $arguments)));

    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', __CLASS__, $method));
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
    
    return $this->getInstance($this->results->response->docs[$this->pointer]);
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
    
    return isset($this->results->response->docs[$this->pointer]);
  }

  public function count()
  {
    
    return $this->results->response->numFound;
  }

  public function offsetExists($offset)
  {
    
    return isset($this->results[$offset]);
  }

  public function offsetGet($offset)
  {
    
    return $this->getInstance($this->results[$offset]);
  }

  public function offsetSet($offset, $set)
  {
    $this->results[$offset] = $set;
  }

  public function offsetUnset($offset)
  {
    unset($this->results[$offset]);
  }

  public function toArray()
  {
    if(!isset($this->results->response))
    {
      
      return array();
    }
    
    return $this->results->response->docs;
  }
  
}