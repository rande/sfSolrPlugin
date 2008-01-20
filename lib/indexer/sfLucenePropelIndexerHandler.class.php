<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package sfLucenePlugin
 * @subpackage Indexer
 * @author Carl Vondrick
 * @version SVN: $Id$
 */

class sfLucenePropelIndexerHandler extends sfLuceneModelIndexerHandler
{
  public function rebuildModel($name)
  {
    $options = $this->getSearch()->getParameter('models')->get($name);

    $per = $options->get('rebuild_limit');
    $peer = $options->get('peer');

    // calculate total number of pages
    $count = call_user_func(array($peer, 'doCount'), new Criteria);

    $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Discovered %d instances of model "%s"', $count, $name)));

    $totalPages = ceil($count / $per);

    $propel13 = class_exists('PropelPDO', true) ? true : false;

    for ($page = 0; $page < $totalPages; $page++)
    {
      $c = new Criteria;
      $c->setOffset($page * $per);
      $c->setLimit($per);

      if($propel13)
      {
        $rs = call_user_func(array($peer, 'doSelectStmt'), $c);
        while ($row = $rs->fetch(PDO::FETCH_NUM))
        {
          $instance = new $name;
          $instance->hydrate($row);

          $this->getFactory()->getModel($instance)->save();

          unset($instance); // free memory
        }
      }
      else
      {
        $rs = call_user_func(array($peer, 'doSelectRS'), $c);
        while ($rs->next())
        {
          $instance = new $name;
          $instance->hydrate($rs);

          $this->getFactory()->getModel($instance)->save();

          unset($instance); // free memory
        }
      }

      unset($rs); // free memory
    }
  }
}