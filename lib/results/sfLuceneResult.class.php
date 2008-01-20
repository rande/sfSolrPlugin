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
  protected $result;

  protected $search;

  /**
  * Consturctor, but consider using factor method ::getInstance()
  */
  public function __construct(Zend_Search_Lucene_Search_QueryHit $result, sfLucene $search)
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
    return ((int) ($this->result->score * 100 + .5)); // round to nearest integer
  }

  /**
  * Gets the partial
  */
  public function getInternalPartial($module = 'sfLucene')
  {
    return $module . '/' . $this->getInternalType() . 'Result';
  }

  public function getInternalDescription()
  {
    try
    {
      return strip_tags($this->result->getDocument()->getFieldValue('sfl_description'));
    }
    catch (Exception $e)
    {
      return 'No description available.';
    }
  }

  public function getInternalTitle()
  {
    try
    {
      return $this->result->getDocument()->getFieldValue('sfl_title');
    }
    catch (Exception $e)
    {
      return 'No title available.';
    }
  }

  /**
  * Factory.  Gets an instance of the appropriate result based off type
  */
  static public function getInstance($result, $search)
  {
    switch ($result->getDocument()->getFieldValue('sfl_type'))
    {
      case 'action':
        $c = 'sfLuceneActionResult';
        break;
      case 'model':
        $c = 'sfLuceneModelResult';
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
      return $this->result->getDocument()->getFieldValue($this->getProperty($method, 'get'));
    }
    elseif (substr($method, 0, 3) == 'has')
    {
      try
      {
        $this->result->getDocument()->getFieldValue($this->getProperty($method, 'has'));

        return true;
      }
      catch (Exception $e)
      {
        return false;
      }
    }

    $event = $this->getSearch()->getEventDispatcher()->notifyUntil(new sfEvent($this, 'result.method_not_found', array('method' => $method, 'arguments' => $args)));

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