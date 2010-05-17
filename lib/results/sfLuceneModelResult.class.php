<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Result from the model indexing engine.
 * @package    sfLucenePlugin
 * @subpackage Results
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id$
 */
abstract class sfLuceneModelResult extends sfLuceneResult
{
  /**
  * Deduces the title to be displayed in search results.
  */
  public function getInternalTitle()
  {
    if ($this->result->__isset('sfl_title')) {
        return $this->result->__get('sfl_title');
    }

    $model = $this->retrieveModel();

    if ($model->has('title') && !is_null($model->get('title')))
    {
      return $this->getResult()->__get($model->get('title'));
    }
      
    foreach (array('title', 'subject') as $check)
    {
      if ($model->get('fields')->has($check))
      {
        return strip_tags($this->getResult()->__get($check));
      }
    }
    

    return 'No title available.';
  }
  
  /**
  * Deduces the title to be displayed in search results.
  */
  public function getInternalModel()
  {
    $model = $this->retrieveModel();

    if (!$this->result->__isset('sfl_model'))
    {
      
      throw new sfLuceneException('no sfl_model value set for a sfLuceneModelResult instance');
    }
    
    return $this->result->__get('sfl_model');
  }

  /**
  * Gets the URI that this model links to
  */
  public function getInternalUri()
  {
    $model = $this->retrieveModel();

    if (!$model->has('route'))
    {
      throw new sfLuceneIndexerException(sprintf('A route for model "%s" was not defined.', $this->getInternalModel()));
    }

    return preg_replace('/%(\w+?)%/e', '$this->result->__get("$1")', $model->get('route'));
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
    if ($this->result->__isset('sfl_description')) {
        return $this->result->__get('sfl_description');
    }

    $model = $this->retrieveModel();

    if ($model->has('description') && !is_null($model->get('description')))
    {
      return strip_tags($this->result->__get($model->get('description')));
    }

    foreach (array('description','summary','about') as $check)
    {
      if ($model->get('fields')->has($check))
      {
        return strip_tags($this->result->__get($check));
      }
    }

    return 'No description available.';
  }

  /**
  * Retrieves properties for this model.
  */
  protected function retrieveModel()
  {
    return $this->search->getParameter('models')->get($this->getSflModel());
  }
  
}
