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
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'search'),
      new sfCommandOption('offset', null, sfCommandOption::PARAMETER_REQUIRED, 'The offset were the index should start', null),
      new sfCommandOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'The number number max of record to index from the offset', null),
      new sfCommandOption('delete', null, sfCommandOption::PARAMETER_OPTIONAL, 'set to true to delete all related index', false),
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
    
    $offset  = $options['offset'];
    $limit   = $options['limit'];
    $delete  = $options['delete'];
    

    $this->checkAppExists($app);
    $this->standardBootstrap($app, $options['env']);

    if(sfConfig::get('sf_orm') != 'doctrine')
    {
      
      throw new LogicException('This feature is only implemented for Doctrine ORM');
    }
    
    $instance = sfLucene::getInstance($index, $culture, $this->configuration);
    
    $this->setupEventDispatcher($instance);
    
    if($delete)
    {
      $query = 'sfl_model:'.$model;
      $instance->getLucene()->deleteByQuery($query);
      $instance->getLucene()->commit();
    }
    
    $this->rebuild($instance, $model, $offset, $limit);

  }

  protected function rebuild($search, $model, $offset, $limit)
  {
    $start = microtime(true);

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format(sprintf('Processing "%s/%s" now...', $search->getParameter('name'), $search->getParameter('culture')), array('fg' => 'red', 'bold' => true)))));

    $search->rebuildIndexModel($model, $offset, $limit);
    $search->optimize();
    $search->commit();

    $time = microtime(true) - $start;

    $final = $this->formatter->format('Done!', 'INFO') . ' Indexed ' . $this->formatter->format($search->numDocs(), array('fg' => 'cyan')) . ' documents in ' . $this->formatter->format(number_format($time, 5), array('fg' => 'cyan')) . ' seconds.';
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($final)));
  }
}