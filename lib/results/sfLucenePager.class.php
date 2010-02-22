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
 * Adds a paging mechanism similar to sfPropelPager to the results.  This is
 * meant to be as similar to sfPager and sfPropelPager as possible.
 *
 * TODO: Find a more efficient way to do paging!  Right now, it has to return
 * the entire result set and do an array_slice() on it.
 *
 * @package    sfLucenePlugin
 * @subpackage Results
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLucenePager
{
  protected 
    $results = array(),
    $search = null,
    $page = 1, 
    $perPage = 5,
    $lucene_results,
    $search_parameters,
    $must_reload = false;

  /**
   *
   * @param sfLuceneResults $lucene_results
   * @param sfLucene $search
   */
  public function __construct($lucene_results, $search = null)
  {

    if(!$lucene_results instanceof sfLuceneResults)
    {
      
      throw new sfLuceneException('first arguments must be a sfLuceneResults instance');
    }
        
    $this->lucene_results = $lucene_results;
    $this->results = $lucene_results->toArray();
    $this->search_parameters = $lucene_results->getRawResult()->sf_lucene_search;

    // compute the page number from the results
    $limit = $lucene_results->getRawResult()->sf_lucene_search['limit'];
    
    $start = $this->getResults()->getRawResult()->response->start;

    $this->setMaxPerPage($limit);
    $this->setPage((int)($start  / $limit) + 1);
    $this->must_reload = false;

    
    $this->search = $search === null ? $lucene_results->getSearch() : $search;

    if(!$this->search instanceof sfLucene)
    {
      
      throw new sfLuceneException('second arguments must be a sfLucene instance');
    }
  }

  /**
   * Hook for sfMixer
   */
  public function __call($method, $arguments)
  {
    $event = $this->getSearch()->getEventDispatcher()->notifyUntil(new sfEvent($this, 'pager.method_not_found', array('method' => $method, 'arguments' => $arguments)));

    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', __CLASS__, $method));
    }

    return $event->getReturnValue();
  }

  /**
   * return the related sfLucene instance
   * 
   * @return sfLucene
   */
  public function getSearch()
  {
    return $this->search;
  }

  /**
   * reload results from the server
   *
   * This method is used when the page number is altered
   */
  protected function reloadFromServer()
  {
    if($this->must_reload === false)
    {
      
      return;
    }
    
    $response = $this->getSearch()->getLucene()->search(
      $this->search_parameters['query'],
      ($this->getPage() - 1) * $this->search_parameters['limit'],
      $this->search_parameters['limit'],
      $this->search_parameters['params'],
      $this->search_parameters['method']
    );
    
    $this->lucene_results = new sfLuceneResults($response, $this->getSearch());

    $this->results = $this->lucene_results->toArray();
    
    $this->must_reload = false;
  }

  /**
   * force the result to be reloaded from the server
   *
   */
  protected function resetResults()
  {
    $this->must_reload = true;
  }

  /**
   * return an array of available page numbers
   *
   * @param integer $nb_links
   * @return array
   */
  public function getLinks($nb_links = 5)
  {
    $links = array();
    $tmp   = $this->getPage() - floor($nb_links / 2);
    $check = $this->getLastPage() - $nb_links + 1;
    $limit = ($check > 0) ? $check : 1;
    $begin = ($tmp > 0) ? (($tmp > $limit) ? $limit : $tmp) : 1;

    $i = $begin;
    while (($i < $begin + $nb_links) && ($i <= $this->getLastPage()))
    {
      $links[] = $i++;
    }

    return $links;
  }

  /**
   * true if the page have to paginate
   * 
   * @return boolean true if the page have to paginate
   */
  public function haveToPaginate()
  {

    return (($this->getPage() != 0) && ($this->getNbResults() > $this->getMaxPerPage()));
  }

  /**
   * return the number of results per page
   * 
   * @return integer number of results per page
   */
  public function getMaxPerPage()
  {
    return $this->perPage;
  }

  /**
   * set the number of results per page
   * 
   * @param integer $per
   */
  public function setMaxPerPage($per)
  {
    $this->perPage = $per;
  }

  /**
   * set the current page number
   *
   * @param integer $page
   */
  public function setPage($page)
  {
    if ($page <= 0)
    {
      $page = 1;
    }
    elseif ($page > $this->getLastPage())
    {
      $page = $this->getLastPage();
    }

    if($this->page != $page)
    {
      $this->resetResults();
    }
    
    $this->page = $page;
  }

  /**
   * return the page number
   *
   * @return integer return the page number
   */
  public function getPage()
  {

    return $this->page;
  }

  /**
   * return the results
   *
   * @return sfLuceneResults
   */
  public function getResults()
  {
    $this->reloadFromServer();
    
    return $this->lucene_results;
  }

  /**
   *
   * @return integer number of result
   */
  public function getNbResults()
  {

    return $this->getResults()->getRawResult()->response->numFound;
  }

  /**
   *
   * @return integer the number of the fist page
   */
  public function getFirstPage()
  {
    
    return 1;
  }

  /**
   *
   * @return integer the last page number
   */
  public function getLastPage()
  {
    return ceil($this->getNbResults() / $this->getMaxPerPage());
  }

  /**
   *
   * @return integer the next availabe page number
   */
  public function getNextPage()
  {
    return min($this->getPage() + 1, $this->getLastPage());
  }

  /**
   *
   * @return integer the previous availabe page number
   */
  public function getPreviousPage()
  {
    return max($this->getPage() - 1, $this->getFirstPage());
  }

  public function getFirstIndice()
  {
    return ($this->getPage() - 1) * $this->getMaxPerPage() + 1;
  }

  public function getLastIndice()
  {
    if (($this->getPage() * $this->getMaxPerPage()) >= $this->getNbResults())
    {
      return $this->getNbResults();
    }
    else
    {
      return $this->getPage() * $this->getMaxPerPage();
    }
  }
}