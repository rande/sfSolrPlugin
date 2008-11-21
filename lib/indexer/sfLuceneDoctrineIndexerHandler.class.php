<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package sfLucenePlugin
 * @subpackage Indexer
 * @author Carl Vondrick
 */

class sfLuceneDoctrineIndexerHandler extends sfLuceneModelIndexerHandler
{
  public function rebuildModel($name)
  {
    $options = $this->getSearch()->getParameter('models')->get($name);

    // get the rebuild limit
    $per   = $options->get('rebuild_limit');

    // get the table object
    $table = Doctrine :: getTable($name);

    // calculate total number of pages
    $count = $table->count();
    
    $totalPages = ceil($count / $per);

    // create the query one time
    $query = $table->createQuery();
    for ($page = 0; $page < $totalPages; $page++)
    {
      $collection = $query->limit($per)->offset($page * $per)->execute();
      
      foreach($collection as $record)
      {
        $this->getFactory()->getModel($record)->save();
        unset($record);
      }
      unset($collection);
      $query->free();
      $query->from($table->getComponentName());
    }
  }
}