<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Factory for indexer.  It determines the appropriate indexer to use.
 * @package sfLucenePlugin
 * @subpackage Indexer
 * @author Carl Vondrick
 * @version SVN: $Id$
 */

class sfLuceneIndexerFactory
{
  protected $search;

  public function __construct($search)
  {
    $this->search = $search;
  }

  public function getHandlers()
  {
    $factories = $this->search->getParameter('factories')->get('indexers');
    $retval = array();
    $model = 'sfLucene' . ucfirst(sfConfig::get('sf_orm', 'Propel')) . 'IndexerHandler';

    $indexers = array_merge(array('model' => array($model), 'action' => array('sfLuceneActionIndexerHandler')), $factories);

    foreach ($indexers as $label => $indexer)
    {
      if (!is_null($indexer) && isset($indexer[0]))
      {
        $indexer = $indexer[0];

        $retval[$label] = new $indexer($this->search);
      }
      else
      {
        unset($retval[$label]);
      }
    }

    return $retval;
  }

  public function getModel($instance)
  {
    $options = $this->search->getParameter('models')->get(get_class($instance));

    if ($options && $options->get('indexer'))
    {
      $indexer = $options->get('indexer');
    }
    else
    {
      $factories = $this->search->getParameter('factories')->get('indexers');

      if (isset($factories['model'][1]))
      {
        $indexer = $factories['model'][1];
      }
      else
      {
        $orm      = ucfirst(sfConfig::get('sf_orm', 'Propel'));
        $indexer  = 'sfLucene' . $orm . 'Indexer';
      }
    }

    return new $indexer($this->search, $instance);
  }

  public function getAction($module, $action)
  {
    $factories = $this->search->getParameter('factories')->get('indexers');

    if (isset($factories['action'][1]))
    {
      $indexer = $factories['action'][1];
    }
    else
    {
      $indexer = 'sfLuceneActionIndexer';
    }

    return new $indexer($this->search, $module, $action);
  }
}