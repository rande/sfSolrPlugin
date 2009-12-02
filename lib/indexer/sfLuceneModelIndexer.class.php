<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
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
  private 
    $instance,
    $model_name; // model name used in the search.yml file

  /**
   * Constructs a new instance for a model
   */
  public function __construct($search, $instance)
  {
    parent::__construct($search);

    $models = $search->getParameter('models')->getAll();
    $this->model_name = false;

    // fix class inheritance
    foreach(array_keys($models) as $model)
    {
      if($instance instanceof $model)
      {
        
        $this->model_name = $model;
        break;
      }
    }
    
    if (!$this->model_name)
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
    return $this->model_name;
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
  protected function configureDocumentMetas(Apache_Solr_Document $doc)
  {
    $doc->addField('sfl_model', $this->getModelName());
    $doc->addField('sfl_type', 'model');

    return $doc;
  }

  /**
   * Configures categories into the document
   */
  protected function configureDocumentCategories(Apache_Solr_Document $doc)
  {
    $categories = $this->getModelCategories();

    if (count($categories) > 0)
    {
      foreach ($categories as $category)
      {
        $this->addCategory($category);
      }

      $doc->addField('sfl_category', implode(' ', $categories));
    }
    
    $doc->addField('sfl_categories_cache', serialize($categories));

    return $doc;
  }
  

  /**
   * Returns the base document to work with.  Most of the time this will just
   * return an empty Apache_Solr_Document, but if a callback is specified
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
      
      $doc = $this->getModel()->$cb($this->getSearch());

      if (!($doc instanceof Apache_Solr_Document))
      {
        throw new sfLuceneIndexerException(sprintf('"%s::%s()" did not return a valid document (must be an instance of Apache_Solr_Document)', $this->getModelName(), $cb));
      }
    }
    else
    {
      $doc = new Apache_Solr_Document();
    }

    return $doc;
  }
  

  /**
   * Builds the fields into the document as configured by the parameters.
   */
  protected function configureDocumentFields(Apache_Solr_Document $doc)
  {
    $properties = $this->getModelProperties();

    // loop through each field
    foreach ($properties->get('fields')->getNames() as $field)
    {
      $field_properties = $properties->get('fields')->get($field);

      $getter = $field_properties->get('alias') ? $field_properties->get('alias') : 'get' . sfInflector::camelize($field);
      
      // build getter by converting from underscore case to camel case
      try
      {
        $value = $this->getModel()->$getter();
      }
      catch(Doctrine_Record_Exception $e)
      {
      
        // some fields can be only used as a definition
        // and used in the callback method
        if(!$properties->get('callback'))
        {
          throw $e;
        }
        else
        {
          continue;
        }
      }
     
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

      $doc->addField($field, $value, $boost);
    }

    return $doc;
  }
}
