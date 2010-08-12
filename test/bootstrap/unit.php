<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * @package sfLucenePlugin
  * @subpackage Test
  * @author Carl Vondrick
  * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
  * @version SVN: $Id$
  */

error_reporting(E_ALL);

$_SERVER['SYMFONY'] = dirname(__FILE__).'/../../../../lib/vendor/symfony/lib';

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

$configuration = new sfProjectConfiguration(dirname(__FILE__).'/../fixtures/project');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

function sfSolrPlugin_autoload_again($class)
{
  $autoload = sfSimpleAutoload::getInstance();
  $autoload->reload();
  return $autoload->autoload($class);
}
spl_autoload_register('sfSolrPlugin_autoload_again');

if (file_exists($config = dirname(__FILE__).'/../../config/sfSolrPluginConfiguration.class.php'))
{
  require_once $config;
  $plugin_configuration = new sfSolrPluginConfiguration($configuration, dirname(__FILE__).'/../..', 'sfSolrPlugin');
}
else
{
  $plugin_configuration = new sfPluginConfigurationGeneric($configuration, dirname(__FILE__).'/../..', 'sfSolrPlugin');
}


// $_test_dir = realpath(dirname(__FILE__). '/../../../..');
// 
require_once(dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php');
// require_once dirname(__FILE__) . '/../fixtures/project/apps/frontend/config/frontendConfiguration.class.php';
// 
// $configuration = new ProjectConfiguration($_test_dir);
// include($configuration->getSymfonyLibDir().'/vendor/lime/lime.php');
// 
// $autoload = sfSimpleAutoload::getInstance();
// $autoload->addDirectory(dirname(__FILE__).'/../bin/model');
// $autoload->addDirectory(dirname(__FILE__).'/../../lib');
// 
// // var_dump(dirname(__FILE__).'/../bin/model'); die();
// sfSimpleAutoload::register();

require_once dirname(__FILE__) . '/../limeade/limeade_loader.php';
require_once dirname(__FILE__) . '/../bin/limeade_lucene.php';

limeade_loader::all();

$app_configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);

$standard_response = '{"responseHeader":{"status":0,"QTime":0},"response":{"numFound":3,"start":0,"docs":[{"description":"symfony fan boy","id":1,"name":"rande","sfl_guid":"GUID_1","skills":["symfony","php","objective-c"]},{"description":"django fan boy","id":2,"name":"rande2","sfl_guid":"GUID_2","skills":["django","python"]}]}}';

function sf_lucene_get_fake_context($app_configuration)
{
  
  return sfContext::createInstance($app_configuration);
}



