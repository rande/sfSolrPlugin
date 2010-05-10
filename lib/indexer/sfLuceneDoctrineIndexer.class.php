<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Doctrine indexing engine.
 *
 * @package sfLucenePlugin
 * @subpackage Indexer
 * @author Carl Vondrick
 * @author Thomas Rabaix
 * @todo Implementation
 */
class sfLuceneDoctrineIndexer extends sfLuceneModelIndexer
{

  public function getDocument()
  {
    if (!$this->shouldIndex())
    {

      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('model "%s" cancelled indexation - primary key = %s', $this->getModelName(), current($this->getModel()->identifier()))));

      return false;
    }
      
    $old_culture = null;

    // automatic symfony i18n detection
    if ($this->getModel()->getTable()->hasRelation('Translation'))
    {
      $old_culture = sfDoctrineRecord::getDefaultCulture();
      sfDoctrineRecord::setDefaultCulture($this->getSearch()->getParameter('culture'));
    }

    // build document
    $doc = $this->getBaseDocument();
    $doc = $this->configureDocumentFields($doc);
    //$doc = $this->configureDocumentCategories($doc);
    $doc = $this->configureDocumentMetas($doc);
    // add document
    $doc->setField('sfl_guid', $this->getModelGuid());

    // restore culture in symfony i18n detection
    if ($old_culture)
    {
      sfDoctrineRecord::setDefaultCulture($old_culture);
    }

    return $doc;
  }

  /**
  * Inserts the provided model into the index based off parameters in search.yml.
  *
  * @param BaseObject $this->getModel() The model to insert
  */
  public function insert()
  {
    $doc = $this->getDocument();

    if (!$doc)
    {
      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('model "%s" cancelled indexation - primary key = %s', $this->getModelName(), current($this->getModel()->identifier()))));

      return $this;
    }

    $this->addDocument($doc, $this->getModelGuid());

    $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Inserted model "%s" from index with primary key = %s', $this->getModelName(), current($this->getModel()->identifier()))));

    return $this;
  }

  /**
  * Deletes the old model
  * @param BaseObject $this->getModel() The model to delete
  */
  public function delete()
  {
    if ($this->deleteGuid( $this->getModelGuid() ))
    {
      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Deleted model "%s" from index with primary key = %s', $this->getModelName(), current($this->getModel()->identifier()))));
    }

    return $this;
  }

  /**
   * Determines if the provided model should be indexed.
   */
  protected function shouldIndex()
  {
    $properties = $this->getModelProperties();

    $method = $properties->get('validator');

    if (method_exists($this->getModel(), $method))
    {
      return (bool) $this->getModel()->$method( $this->getSearch());
    }

    return true;
  }

  protected function getModelCategories()
  {
    $retval = array();

    $error = error_reporting(0);
    $i18n = sfContext::getInstance()->getI18N();
    error_reporting($error);

    if ($i18n)
    {
      $i18n = $this->getSearch()->getContext()->getI18N();
      $i18n->setMessageSource(null, $this->getSearch()->getParameter('culture'));
    }

    // see: http://www.nabble.com/Lucene-and-n:m-t4449653s16154.html#a12695579
    foreach (parent::getModelCategories() as $category)
    {
      if (preg_match('/^%(.*)%$/', $category, $matches))
      {
        $category = $matches[1];

        $getter = 'get' . $category;

        if (!is_callable(array($this->getModel(), $getter)))
        {
          throw new sfLuceneIndexerException(sprintf('%s->%s() cannot be called', $this->getModelName(), $getter));
        }

        $getterValue = $this->getModel()->$getter();

        if (is_object($getterValue) && method_exists($getterValue, '__toString'))
        {
          $getterValue = $getterValue->__toString();
        }
        elseif (!is_scalar($getterValue))
        {
          if (is_object($getterValue))
          {
            throw new sfLuceneIndexerException('Category value returned is an object, but could not be casted to a string (add a __toString() method to fix this).');
          }
          else
          {
            throw new sfLuceneIndexerException('Category value returned is not a string (got a ' . gettype($value) . ' ) and could not be transformed into a string.');
          }
        }

        $retval[] = $getterValue;
      }
      else
      {
        $retval[] = $i18n ? $i18n->__($category) : $category;
      }
    }

    return $retval;
  }

  protected function getModelGuid()
  {
    return $this->getGuid( $this->getModelName() . '_' . current($this->getModel()->identifier()) );
  }

  protected function validate()
  {
    return NULL;
  }
  
  public function getFieldValue($field, $properties)
  {
  
    $getter = $properties->get('alias') ? $properties->get('alias') : 'get' . sfInflector::camelize($field);
    
    // build getter by converting from underscore case to camel case
    try
    {
      $value = call_user_func(array($this->getModel(), $getter));
    }
    catch(Doctrine_Record_Exception $e)
    {
      
      $model_properies = $this->getModelProperties();
      
      // some fields can be only used as a definition
      // and used in the callback method
      if(!$model_properies->get('callback'))
      {
        
        throw $e;
      }
      else
      {
        $value = null;
      }
    }
    
    if((is_array($value) || $value instanceof Doctrine_Collection) && !$properties->get('multiValued'))
    {
      
      throw new sfException('You cannot store a Doctrine_Collection with multiValued=false');
    }
    else if($value instanceof Doctrine_Collection && $properties->get('multiValued'))
    {
      $values = array();
      foreach($value as $object)
      {
        $values[] = $object->__toString();
      }
      
      return $values;
    }
    else if($value instanceof Doctrine_Record)
    {
      
      $value = $value->__toString();
    }

    return $value;
  }
}