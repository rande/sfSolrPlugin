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
  static
    $model_properties = array();
    
  protected 
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

    if(!is_object($instance))
    {
      throw new sfLuceneIndexerException('The instance is not an object');
    }
    
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
   * return the full document
   *
   * @return sfLuceneDocument
   */
  abstract protected function getDocument();

  /**
   * Calculates the GUID for the model
   */
  abstract protected function getModelGuid();

  /**
   * Returns the instance of the model
   */
  public function getModel()
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
    $model_name = $this->getModelName();
    if(!isset(self::$model_properties[$model_name]))
    {
      self::$model_properties[$model_name] =  $this->getSearch()->getParameter('models')->get($model_name);
    }
    
    return self::$model_properties[$model_name];
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
  protected function configureDocumentMetas(sfLuceneDocument $doc)
  {
    
    $properties = $this->getModelProperties();
    $fields = $properties->get('fields');
    
    $doc->setField('sfl_model', $this->getModelName());
    $doc->setField('sfl_type',  'model');
    
    try
    {
      if ($properties->get('title')) {
        $title = $fields->get($properties->get('title'));
        $title = (isset($title) && $title->get('alias'))
          ? $this->getModel()->{$title->get('alias')}()
          : $this->getModel()->get($properties->get('title'));

        $doc->setField('sfl_title', $title);
    }
    }
    catch(Doctrine_Record_Exception $e)
    {
      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('model "%s" does not have a valid `sfl_title` field - primary key = %s: %s', $this->getModelName(), $properties->get('title'), current($this->getModel()->identifier()), $e->getMessage())));
    }
    
    try
    {
      if ($properties->get('description')) {
        $description = $fields->get($properties->get('description'));
        $description = (isset($description) && $description->get('alias'))
          ? $this->getModel()->{$description->get('alias')}()
          : $this->getModel()->get($properties->get('description'));

        $doc->setField('sfl_description', $description);
    }
    }
    catch(Doctrine_Record_Exception $e)
    {
        $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('model "%s" does not have a valid `sfl_description` field using "%s" - primary key = %s: %s', $this->getModelName(), $properties->get('description'), current($this->getModel()->identifier()), $e->getMessage())));
    }
    
    return $doc;
  }

  /**
   * Configures categories into the document
   */
  protected function configureDocumentCategories(sfLuceneDocument $doc)
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
   * return an empty sfLuceneDocument, but if a callback is specified
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

      if (!($doc instanceof sfLuceneDocument))
      {
        throw new sfLuceneIndexerException(sprintf('"%s::%s()" did not return a valid document (must be an instance of sfLuceneDocument)', $this->getModelName(), $cb));
      }
    }
    else
    {
      $doc = new sfLuceneDocument();
    }

    return $doc;
  }
  

  /**
   * Builds the fields into the document as configured by the parameters.
   */
  protected function configureDocumentFields(sfLuceneDocument $doc)
  {
    $properties = $this->getModelProperties();

    // loop through each field
    foreach ($properties->get('fields')->getNames() as $field)
    {
      $field_properties = $properties->get('fields')->get($field);

      $value = $this->getFieldValue($field, $field_properties);

      // do not index null value
      if($value === null || (is_array($value) && empty($value)))
      {
        continue;
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
      elseif (is_array($value) && $field_properties->get('multiValued'))
      {
        // nothing to do ;)
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
      }
      elseif($type === 'boolean')
      {
        $transform = array($this, 'forceBooleanString');
      }
      

      if(!is_array($value))
      {
        $value = array($value);
      }

      foreach($value as $pos => $v)
      {
        if($transform)
        {
           $value[$pos] = call_user_func($transform, $v);
        }
      }
      
      $doc->setField($field, $value, $boost);
    }

    return $doc;
  }
  
  public function forceBooleanString($value) {
      return $value ? 'true' : 'false';
  }

  abstract public function getFieldValue($field, $properties);
}
