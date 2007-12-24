<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
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
}
