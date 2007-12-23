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
  * @subpackage Test
  * @author Carl Vondrick
  * @version SVN: $Id$
  */

$app = isset($app) ? $app : 'frontend';

define('SF_ROOT_DIR', dirname(__FILE__) . '/../../../..');
define('SF_APP', $app);
define('SF_ENVIRONMENT', 'dev');
define('SF_DEBUG', true);

require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

sfContext::getInstance();

error_reporting(E_ALL);

include(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
require_once($sf_symfony_lib_dir.'/vendor/lime/lime.php');

define('SANDBOX_DIR', dirname(dirname(__FILE__)) . '/sandbox');
define('DATA_DIR', dirname(dirname(__FILE__)) . '/data');

function clear_sandbox()
{
  // clear sandbox
  sfToolkit::clearDirectory(SANDBOX_DIR);
}

function configure_i18n($status = true, $culture = 'en_US')
{
  if ($status)
  {
    sfConfig::add(array(
      'sf_i18n_default_culture' => 'en_US',
      'sf_i18n_source' => 'XLIFF',
      'sf_i18n_debug' => false,
      'sf_i18n_untranslated_prefix' => '[T]',
      'sf_i18n_untranslated_suffix' => '[/T]',
    ));

    sfConfig::set('sf_i18n', true);
    sfContext::getInstance()->set('i18n', new sfI18N(sfContext::getInstance()));
  }
  else
  {
    sfConfig::set('sf_i18n', false);
    sfContext::getInstance()->set('i18n', null);
  }
}

function remove_from_sfconfig($what)
{
  $all = sfConfig::getAll();
  unset($all[$what]);
  sfConfig::clear();
  sfConfig::add($all);
}

clear_sandbox();

sfConfig::set('sf_config_dir_name', dirname(__FILE__) . '/../data/config');
sfConfig::set('sf_data_dir', SANDBOX_DIR . '/data');