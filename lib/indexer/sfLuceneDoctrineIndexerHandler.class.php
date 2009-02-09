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
  public function rebuildModel($name, $offset = null, $limit = null)
  {

    $options = $this->getSearch()->getParameter('models')->get($name);

    if(!$options)
    {
      throw new LogicException('The model \''.$name.'\' does not have any configurations');
    }
    
    $table = Doctrine :: getTable($name);
    $query = $this->getBaseQuery($name);

    if(is_numeric($offset) && is_numeric($limit))
    {
      $this->_rebuild($query, $offset, $limit);
      $query->free();
      $query->from($table->getComponentName());
    }
    else
    {

      $count = $query->count();
      $per   = $options->get('rebuild_limit');

      $totalPages = ceil($count / $per);

      for ($page = 0; $page < $totalPages; $page++)
      {
        $offset = $page * $per;
        $this->_rebuild($query, $offset, $per);
        $query->free();
        $query->from($table->getComponentName());
      }
    }
  }

  public function getBaseQuery($model)
  {
    $table = Doctrine::getTable($model);

    if(method_exists($table, 'getLuceneQuery'))
    {
      $query = $table->getLuceneQuery($this->getSearch());
    }
    else
    {
      $query = $table->createQuery();
    }
    
    return $query;
  }

  public function getCount($model)
  {
    $query = $this->getBaseQuery($model);
    
    return $query->count();
  }

  protected function _rebuild($query, $offset, $limit)
  {
    $collection = $query->limit($limit)->offset($offset)->execute();

    foreach($collection as $record)
    {
       
      $this->getFactory()->getModel($record)->save();
      unset($record);
    }
    unset($collection);
  }
}