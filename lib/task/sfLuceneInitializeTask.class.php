<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
* Task that initializes all the configuration files.
*
* @author Carl Vondrick <carlv@carlsoft.net>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id$
*/

class sfLuceneInitializeTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name')
    ));

    $this->aliases = array('lucene-init');
    $this->namespace = 'lucene';
    $this->name = 'initialize';
    $this->briefDescription = 'Initializes the sfLucene configuration files';

    $this->detailedDescription = <<<EOF
The [lucene:intialize|INFO] initializes the configuration files for your application.

This task will simply create a skeleton of two [search.yml|COMMENT] files to get you started
with using sfLucene.  Do not run this task if you are upgrading.

If current search.yml are newer than the skeleton files, then nothing is done.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];

    $this->checkAppExists($app);
    $this->standardBootstrap($app);

    $skeletonDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeleton';
    $projectConfig = sfConfig::get('sf_config_dir') . DIRECTORY_SEPARATOR . 'search.yml';
    $appConfig = sfConfig::get('sf_app_config_dir') . DIRECTORY_SEPARATOR .'search.yml';

    $this->filesystem->copy($skeletonDir.DIRECTORY_SEPARATOR.'project'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'search.yml', $projectConfig);
    $this->filesystem->copy($skeletonDir.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'search.yml', $appConfig);
  }
}