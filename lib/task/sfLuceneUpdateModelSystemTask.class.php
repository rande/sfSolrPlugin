<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfLuceneBaseTask.class.php');

/**
 * This task rebuilds the entire index.
 *
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @package sfLucenePlugin
 * @subpackage Tasks
 * @version SVN: $Id: sfLuceneRebuildTask.class.php 7466 2008-02-12 05:34:08Z dwhittle $
 */

class sfLuceneUpdateModelSystemTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
    new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
    new sfCommandArgument('index', sfCommandArgument::REQUIRED, 'The name of the index to rebuild'),
    new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'The name of the culture to rebuild'),
    ));

    $this->addOptions(array(
    new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'search'),
    new sfCommandOption('model', null, sfCommandOption::PARAMETER_OPTIONAL, 'The model to reindex', null),
    new sfCommandOption('limit', null, sfCommandOption::PARAMETER_OPTIONAL, 'The environment', 50),
    ));

    $this->aliases = array('lucene-rebuild-system');
    $this->namespace = 'lucene';
    $this->name = 'update-model-system';
    $this->briefDescription = 'Update one index by using sub process to avoid memory leak (Doctrine Only)';

    $this->detailedDescription = <<<EOF
The [lucene:rebuild|INFO] task rebuilds all the sfLucene indexes.

It configures sfLucene according to your [search.yml|COMMENT] files and then rebuilds
all the indexes for every culture.

[Warning:|ERROR] Depending on how much you are indexing, this may take from a couple of
minutes to a couple of hours.  If you run this on a production server, the search
function will offline until it completes indexing.

For example:

  [./symfony lucene:rebuild myapp|INFO]

will initiate rebuilding.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app     = $arguments['application'];
    $index   = $arguments['index'];
    $culture = $arguments['culture'];

    $limit   = $options['limit'];
    $model   = $options['model'];

    $this->checkAppExists($app);
    $this->standardBootstrap($app, $options['env']);

    if(sfConfig::get('sf_orm') != 'doctrine')
    {
      
      throw new LogicException('This feature is only implemented for Doctrine ORM');
    }
    
    
    $start = microtime(true);

    $search = sfLucene::getInstance($index, $culture, is_null($model));
    $search->optimize();
    
    $this->setupEventDispatcher($search);

    $models = $search->getParameter('models')->getAll();

    $factory = new sfLuceneIndexerFactory($search);
    $handler = null;
    foreach($factory->getHandlers() as $handler)
    {
      if($handler instanceof sfLuceneModelIndexerHandler)
      {
        break;
      }
    }

    if(!$handler instanceof sfLuceneModelIndexerHandler)
    {
      throw new LogicException('No sfLuceneModelIndexerHandler defined !');
    }

    if($model)
    {
      $this->update($handler, $app, $index, $culture, $model, $limit);
    }
    else
    {
      foreach($models as $model => $params)
      {
        $this->update($handler, $app, $index, $culture, $model, $limit);
      }
    }
    
    $time = microtime(true) - $start;

   $final = $this->formatter->format('Update index done !!', array('fg' => 'green', 'bold' => true));
   $final .= $this->formatter->format(number_format($time, 5), array('fg' => 'cyan')) . ' seconds.';

   $this->dispatcher->notify(new sfEvent($this, 'command.log', array('', $final)));
  }

  public function update($handler, $app, $index, $culture, $model, $limit)
  {
    $page   = 0;
    $count    = $handler->getCount($model);
    $num_pages = ceil($count / $limit);

    do
    {
      $offset = $page * $limit;
      $final = $this->formatter->format('Updating model='.$model.', page='.$page.'/'.$num_pages, array('fg' => 'green', 'bold' => true));
      
      $this->dispatcher->notify(new sfEvent($this, 'command.log', array('', $final)));
      
      $command = sprintf('%s/symfony lucene:update-model %s %s %s %s --limit=%s --offset=%s',
        $this->configuration->getRootDir(),
        $app,
        $index,
        $culture,
        $model,
        $limit,
        $offset
      );

      echo $command."\n";
      system($command);

    } while((++$page < $num_pages ?  true : false));
  }
}