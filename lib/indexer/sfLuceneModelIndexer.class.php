<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Model indexing engine.
 * @package sfLucenePlugin
 * @subpackage Indexer
 * @author Carl Vondrick
 * @version SVN: $Id$
 */

abstract class sfLuceneModelIndexer extends sfLuceneIndexer
{
  private $instance;

  /**
   * Constructs a new instance for a model
   */
  public function __construct($search, $instance)
  {
    parent::__construct($search);

    if ($search->getParameter('models')->get(get_class($instance), null) == null)
    {
      throw new sfLuceneIndexerException(sprintf('Model "%s" is not registered.', get_class($instance)));
    }

    $this->instance = $instance;
  }

  /**
   * Calculates the GUID for the model
   */
  abstract protected function getModelGuid();

  /**
   * Returns the instance of the model
   */
  protected function getModel()
  {
    return $this->instance;
  }

  /**
   * Returns the model name
   */
  protected function getModelName()
  {
    return get_class($this->getModel());
  }

  /**
  * Returns the properties of the given model.
  */
  protected function getModelProperties()
  {
    return $this->getSearch()->getParameter('models')->get($this->getModelName());
  }

  /**
   * Returns an array of the model categories
   */
  protected function getModelCategories()
  {
    $properties = $this->getModelProperties();

    if (!$properties->has('categories'))
    {
      return array();
    }

    $categories = $properties->get('categories');

    if (!is_array($categories))
    {
      $categories = array($categories);
    }

    return $categories;
  }

  /**
   * Configures meta data about the document
   */
  protected function configureDocumentMetas(Zend_Search_Lucene_Document $doc)
  {
    $doc->addField($this->getLuceneField('unindexed', 'sfl_model', $this->getModelName()));
    $doc->addField($this->getLuceneField('unindexed', 'sfl_type', 'model'));

    return $doc;
  }

  /**
   * Configures categories into the document
   */
  protected function configureDocumentCategories(Zend_Search_Lucene_Document $doc)
  {
    $categories = $this->getModelCategories();

    if (count($categories) > 0)
    {
      foreach ($categories as $category)
      {
        $this->addCategory($category);
      }

      $doc->addField( $this->getLuceneField('text', 'sfl_category', implode(' ', $categories)) );
    }
    
    $doc->addField( $this->getLuceneField('unindexed', 'sfl_categories_cache', serialize($categories)) );

    return $doc;
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

      if (!is_callable(array($this->getModel(), $cb)))
      {
        throw new sfLuceneIndexerException(sprintf('Callback "%s::%s()" does not exist', $this->getModelName(), $cb));
      }
      
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
}
