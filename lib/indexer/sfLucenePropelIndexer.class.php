<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
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

  /**
  * Inserts the provided model into the index based off parameters in search.yml.
  */
  public function insert()
  {
    // should we continue with indexing?
    if (!$this->shouldIndex())
    {
      // indexer said to skip indexing
      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Ignoring model "%s" from index with primary key = %s', $this->getModelName(), $this->getModel()->getPrimaryKey())));

      return $this;
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
    $this->addDocument($doc, $this->getModelGuid());

    // restore culture in symfony i18n detection
    if ($old_culture)
    {
      $this->getModel()->setCulture($old_culture);
    }

    // notify about new record
    $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Inserted model "%s" from index with primary key = %s', $this->getModelName(), $this->getModel()->getPrimaryKey())));

    return $this;
  }

  /**
   * Returns the base document to work with.  Most of the time this will just
   * return an empty Zend_Search_Lucene_Document, but if a callback is specified
   * it will return that.
   */
  protected function getBaseDocument()
  {
    $properties = $this->getModelProperties();

    // get our base document from callback?
    if ($properties->get('callback'))
    {
      $cb = $properties->get('callback');
      $doc = $this->getModel()->$cb();

      if (!($doc instanceof Zend_Search_Lucene_Document))
      {
        throw new sfLuceneIndexerException(sprintf('"%s::%s()" did not return a valid document (must be an instance of Zend_Search_Lucene_Document)', $this->getModelName(), $cb));
      }
    }
    else
    {
      $doc = new Zend_Search_Lucene_Document();
    }

    return $doc;
  }

  /**
   * Builds the fields into the document as configured by the parameters.
   */
  protected function configureDocumentFields(Zend_Search_Lucene_Document $doc)
  {
    $properties = $this->getModelProperties();

    // loop through each field
    foreach ($properties->get('fields')->getNames() as $field)
    {
      $field_properties = $properties->get('fields')->get($field);

      // build getter by converting from underscore case to camel case
      $getter = 'get' . sfInflector::camelize($field);
      $value = $this->getModel()->$getter();

      $type = $field_properties->get('type');
      $boost = $field_properties->get('boost');

      // validate value to make sure we can really index this
      if (is_object($value) && method_exists($value, '__toString'))
      {
        $value = $value->__toString();
      }
      elseif (is_null($value))
      {
        $value = '';
      }
      elseif (!is_scalar($value))
      {
        throw new sfLuceneIndexerException('Field value returned is not a string (got a ' . gettype($value) . ' ) and it could be casted to a string for field ' . $field);
      }

      // handle a possible transformation function
      if ($transform = $field_properties->get('transform'))
      {
        if (!is_callable($transform))
        {
          throw new sfLuceneIndexerException('Transformation function ' . $transform . ' does not exist');
        }

        $value = call_user_func($transform, $value);
      }

      $zsl_field = $this->getLuceneField($type, strtolower($field), $value);
      $zsl_field->boost = $boost;

      $doc->addField($zsl_field);
    }

    return $doc;
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