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
class sfLuceneResponse extends Apache_Solr_Response
{

  protected
    $document_class = 'sfLuceneDocument';
      
  /**
   * Parse the raw response into the parsed_data array for access
   */
  protected function _parseData()
  {
    //An alternative would be to use Zend_Json::decode(...)
    $data = json_decode($this->_rawResponse);

    // check that we receive a valid JSON response - we should never receive a null
    if ($data === null)
    {
      throw new Exception('Solr response does not appear to be valid JSON, please examine the raw response with getRawResposne() method');
    }

    //if we're configured to collapse single valued arrays or to convert them to Apache_Solr_Document objects
    //and we have response documents, then try to collapse the values and / or convert them now
    if (($this->_createDocuments || $this->_collapseSingleValueArrays) && isset($data->response) && is_array($data->response->docs))
    {
      $documents = array();

      foreach ($data->response->docs as $originalDocument)
      {
        if ($this->_createDocuments)
        {
          $class = $this->document_class;
          $document = new $class;
        }
        else
        {
          $document = $originalDocument;
        }

        foreach ($originalDocument as $key => $value)
        {
          //If a result is an array with only a single
          //value then its nice to be able to access
          //it as if it were always a single value
          if ($this->_collapseSingleValueArrays && is_array($value) && count($value) <= 1)
          {
            $value = array_shift($value);
          }

          $document->$key = $value;
        }

        $documents[] = $document;
      }

      $data->response->docs = $documents;
    }

    $this->_parsedData = $data;
  }
}