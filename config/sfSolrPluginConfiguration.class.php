<?php

class sfSolrPluginConfiguration extends sfPluginConfiguration
{

  public function setup()
  {
    if ($this->configuration instanceof sfApplicationConfiguration)
    {
      /**
       * Add configuration handlers for search.yml
       */
      
      $this->loadConfigHandlerClasses();
      
      $configCache = $this->configuration->getConfigCache();
      $configCache->registerConfigHandler('config/search.yml', 'sfLuceneProjectConfigHandler');
      $configCache->registerConfigHandler('modules/*/config/search.yml', 'sfLuceneModuleConfigHandler');
    }
    
  }
  
  /**
   * 
   * At this point the autoloaded is not yet started
   * 
   */
  public function loadConfigHandlerClasses()
  {
    
    $lib_folder = sfConfig::get('sf_plugins_dir').'/sfSolrPlugin/lib/config';
    
    if(!class_exists('sfLuceneProjectConfigHandler', true))
    {
      include_once $lib_folder.'/sfLuceneProjectConfigHandler.class.php';
    }
    
    if(!class_exists('sfLuceneModuleConfigHandler', true))
    {
      include_once $lib_folder.'/sfLuceneModuleConfigHandler.class.php';
    }
  }
}
