<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
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
  public function rebuildModel($name, $start_page = null, $limit = null)
  {
    
    $options    = $this->getSearch()->getParameter('models')->get($name);
    $start_page = $start_page === null ? 1 : $start_page;
    $limit      = is_numeric($limit) ? $limit : $options->get('rebuild_limit');

    if(!$options)
    {
      throw new LogicException('The model \''.$name.'\' does not have any configurations');
    }
    
    $table = Doctrine :: getTable($name);
    $query = $this->getBaseQuery($name);

    $count = $query->count();
    
    $totalPages = ceil($count / $limit);
    
    for ($page = $start_page; $page <= $totalPages; $page++)
    {
      
      $this->getSearch()->getEventDispatcher()->notifyUntil(new sfEvent($this, 'lucene.indexing_loop', array(
        'model' => $name,
        'page'  => $page,
        'limit' => $limit
      )));
    
      $offset = ($page - 1) * $limit;
      
      $this->batchRebuild($query, $offset, $limit);
    }
  }

  public function getBaseQuery($model)
  {
    $table = Doctrine_Core::getTable($model);

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

  public function batchRebuild($query, $offset, $limit)
  { 

    $start = microtime(true);
    $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this,'indexer.log', array('batch indexing - offset:%s, limit:%s.', $offset, $limit)));
    
    $collection = $query->limit($limit)->offset($offset)->execute();

    $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this,'indexer.log', array('  - fetching and hydrating objects in %s seconds.', number_format(microtime(true) - $start, 5))));
    
    $documents = array();
    $pks = array();
    
    $start = microtime(true);
    foreach($collection as $record)
    {  
      $start1 = microtime(true);
      
      $indexer = $this->getFactory()->getModel($record);

      $doc = $indexer->getDocument();
      // $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this,'indexer.log', array('  - creating one object in %s seconds.', number_format(microtime(true) - $start1, 5))));

      if(!$doc)
      {
        $this->getSearch()->getEventDispatcher()->notify( new sfEvent($this, 'application.log', array( sprintf('invalid document %s [id:%s]: ', get_class($record), current($record->identifier())), 'priority' => sfLogger::ALERT )));
        $this->getSearch()->getEventDispatcher()->notify( new sfEvent($this, 'indexer.log', array( sprintf('  - invalid document %s [id:%s]: ', get_class($record), current($record->identifier())), 'priority' => sfLogger::ALERT )));
        
        continue;
      }

      $documents[$doc->sfl_guid] = $doc;
      
      $field = $doc->getField('id');
      
      $pks[] = $field['value']['0'];
    }
    
    $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this,'indexer.log', array('  - creating solr documents in %s seconds.', number_format(microtime(true) - $start, 5))));

    $search_engine =  $this->getSearch()->getSearchService();

    try
    {
      $start = microtime(true);
      
      $search_engine->deleteByMultipleIds(array_keys($documents));
      $search_engine->addDocuments($documents);
      $search_engine->commit();

      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this,'indexer.log', array('  - indexing %s objects in %s seconds.', count($documents),  number_format(microtime(true) - $start, 5))));      
    }
    catch(Exception $e)
    {
      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('indexing Failed indexing object - primary keys [%s]', implode(', ', $pks))));       
      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array('indexing document fail : '.$e->getMessage(),'priority' => sfLogger::ALERT)));
    }
  }
}