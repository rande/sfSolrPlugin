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
* Task that initializes a skeleton module to customize.
*
* @author Carl Vondrick <carl@carlsoft.net>
* @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id$
*/

class sfLuceneInitializeModuleTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('module', sfCommandArgument::OPTIONAL, 'The module name', 'sfLucene'),
      new sfCommandArgument('index', sfCommandArgument::OPTIONAL, 'The sfLucene index', null)
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'search')
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

If you specify the optional parameters, you can customize the default module
name and the index the module is linked to.  For example:

  [symfony lucene:init-module frontend myLucene foo|INFO]

will create a myLucene module in the frontend application and configure it to
search from the "foo" index.   You can create multiple search modules this way.

If you omit the third argument, then sfLucene will guess the best index name.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];

    $this->checkAppExists($app, $options['env']);
    $this->standardBootstrap($app);

    $skeletonDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeleton';
    $moduleDir = sfConfig::get('sf_app_module_dir') . DIRECTORY_SEPARATOR . $arguments['module'];

    if (is_dir($moduleDir))
    {
      throw new sfCommandException(sprintf('The module "%s" already exists in the "%s" application.', $moduleDir, $app));
    }

    $mirrorDir = $skeletonDir . DIRECTORY_SEPARATOR . 'module';

    $finder = sfFinder::type('any')->ignore_version_control()->discard('.sf');
    $this->getFilesystem()->mirror($mirrorDir, $moduleDir, $finder);

    $constants = array('MODULE_NAME' => $arguments['module']);

    if ($arguments['index'])
    {
      $constants['CALLABLE'] = 'sfLucene::getInstance(\'' . $arguments['index'] . '\')';
    }
    else
    {
      $constants['CALLABLE'] = 'sfLucene::getInstance($name, $culture, $this->context->getConfiguration());';
    }

    $finder = sfFinder::type('file')->ignore_version_control()->discard('.sf');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '##', '##', $constants);
  }
}