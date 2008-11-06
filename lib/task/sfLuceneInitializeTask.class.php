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
* Task that initializes all the configuration files.
*
* @author Carl Vondrick <carl@carlsoft.net>
* @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
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

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'search')
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
    $this->standardBootstrap($app, $options['env']);

    $skeletonDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeleton';
    $projectConfig = sfConfig::get('sf_config_dir') . DIRECTORY_SEPARATOR . 'search.yml';
    $appConfig = sfConfig::get('sf_app_config_dir') . DIRECTORY_SEPARATOR .'search.yml';

    $this->getFilesystem()->copy($skeletonDir.DIRECTORY_SEPARATOR.'project'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'search.yml', $projectConfig);
    $this->getFilesystem()->copy($skeletonDir.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'search.yml', $appConfig);
  }
}