<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
* Task that initializes a skeleton module to customize.
*
* @author Carl Vondrick <carlv@carlsoft.net>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id$
*/

class sfLuceneInitializeModuleTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name')
    ));

    $this->aliases = array('lucene-init-module');
    $this->namespace = 'lucene';
    $this->name = 'init-module';
    $this->briefDescription = 'Initialize a skeleton sfLucene module for overloading';

    $this->detailedDescription = <<<EOF
The [lucene:init-module|INFO] initializes a base module for overloading.

This task will simply create a skeleton of of an extended sfLucene module in the
application specified.  By extending this module, you can customize the presentation
for sfLucene without too much work.

If current skeleton files are newer than the base files, then nothing is done.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];

    $this->checkAppExists($app);
    $this->standardBootstrap($app);

    $skeletonDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeleton';
    $moduleDir = sfConfig::get('sf_app_module_dir');

    $mirrorDir = $skeletonDir . DIRECTORY_SEPARATOR . 'module';

    $finder = sfFinder::type('any')->ignore_version_control()->discard('.sf');
    $this->filesystem->mirror($mirrorDir, $moduleDir, $finder);
  }
}