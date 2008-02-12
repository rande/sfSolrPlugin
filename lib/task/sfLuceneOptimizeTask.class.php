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
* This task optimizes all the indexes.
*
* @author Carl Vondrick <carl@carlsoft.net>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id$
*/

class sfLuceneOptimizeTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name')
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'search')
    ));

    $this->aliases = array('lucene-optimize');
    $this->namespace = 'lucene';
    $this->name = 'optimize';
    $this->briefDescription = 'Optimizes the sfLucene index';

    $this->detailedDescription = <<<EOF
The [lucene:optimize|INFO] task optimizes all the sfLucene indexes.  No data is loss.

[Warning:|ERROR] Depending on how much data the indexes contain, this may take from a
couple of minutes to a couple of hours.  If you run this on a production server,
the server will operate slower than normal until it completes optimization.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];

    $this->checkAppExists($app);
    $this->standardBootstrap($app, $options['env']);

    $start = microtime(true);

    $instances = sfLucene::getAllInstances();

    foreach ($instances as $instance)
    {
      $this->optimize($instance);
    }

    $time = microtime(true) - $start;

    $final = $this->formatter->format('All done!', array('fg' => 'red', 'bold' => true)) . ' Optimized for ' . $this->formatter->format(count($instances), array('fg' => 'cyan'));
    $final .= count($instances) == 1 ? ' index in ' : ' indexes in ';
    $final .= $this->formatter->format(number_format($time, 5), array('fg' => 'cyan')) . ' seconds.';

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array('', $final)));
  }

  protected function optimize($search)
  {
    $start = microtime(true);

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format(sprintf('Optimizing "%s/%s" now...', $search->getParameter('name'), $search->getParameter('culture')), array('fg' => 'red', 'bold' => true)))));

    $search->optimize();

    $time = microtime(true) - $start;

    $final = $this->formatter->format('Done!', 'INFO') . ' Optimized ' . $this->formatter->format($search->numDocs(), array('fg' => 'cyan')) . ' documents in ' . $this->formatter->format(number_format($time, 5), array('fg' => 'cyan')) . ' seconds.';
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($final)));
  }
}