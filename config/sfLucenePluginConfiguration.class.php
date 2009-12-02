<?php


class sfLucenePluginConfiguration extends sfPluginConfiguration
{

  public function setup()
  {

    if ($this->configuration instanceof sfApplicationConfiguration)
    {
      /**
       * Add configuration handlers for search.yml
       */
      $configCache = $this->configuration->getConfigCache();
      $configCache->registerConfigHandler('config/search.yml', 'sfLuceneProjectConfigHandler');
      $configCache->registerConfigHandler('modules/*/config/search.yml', 'sfLuceneModuleConfigHandler');
    }
    
  }
}
