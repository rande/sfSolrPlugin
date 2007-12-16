<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * @package sfLucenePlugin
  * @subpackage Configuration
  * @author Carl Vondrick
  * @version SVN: $Id$
  */

// WARNING: Currently broken in symfony trunk; see ticket #2408

// setup default routes
if (sfConfig::get('app_lucene_routes', true) && in_array('sfLucene', sfConfig::get('sf_enabled_modules', array())))
{
  $r = sfContext::getInstance()->getRouting();

  $r->prependRoute('sf_lucene_search', '/search', array('module' => 'sfLucene', 'action' => 'search'));
  $r->prependRoute('sf_lucene_search_results', '/search/results/:query/:page', array('module' => 'sfLucene', 'action' => 'search', 'page' => 1), array('page' => '\d+'));

  if (sfConfig::get('app_lucene_advanced', true))
  {
    $r->prependRoute('sf_lucene_search_advanced', '/search/advanced', array('module' => 'sfLucene', 'action' => 'advanced'));
  }

  if (sfConfig::get('app_lucene_categories', true))
  {
    $r->prependRoute('sf_lucene_search_results_categories', '/search/:category/results/:query/:page', array('module' => 'sfLucene', 'action' => 'search', 'page' => 1), array('page' => '\d+'));
  }
}