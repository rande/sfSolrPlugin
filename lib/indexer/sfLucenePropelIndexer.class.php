<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Propel indexing engine.
 *
 * @package sfLucenePlugin
 * @subpackage Indexer
 * @author Carl Vondrick
 * @version SVN: $Id$
 */
class sfLucenePropelIndexer extends sfLuceneModelIndexer
{
  /**
   * Constructs a new instance
   * @param sfLucene $search The search instance to index to
   * @param BaseObject $instance The model instance to index
   */
  public function __construct($search, $instance)
  {
    if (!($instance instanceof BaseObject))
    {
      throw new sfLuceneIndexerException('Model is not a Propel model (must extend BaseObject)');
    }

    parent::__construct($search, $instance);
  }

  public function getDocument()
  {
     // should we continue with indexing?
    if (!$this->shouldIndex())
    {

      return false;
    }

    $old_culture = null;

    // automatic symfony i18n detection
    if (method_exists($this->getModel(), 'getCulture') && method_exists($this->getModel(), 'setCulture'))
    {
      $old_culture = $this->getModel()->getCulture();
      $this->getModel()->setCulture($this->getSearch()->getParameter('culture'));
    }

    // build document
    $doc = $this->getBaseDocument();
    $doc = $this->configureDocumentFields($doc);
    $doc = $this->configureDocumentCategories($doc);
    $doc = $this->configureDocumentMetas($doc);

    // add document to index
    $doc->addField('sfl_guid', $this->getModelGuid());

    // restore culture in symfony i18n detection
    if ($old_culture)
    {
      $this->getModel()->setCulture($old_culture);
    }

    return $doc;
  }
  
  /**
  * Inserts the provided model into the index based off parameters in search.yml.
  */
  public function insert()
  {
    $doc = $this->getDocument();
    
    // should we continue with indexing?
    if (!$doc)
    {
      // indexer said to skip indexing
      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Ignoring model "%s" from index with primary key = %s', $this->getModelName(), $this->getModel()->getPrimaryKey())));

      return $this;
    }

    $this->addDocument($doc, $this->getModelGuid());

    // notify about new record
    $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Inserted model "%s" from index with primary key = %s', $this->getModelName(), $this->getModel()->getPrimaryKey())));

    return $this;
  }

  /**
  * Deletes the old model
  */
  public function delete()
  {
    if ($this->deleteGuid($this->getModelGuid()))
    {
      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Deleted model "%s" from index with primary key = %s', $this->getModelName(), $this->getModel()->getPrimaryKey())));
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

    if ($method)
    {
      return (bool) $this->getModel()->$method();
    }

    return true;
  }

  /**
   * Returns an array of all the categories that this model is configured for
   */
  protected function getModelCategories()
  {
    $retval = array();

    // change i18n to this culture
    if (sfConfig::get('sf_i18n'))
    {
      $i18n = $this->getSearch()->getContext()->getI18N();
      $i18n->setMessageSource(null, $this->getSearch()->getParameter('culture'));
    }

    // see: http://www.nabble.com/Lucene-and-n:m-t4449653s16154.html#a12695579
    foreach (parent::getModelCategories() as $category)
    {
      // if category fits into syntax "%XXX%" then we must replace it with ->getXXX() on the model
      if (substr($category, 0, 1) == '%' && substr($category, -1, 1) == '%')
      {
        $category = substr($category, 1, -1);

        $getter = 'get' . sfInflector::camelize($category);
        $getterValue = $this->getModel()->$getter();

        // attempt to convert value to string
        if (is_object($getterValue) && method_exists($getterValue, '__toString'))
        {
          $getterValue = $getterValue->__toString();
        }
        elseif (!is_scalar($getterValue))
        {
          throw new sfLuceneIndexerException('Category value returned is not a string (got a ' . gettype($getterValue) . ') and could not be transformed into a string.');
        }

        // store value for returning.  as the value comes the model, it is already
        // configured for i18n
        $retval[] = $getterValue;
      }
      else
      {
        // value did not come from model, so store it using i18n if possible

        if (isset($i18n) && $i18n)
        {
          $retval[] = $i18n->__($category);
        }
        else
        {
          $retval[] = $category;
        }
      }
    }

    return $retval;
  }

  /**
   * Calculates the GUID for the model
   */
  public function getModelGuid()
  {
    return self::getGuid($this->getModelName() . '_' . $this->getModel()->hashCode());
  }
}