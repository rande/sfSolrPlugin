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

class sfLuceneCleanupTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('confirmation', sfCommandArgument::OPTIONAL, 'Confirmation to delete')
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

    $remove = sfLuceneToolkit::getDirtyIndexRemains();

    if (count($remove) == 0)
    {
      $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format('Nothing to do!', 'INFO'))));
    }
    elseif ($arguments['confirmation'] == 'delete')
    {
      $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format('Delete confirmation provided.  Deleting directories now...' , array('fg' => 'red', 'bold' => true)))));

      foreach ($remove as $dir)
      {
        sfToolkit::clearDirectory($dir);
        rmdir($dir);

        $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('dir-', $dir))));
      }
    }
    else
    {
      $messages = array($this->formatter->format('The following directories are alien and not referenced by your configuration:', 'INFO'));

      for ($c = count($remove), $x = 0; $x < $c; $x++)
      {
        $messages[] = '  ' . ($x + 1) . ') ' . $remove[$x];
      }

      $messages[] = '';

      $messages[] = 'These directories were ' . $this->formatter->format('not', array('fg' => 'red', 'bold' => true)) . ' deleted.  To delete these directories, please run:';
      $messages[] = '';
      $messages[] = '     ' . $this->formatter->format('symfony lucene:clean ' . $arguments['application'] . ' delete', 'INFO');
      $messages[] = '';

      $this->dispatcher->notify(new sfEvent($this, 'command.log', $messages));
    }
  }
}