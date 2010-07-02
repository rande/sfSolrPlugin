<?php

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

include_once dirname(__FILE__).'/../lib/sfLuceneTestPatternRouting.class.php';
class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->setPlugins(array('sfSolrPlugin'));
    $this->setPluginPath('sfSolrPlugin', dirname(__FILE__).'/../../../..');
  }
}
