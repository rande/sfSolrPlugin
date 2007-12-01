<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
* This task rebuilds the entire index.
*
* @author Carl Vondrick <carlv@carlsoft.net>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id$
*/

class sfLuceneRebuildTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name')
    ));

    $this->aliases = array('lucene-rebuild');
    $this->namespace = 'lucene';
    $this->name = 'rebuild';
    $this->briefDescription = 'Rebuilds the sfLucene index';

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
    $app = $arguments['application'];

    $this->checkAppExists($app);
    $this->standardBootstrap($app);

    $start = microtime(true);

    $instances = sfLucene::getAllInstances(true);

    foreach ($instances as $instance)
    {
      try
      {
        $this->rebuild($instance);
      }
      catch (Exception $e)
      {
        sfException::createFromException($e)->printStackTrace();
      }
    }

    $time = microtime(true) - $start;

    $final = $this->formatter->format('All done!', array('fg' => 'red', 'bold' => true)) . ' Rebuilt for ' . $this->formatter->format(count($instances), array('fg' => 'cyan'));
    $final .= count($instances) == 1 ? ' index in ' : ' indexes in ';
    $final .= $this->formatter->format(number_format($time, 5), array('fg' => 'cyan')) . ' seconds.';

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($final)));
  }

  protected function rebuild($search)
  {
    $start = microtime(true);

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format(sprintf('Processing "%s/%s" now...', $search->getName(), $search->getCulture()), array('fg' => 'red', 'bold' => true)))));

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('lucene', 'Rebuilding...'))));
    $search->rebuildIndex();

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('lucene', 'Optimizing...'))));
    $search->optimize();

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('lucene', 'Comitting..'))));
    $search->commit();

    $time = microtime(true) - $start;

    $final = $this->formatter->format('Done!', 'INFO') . ' Indexed ' . $this->formatter->format($search->numDocs(), array('fg' => 'cyan')) . ' documents in ' . $this->formatter->format(number_format($time, 5), array('fg' => 'cyan')) . ' seconds.';
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($final)));
  }
}