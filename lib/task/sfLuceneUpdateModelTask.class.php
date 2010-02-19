<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfLuceneBaseTask.class.php');

/**
* This task rebuilds the entire index.
*
* @author Carl Vondrick <carl@carlsoft.net>
* @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id: sfLuceneRebuildTask.class.php 7466 2008-02-12 05:34:08Z dwhittle $
*/

class sfLuceneUpdateModelTask extends sfLuceneBaseTask
{    
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('index', sfCommandArgument::REQUIRED, 'The name of the index to rebuild'),
      new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'The name of the culture to rebuild'),
      new sfCommandArgument('model', sfCommandArgument::REQUIRED, 'The model to reindex from the index'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_OPTIONAL, 'The environment', 'search'),
      new sfCommandOption('state', null, sfCommandOption::PARAMETER_OPTIONAL, 'If state is set to true then the task will save the state on memory limit exception', false),
      new sfCommandOption('page', null, sfCommandOption::PARAMETER_OPTIONAL, 'The page where the index should start', 1),
      new sfCommandOption('limit', null, sfCommandOption::PARAMETER_OPTIONAL, 'The number number max of record to index from the page', null),
      new sfCommandOption('delete', null, sfCommandOption::PARAMETER_OPTIONAL, 'set to true to delete all related index - page should', false),
    ));

    $this->aliases = array('lucene-update-model');
    $this->namespace = 'lucene';
    $this->name = 'update-model';
    $this->briefDescription = 'Update the model indexation for a sfLucene index';

    $this->detailedDescription = <<<EOF
The [lucene:update-model|INFO] task update one model for one index.

It configures sfLucene according to your [search.yml|COMMENT] files and then update the model
index.

[Warning:|ERROR] Depending on how much you are indexing, this may take from a couple of
minutes to a couple of hours.  If you run this on a production server, the search
function will offline until it completes indexing.

For example:

  [./symfony lucene:update-model frontend MyIndex fr ModelName|INFO]

will initiate rebuilding.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app     = $arguments['application'];
    $index   = $arguments['index'];
    $culture = $arguments['culture'];
    $model   = $arguments['model'];
    
    $state   = $options['state'];
    $limit   = $options['limit'];
    $page    = $options['page'];
    $delete  = $options['delete'];
    
    $this->checkAppExists($app);
    $this->standardBootstrap($app, $options['env']);

    if(sfConfig::get('sf_orm') != 'doctrine')
    {
      
      throw new LogicException('This feature is only implemented for Doctrine ORM');
    }
    
    if($state)
    {
      // use state file
      // the state file only contains the last page used and the limit
      $state = $this->getState($model);
      $page  = $state['page'];
      $limit = $state['limit'];
      $this->logSection('lucene', sprintf('Loading state page:%s, limit:%s', $page, $limit));
    }
    
    $this->dispatcher->connect('lucene.indexing_loop', array($this, 'handleMemoryLimitEvent'));

    $instance = sfLucene::getInstance($index, $culture, $this->configuration);
    
    $this->setupEventDispatcher($instance);
    
    if($delete)
    {
      $query = 'sfl_model:'.$model;
      $instance->getSearchService()->deleteByQuery($query);
      $instance->getSearchService()->commit();
    }
    
    $this->rebuild($instance, $model, $page, $limit);
    
    if($state)
    {
      $file = $this->getFilestatePath($model);
      $this->getFilesystem()->remove($file);
    }
  }
  
  public function handleMemoryLimitEvent(sfEvent $event)
  {
    
    // store the current state
    $this->saveState($event['model'], array(
      'limit' => $event['limit'],
      'page'  => $event['page']
    ));
    
    $event->setProcessed(true);
  }
  
  public function getFilestatePath($model)
  {
    
    return sprintf(sfConfig::get('sf_data_dir').'/solr_index/update_%s.state', sfInflector::underscore($model));
  }
  
  public function getState($model)
  {
    
    $file = $this->getFilestatePath($model);
    
    $state = false;
    
    if(is_file($file))
    {
      $state = unserialize(@file_get_contents($file));

    }
    
    if(!is_array($state))
    {
      $state = array(
        'page' => 1,
        'limit' => null,
      );
    }
    
    return $state;
  }
  
  public function saveState($model, $state)
  {
    $file = sprintf(sfConfig::get('sf_data_dir').'/solr_index/update_%s.state', sfInflector::underscore($model));
    file_put_contents($file, serialize($state));
  }

  protected function rebuild($search, $model, $offset, $limit)
  {
    $start = microtime(true);

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format(sprintf('Processing "%s/%s" now...', $search->getParameter('name'), $search->getParameter('culture')), array('fg' => 'red', 'bold' => true)))));

    $search->rebuildIndexModel($model, $offset, $limit);

    $search->commit();
    $search->optimize();

    $time = microtime(true) - $start;

    $final = $this->formatter->format('Done!', 'INFO') . ' Indexed ' . $this->formatter->format($search->numDocs(), array('fg' => 'cyan')) . ' documents in ' . $this->formatter->format(number_format($time, 5), array('fg' => 'cyan')) . ' seconds.';
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($final)));
  }
}