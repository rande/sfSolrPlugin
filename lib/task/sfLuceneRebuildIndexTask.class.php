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
* @author Carl Vondrick <carl@carlsoft.net>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id: sfLuceneRebuildTask.class.php 7466 2008-02-12 05:34:08Z dwhittle $
*/

class sfLuceneRebuildIndexTask extends sfLuceneBaseTask
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
    ));

    $this->aliases = array('lucene-rebuild-index');
    $this->namespace = 'lucene';
    $this->name = 'rebuild-index';
    $this->briefDescription = 'Rebuilds the sfLucene index based on its name and culture';

    $this->detailedDescription = <<<EOF
The [lucene:rebuild-index|INFO] task rebuild one sfLucene index.

It configures sfLucene according to your [search.yml|COMMENT] files and then rebuild
the index for the named culture.

[Warning:|ERROR] Depending on how much you are indexing, this may take from a couple of
minutes to a couple of hours.  If you run this on a production server, the search
function will offline until it completes indexing.

For example:

  [./symfony rebuild-index myapp index1 en]

will initiate rebuilding.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app     = $arguments['application'];
    $index   = $arguments['index'];
    $culture = $arguments['culture'];
    
    $this->checkAppExists($app);
    $this->standardBootstrap($app, $options['env']);

    $start = microtime(true);

    $instance = sfLucene::getInstance($index, $culture, true);
    
    $this->setupEventDispatcher($instance);
    
    $this->rebuild($instance);
    
    $time = microtime(true) - $start;

    $final = $this->formatter->format('All done!', array('fg' => 'red', 'bold' => true)) . ' Rebuilt for ' . $this->formatter->format(count($instances), array('fg' => 'cyan'));
    $final .= count($instances) == 1 ? ' index in ' : ' indexes in ';
    $final .= $this->formatter->format(number_format($time, 5), array('fg' => 'cyan')) . ' seconds.';

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array('', $final)));
  }

  protected function rebuild($search)
  {
    $start = microtime(true);

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format(sprintf('Processing "%s/%s" now...', $search->getParameter('name'), $search->getParameter('culture')), array('fg' => 'red', 'bold' => true)))));

    $search->rebuildIndex();
    
    $search->optimize();
    $search->commit();

    $time = microtime(true) - $start;

    $final = $this->formatter->format('Done!', 'INFO') . ' Indexed ' . $this->formatter->format($search->numDocs(), array('fg' => 'cyan')) . ' documents in ' . $this->formatter->format(number_format($time, 5), array('fg' => 'cyan')) . ' seconds.';
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($final)));
  }
}