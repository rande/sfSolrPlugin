<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
* This class represents a base task for all sfLucene tasks.
*
* @author Carl Vondrick <carl@carlsoft.net>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id$
*/

abstract class sfLuceneBaseTask extends sfBaseTask
{
  protected function standardBootstrap($app, $env = 'search')
  {
    $this->bootstrapSymfony($app, $env, true);

    sfAutoload::getInstance()->autoload('Propel'); // see ticket #2613
  }

  protected function setupEventDispatcher($search)
  {
    $source = $search->getEventDispatcher();
    $target = $this->dispatcher;
    $formatter = $this->formatter;

    new sfLuceneEventConnectorLogger($source, 'lucene.log', $target, 'command.log', $formatter, 'Lucene');
    new sfLuceneEventConnectorLogger($source, 'indexer.log', $target, 'command.log', $formatter, 'Indexer');
  }
}