<?php

/**
 * @package sfLucenePlugin
 */

/**
 * Add configuration handlers for search.yml
 */
$configCache = sfProjectConfiguration::getActive()->getConfigCache();
$configCache->registerConfigHandler('config/search.yml', 'sfLuceneProjectConfigHandler');
$configCache->registerConfigHandler('modules/*/config/search.yml', 'sfLuceneModuleConfigHandler');
