<?php

class frontendConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    $configCache = $this->getConfigCache();
    $configCache->registerConfigHandler('config/search.yml', 'sfLuceneProjectConfigHandler');
    $configCache->registerConfigHandler('modules/*/config/search.yml', 'sfLuceneModuleConfigHandler');
  }
}
