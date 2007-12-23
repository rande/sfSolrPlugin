<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Result from the model indexing engine.
 * @package    sfLucenePlugin
 * @subpackage Results
 * @author     Carl Vondrick <carlv@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneModelResult extends sfLuceneResult
{
  /**
  * Deduces the title to be displayed in search results.
  */
  public function getInternalTitle()
  {
    $model = $this->retrieveModel();

    if ($model->has('title'))
    {
      return $this->result->getDocument()->getFieldValue($model->get('title'));
    }
    else
    {
      foreach (array('title', 'subject') as $check)
      {
        if ($model->get('fields')->has($check))
        {
          return strip_tags($this->result->getDocument()->getFieldValue($check));
        }
      }
    }

    return 'No title available.';
  }

  /**
  * Gets the URI that this model links to
  */
  public function getInternalUri()
  {
    $model = $this->retrieveModel();

    if (!$model->has('route'))
    {
      throw new sfLuceneIndexerException(sprintf('A route for model "%s" was not defined in the search.yml file.', $this->getInternalModel()));
    }

    return preg_replace_callback('/%(\w+?)%/', array($this, 'internalUriCallback'), $model->get('route'));
  }

  /**
  * Callback for self::getInternalUri()
  */
  protected function internalUriCallback($matches)
  {
    return $this->result->getDocument()->getFieldValue($matches[1]);
  }

  /**
  * Gets the partial specified for this result.
  */
  public function getInternalPartial($module = 'sfLucene')
  {
    $model = $this->retrieveModel();

    if ($model->get('partial'))
    {
      return $model->get('partial');
    }

    return parent::getInternalPartial($module);
  }

  public function getInternalDescription()
  {
    $model = $this->retrieveModel();

    if ($model->has('description'))
    {
      return strip_tags($this->result->getDocument()->getFieldValue($model->get('description')));
    }

    foreach (array('description','summary','about') as $check)
    {
      if ($model->get('fields')->has($check))
      {
        return strip_tags($this->result->getDocument()->getFieldValue($check));
      }
    }

    return 'No description available.';
  }

  /**
  * Retrieves properties for this model.
  */
  protected function retrieveModel()
  {
    return $this->search->getParameter('models')->get($this->getInternalModel());
  }
}