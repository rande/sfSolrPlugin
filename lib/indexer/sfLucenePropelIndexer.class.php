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
  * @param BaseObject $this->getModel() The model to insert
  */
  public function insert()
  {
    // should we continue with indexing?
    if (!$this->shouldIndex())
    {
      // we should not index this, so abort
      return $this;
    }

    $old_culture = null;

    // automatic symfony i18n detection
    if (method_exists($this->getModel(), 'getCulture') && method_exists($this->getModel(), 'setCulture'))
    {
      $old_culture = $this->getModel()->getCulture();
      $this->getModel()->setCulture($this->getSearch()->getParameter('culture'));
    }

    $doc = $this->getBaseDocument();
    $doc = $this->configureDocumentFields($doc);
    $doc = $this->configureDocumentCategories($doc);
    $doc = $this->configureDocumentMetas($doc);

    // add document
    $this->addDocument($doc, $this->getModelGuid());

    // restore culture in symfony i18n detection
    if ($old_culture)
    {
      $this->getModel()->setCulture($old_culture);
    }

    return $this;
  }

  /**
   * Returns the base document
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
   * Builds the fields into the document as configured by the search.yml file.
   */
  protected function configureDocumentFields($doc)
  {
    $properties = $this->getModelProperties();

    foreach ($properties->get('fields')->getNames() as $field)
    {
      $field_properties = $properties->get('fields')->get($field);

      $getter = 'get' . $field;

      $type = $field_properties->get('type');
      $boost = $field_properties->get('boost');

      $value = $this->getModel()->$getter();

      // validate value
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
        throw new sfLuceneIndexerException('Field value returned is not a string (got a ' . gettype($value) . ' ) and it could be casted to a string.');
      }

      // handle a possible transformation function
      if ($field_properties->get('transform'))
      {
        if (!is_callable($field_properties->get('transform')))
        {
          throw new sfLuceneIndexerException('Transformation function cannot be called in field "' . $field . '" on model "' . $this->getModelName() . '"');
        }

        $value = call_user_func($field_properties->get('transform'), $value);
      }

      $zsl_field = $this->getLuceneField($type, strtolower($field), $value);
      $zsl_field->boost = $boost;

      $doc->addField($zsl_field);
    }

    return $doc;
  }

  protected function configureDocumentMetas($doc)
  {
    $doc->addField($this->getLuceneField('unindexed', 'sfl_model', $this->getModelName()));
    $doc->addField($this->getLuceneField('unindexed', 'sfl_type', 'model'));

    return $doc;
  }

  protected function configureDocumentCategories($doc)
  {
    // category support
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
  * Deletes the old model
  * @param BaseObject $this->getModel() The model to delete
  */
  public function delete()
  {
    $this->deleteGuid($this->getModelGuid());

    return $this;
  }

  /**
   * Determines if the provided model should be indexed.
   */
  protected function shouldIndex()
  {
    $properties = $this->getModelProperties();
    $method = $properties->get('validator');

    if ($method && method_exists($this->getModel(), $method))
    {
      return (bool) $this->getModel()->$method();
    }

    return true;
  }

  protected function getModelCategories()
  {
    $retval = array();

    if (sfConfig::get('sf_i18n'))
    {
      $i18n = $this->getSearch()->getContext()->getI18N();
      $i18n->setMessageSource(null, $this->getSearch()->getParameter('culture'));
    }

    // see: http://www.nabble.com/Lucene-and-n:m-t4449653s16154.html#a12695579
    foreach (parent::getModelCategories() as $category)
    {
      if (substr($category, 0, 1) == '%' && substr($category, -1, 1) == '%')
      {
        $category = substr($category, 1, -1);

        $getter = 'get' . $category;

        $getterValue = $this->getModel()->$getter();

        if (is_object($getterValue) && method_exists($getterValue, '__toString'))
        {
          $getterValue = $getterValue->__toString();
        }
        elseif (!is_scalar($getterValue))
        {
          throw new sfLuceneIndexerException('Category value returned is not a string (got a ' . gettype($getterValue) . ' ) and could not be transformed into a string.');
        }

        $retval[] = $getterValue;
      }
      else
      {
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

  public function getModelGuid()
  {
    return self::getGuid($this->getModelName() . '_' . $this->getModel()->hashCode());
  }
}