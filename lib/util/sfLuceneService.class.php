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
 * This class extends some original method from the parent class in order
 * to be more flexible
 *
 * @package    sfLucenePlugin
 * @subpackage Utilities
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id$
 */
class sfLuceneService extends Apache_Solr_Service
{
  
  protected
    $response_class = 'sfLuceneResponse';
  
  
  public static function convertDate($date)
  {
    
    return date('c\Z', strtotime($date));
  }
  
  public static function convertTimestamp($timestamp)
  {
    
    return date('c\Z', $timestamp);
  }
  
  /**
   * Simple Search interface
   *
   * @param string $query The raw query string
   * @param int $offset The starting offset for result documents
   * @param int $limit The maximum number of result documents to return
   * @param array $params key / value pairs for other query parameters (see Solr documentation), use arrays for parameter keys used more than once (e.g. facet.field)
   * @return sfLuceneResponse
   *
   * @throws Exception If an error occurs during the service call
   */
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
  
  /**
   * Central method for making a get operation against this Solr Server
   *
   * @param string $url
   * @param float $timeout Read timeout in seconds
   * @return sfLuceneResponse
   *
   * @throws Exception If a non 200 response status is returned
   */
  protected function _sendRawGet($url, $timeout = FALSE)
  {
    // set the timeout if specified
    if ($timeout !== FALSE && $timeout > 0.0)
    {
      // timeouts with file_get_contents seem to need
      // to be halved to work as expected
      $timeout = (float) $timeout / 2;

      stream_context_set_option($this->_getContext, 'http', 'timeout', $timeout);
    }
    else
    {
      // use the default timeout pulled from default_socket_timeout otherwise
      stream_context_set_option($this->_getContext, 'http', 'timeout', $this->_defaultTimeout);
    }

    // $http_response_header will be updated by the call to file_get_contents later
    // see http://us.php.net/manual/en/wrappers.http.php for documentation
    // Unfortunately, it will still create a notice in analyzers if we don't set it here
    $http_response_header = null;

    $class = $this->response_class;
    
    $response = new $class(@file_get_contents($url, false, $this->_getContext), $http_response_header, $this->_createDocuments, $this->_collapseSingleValueArrays);

    if ($response->getHttpStatus() != 200)
    {
      throw new Apache_Solr_HttpTransportException($response);
    }

    return $response;
  }

  /**
   * Central method for making a post operation against this Solr Server
   *
   * @param string $url
   * @param string $rawPost
   * @param float $timeout Read timeout in seconds
   * @param string $contentType
   * @return sfLuceneResponse
   *
   * @throws Exception If a non 200 response status is returned
   */
  protected function _sendRawPost($url, $rawPost, $timeout = FALSE, $contentType = 'text/xml; charset=UTF-8')
  {
    stream_context_set_option($this->_postContext, array(
        'http' => array(
          // set HTTP method
          'method' => 'POST',

          // Add our posted content type
          'header' => "Content-Type: $contentType",

          // the posted content
          'content' => $rawPost,

          // default timeout
          'timeout' => $this->_defaultTimeout
        )
      )
    );

    // set the timeout if specified
    if ($timeout !== FALSE && $timeout > 0.0)
    {
      // timeouts with file_get_contents seem to need
      // to be halved to work as expected
      $timeout = (float) $timeout / 2;

      stream_context_set_option($this->_postContext, 'http', 'timeout', $timeout);
    }

    // $http_response_header will be updated by the call to file_get_contents later
    // see http://us.php.net/manual/en/wrappers.http.php for documentation
    // Unfortunately, it will still create a notice in analyzers if we don't set it here
    $http_response_header = null;

    $class = $this->response_class;
    
    $response = new $class(@file_get_contents($url, false, $this->_postContext), $http_response_header, $this->_createDocuments, $this->_collapseSingleValueArrays);

    if ($response->getHttpStatus() != 200)
    {
      throw new Apache_Solr_HttpTransportException($response);
    }

    return $response;
  }
}