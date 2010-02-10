<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides a clean way to retrieve index information
 *
 *
 *
 * @package    sfLucenePlugin
 * @subpackage Utilities
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id:$
 */
class sfLuceneLuke 
{
  
  protected
    $stats = null,
    $lucene = null
  ;
  
  public function __construct(sfLucene $lucene)
  {
    $this->lucene = $lucene;
  }
  
  public function getRequestHandlerUrl()
  {
    return sprintf("%s://%s:%s%s/%s/admin/%s",
      'http', // TODO : make this configurable
      $this->lucene->getParameter('host'),
      $this->lucene->getParameter('port'),
      $this->lucene->getParameter('base_url'),
      $this->lucene->getParameter('index_location'),
      'luke'
    );

  }
  
  public function loadInformation()
  {
    
    if($this->stats !== null)
    {
      
      return;
    }
    
    $xml = simplexml_load_file($this->getRequestHandlerUrl());
    
    if(!$xml)
    {
      
      throw new sfException('unable to retrieve luke information');
    }
    
    foreach($xml as $main_node)
    {
      $node_type = (string)$main_node['name'];
      
      if($node_type == 'index')
      {
        $this->stats['index'] = array();
        foreach($main_node as $info)
        {
          $var_type = $info->getName();
          
          if($var_type == 'str')
          {
            $val = (string)$info;
          } 
          else if ($var_type == 'bool')
          {
            $val = (boolean)$info;
          } 
          else if($var_type == 'long')
          {
            $val = (double)$info;
          }
          else if($var_type == 'float')
          {
            $val = (float)$info;
          }
          else if($var_type == 'int')
          {
            $val = (int)$info;
          }
          else if($var_type == 'date')
          {
            $val = (string)$info;
          }
          else
          {
             // unsupported type
            $val = null;
          }
          
          $this->stats['index'][(string)$info['name']] = $val;
          
        }
      }
    }
    
    return $this;
  }
  
  public function getStats()
  {
    $this->loadInformation();
    
    $args = func_get_args();
    
    $stats = $this->stats;
    
    foreach($args as $name)
    {
      if(array_key_exists($name, $stats))
      {
        $stats = $stats[$name];
      }
      else
      {
        
        return null;
      }
    }
    
    return $stats;
  }
  
  
  public function getNumDocs()
  {
    
    return $this->getStats('index', 'numDocs');
  }
  
  public function getMaxDoc()
  {
    
    return $this->getStats('index', 'maxDoc');
  }
  
  public function getNumTerms()
  {
    
    return $this->getStats('index', 'numTerms');
  }
  
  public function getVersion()
  {
    
    return $this->getStats('index', 'version');
  }
  
  public function getOptimized()
  {
    
    return $this->getStats('index', 'optimized');
  }
  
  public function getCurrent()
  {
    
    return $this->getStats('index', 'current');
  }
  
  public function getHasDeletions()
  {
    
    return $this->getStats('index', 'hasDeletions');
  }
  
  public function getDirectory()
  {
    
    return $this->getStats('index', 'directory');
  }
  
  public function getLastModified()
  {
    
    return $this->getStats('index', 'lastModified');
  }
}