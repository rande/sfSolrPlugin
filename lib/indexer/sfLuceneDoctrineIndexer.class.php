<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
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
  /**
  * Inserts the provided model into the index based off parameters in search.yml.
  * @param BaseObject $this->getModel() The model to insert
  */
  public function insert()
  {
    if (!$this->shouldIndex())
    {
      return $this;
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
    $doc = $this->configureDocumentCategories($doc);
    $doc = $this->configureDocumentMetas($doc);
    // add document
    $this->addDocument($doc, $this->getModelGuid());
    
    $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Inserted model "%s" from index with primary key = %s', $this->getModelName(), current($this->getModel()->identifier()))));

    // restore culture in symfony i18n detection
    if ($old_culture)
    {
      sfDoctrineRecord::setDefaultCulture($old_culture);
    }
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
      return (bool) $this->getModel()->$method();
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
}