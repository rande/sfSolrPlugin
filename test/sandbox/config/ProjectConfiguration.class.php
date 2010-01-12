<?php

require_once dirname(__FILE__).'/../../../../../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
     sfConfig::set('sf_plugins_dir', dirname(__FILE__).'/../../../../../plugins' );
     
    // for compatibility / remove and enable only the plugins you want
    $this->enablePlugins(array(
     'sfDoctrinePlugin',
     'sfSolrPlugin',
    ));
  }
}
