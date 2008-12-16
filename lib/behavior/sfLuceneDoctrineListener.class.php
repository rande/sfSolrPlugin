<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Responsible for handling Doctrine's behaviors.
 * @package    sfLucenePlugin
 * @subpackage Behavior
 * @author     Carl Vondrick <carlv@carlsoft.net>
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 */
class sfLuceneDoctrineListener extends Doctrine_Record_Listener
{
  /**
   * Executes save routine
   */
  public function postSave(Doctrine_Event $event)
  {
    try {
      $this->saveIndex($event->getInvoker());
    } catch(sfException $e) {
      // no context define, cannot do anything, 
    }
  }

  /**
   * Deletes the object
   */
  public function postDelete(Doctrine_Event $event)
  {
    try {
      $this->deleteIndex($event->getInvoker());
    } catch(sfException $e) {
      // no context define, cannot do anything
    }
    
  }

  /**
   * Saves index by deleting and inserting.
   */
  public function saveIndex($node)
  {
    $this->deleteIndex($node);
    $this->insertIndex($node);
  }

  /**
  * Deletes the old model
  */
  public function deleteIndex($node)
  {
    
    sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('{sfLucene} deleting model "%s" with PK = "%s"', get_class($node), current($node->identifier()))));

    foreach ($this->getSearchInstances($node) as $instance)
    {
      $instance->getIndexerFactory()->getModel($node)->delete();
    }
  }

  /**
  * Adds the new model
  */
  public function insertIndex($node)
  {
    
    sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('{sfLucene} deleting model "%s" with PK = "%s"', get_class($node), current($node->identifier()))));
  
    foreach ($this->getSearchInstances($node) as $instance)
    {
      $instance->getIndexerFactory()->getModel($node)->insert();
    }
  }

  protected function getSearchInstances($node)
  {
    static $instances;

    $class = get_class($node);

    if (!isset($instances))
    {
      $instances = array();
    }

    if (!isset($instances[$class]))
    {
      $config = sfLucene::getConfig();

      foreach ($config as $name => $item)
      {
        if (isset($item['models'][$class]))
        {
          foreach ($item['index']['cultures'] as $culture)
          {
            $instances[$class][] = sfLucene::getInstance($name, $culture);
          }
        }
      }
    }

    return $instances[$class];
  }

  /**
  * Returns the behavior initializer
  */
  static public function getInitializer()
  {
    return sfLuceneDoctrineInitializer::getInstance();
  }
}