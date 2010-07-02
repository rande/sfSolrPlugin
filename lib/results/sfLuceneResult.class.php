<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Standard Lucene result.  This does all the mapping to follow the symfony coding standard.
 * @package    sfLucenePlugin
 * @subpackage Results
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneResult
{
  protected 
    $result,
    $search;

  /**
  * Consturctor, but consider using factor method ::getInstance()
  */
  public function __construct(sfLuceneDocument $result, sfLucene $search)
  {
    $this->result = $result;
    $this->search = $search;
  }

  public function getSearch()
  {
    return $this->search;
  }

  public function getResult()
  {
    return $this->result;
  }

  /**
  * Gets the score of this hit.
  */
  public function getScore()
  {
    if($this->result->__isset('score'))
    {
      return ((int) ($this->result->score * 100 + .5)); // round to nearest integer
    }
    
    return '-';
  }

  /**
  * Gets the partial
  */
  public function getInternalPartial($module = 'sfLucene')
  {
    return $module . '/' . $this->getInternalType() . 'Result';
  }

  public function getInternalType()
  {
    return $this->result->sfl_type;
  }
  
  
  public function getInternalDescription()
  {
    
    if(!isset($this->result->sfl_description))
    {
      return 'No description available.';
    }
    
    return strip_tags($this->result->sfl_description);
  }

  public function getInternalTitle()
  {
    
    if(!isset($this->result->sfl_title))
    {
      return 'No title available.';
    }
    
    return $this->result->sfl_title;
  }

  /**
  * Factory.  Gets an instance of the appropriate result based off type
  */
  static public function getInstance($result, $search)
  {
    
    $type = isset($result->sfl_type) ? $result->sfl_type : null;
    
    switch ($type)
    {
      case 'action':
        $c = 'sfLuceneActionResult';
        break;
      case 'model':
        if(strtolower(sfConfig::get('sf_orm')) == 'doctrine')
        {
          $c = 'sfLuceneDoctrineResult';
        }
        else if(strtolower(sfConfig::get('sf_orm')) == 'propel')
        {
          $c = 'sfLucenePropelResult';
        }
        else
        {
          throw new sfException('Unable to detect the current ORM');
        }
        
        break;
      default:
        $c = __CLASS__;
    }

    return new $c($result, $search);
  }

  /**
  * Adapts the ->getXXX() methods to lucene.
  */
  public function __call($method, $args = array())
  {
    if (substr($method, 0, 3) == 'get')
    {
      $field = sfInflector::underscore(substr($method, 3));
      
      if(substr($field, 0, 8) == 'internal')
      {
        $field = 'sfl_'.substr($field, 9);
      }
      
      if($this->result->__isset($field))
      {
        
        return $this->result->__get($field);
      }
    }
    elseif (substr($method, 0, 3) == 'has')
    {
      $field = sfInflector::underscore(substr($method, 3));
      
      if(substr($field, 0, 8) == 'internal')
      {
        $field = 'sfl_'.substr($field, 9);
      }
      
      return $this->result->__isset($field);
    }

    $event = $this->getSearch()->getEventDispatcher()->notifyUntil(new sfEvent($this, 'sf_lucene_result.method_not_found', array('method' => $method, 'arguments' => $args)));

    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', __CLASS__, $method));
    }

    return $event->getReturnValue();
  }

  /**
  * Maps a property from a ->getXXX() method to a lucene property
  */
  protected function getProperty($method, $prefix)
  {
    $property = substr($method, strlen($prefix));
    $property = $property;

    if (strtolower(substr($property, 0, 8)) == 'internal')
    {
      $property = 'sfl_' . sfInflector::underscore(substr($property, 8));
    }
    else
    {
      $property = sfInflector::underscore($property);
    }

    return $property;
  }
}