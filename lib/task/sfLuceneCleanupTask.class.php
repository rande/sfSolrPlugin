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
* @version SVN: $Id$
*/

class sfLuceneCleanupTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('confirmation', sfCommandArgument::OPTIONAL, 'Confirmation to delete, enter `delete`')
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'search')
    ));

    $this->aliases = array('lucene-clean');
    $this->namespace = 'lucene';
    $this->name = 'clean';
    $this->briefDescription = 'Clean the sfLucene index for stray files';

    $this->detailedDescription = <<<EOF
The [lucene:clean|INFO] task searches the index and deletes stray files.

If you run [lucene:clean|INFO] without the [confirmation|COMMENT] argument, this task
spits out which directories are alien.  For example:

    [./symfony lucene:clean myapp|INFO]

If you set the [confirmation|COMMENT] argument to [delete|COMMENT], the directories are
instantly deleted from the filesystem.  [Warning:|ERROR] You cannot undo this!

      [./symfony lucene:clean myapp delete|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->standardBootstrap($arguments['application'], $options['env']);

    if ($arguments['confirmation'] == 'delete')
    {
      $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format('Delete confirmation provided.  Deleting index now...' , array('fg' => 'red', 'bold' => true)))));

      $instances = sfLucene::getAllInstances();

      foreach ($instances as $instance)
      {
        $instance->getLucene()->deleteByQuery('*:*');
        $instance->getLucene()->commit();
        $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format('Delete '.$instance->getPublicName() , array('fg' => 'red', 'bold' => true)))));
      }
    }
  }
}