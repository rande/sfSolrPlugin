<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    sfLucenePlugin
 * @subpackage Utilities
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id$
 */
class sfLuceneApacheSolrService extends Apache_Solr_Service
{
  
  public function search($query, $offset = 0, $limit = 10, $params = array(), $method = self::METHOD_GET)
  {
    
    $results = parent::search($query, $offset, $limit, $params, $method);
    
    $results->sf_lucene_search = array(
      'query'   => $query,
      'offset'  => $offset,
      'limit'   => $limit,
      'params'  => $params,
      'method'  => $method
    );
    
    return $results;
  }
}