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

require dirname(__FILE__) . '/../../bootstrap/unit.php';

function filter_callback($input)
{
  return preg_match('#/Zend/Search/Lucene/#', $input);
}
function zend_loaded()
{
  $files = get_included_files();
  $files = array_filter($files, 'filter_callback');

  return count($files) != 0;
}
function zend_in_include_path()
{
  $paths = explode(PATH_SEPARATOR, get_include_path());

  foreach ($paths as $path)
  {
    if (file_exists($path . '/Zend/Search/Lucene.php'))
    {
      return true;
    }
  }

  return false;
}

$t = new lime_test(14, new lime_output_color());

$t->diag('testing ::loadZend()');

sfConfig::set('app_lucene_zend_location', '/tmp');

try {
  sfLuceneToolkit::loadZend();
  $t->fail('::loadZend() fails with non-existant Zend path');
} catch (Exception $e) {
  $t->pass('::loadZend() fails with non-existant Zend path');
}

remove_from_sfconfig('app_lucene_zend_location');

$t->ok(!zend_loaded(), 'Zend Search Lucene is not loaded after failed run');
$t->ok(!zend_in_include_path(), 'Zend Search Lucene is not in the include path after failed run');

sfLuceneToolkit::loadZend();

$t->ok(zend_loaded(), '::loadZend() loads Zend Search Lucene');
$t->ok(zend_in_include_path(), '::loadZend() configures include path');

$t->diag('testing ::getDirtyIndexRemains()');

clear_sandbox();
$root = sfConfig::get('sf_data_dir') . '/index/';

// build valid indexes structure
sfLucene::getInstance('testLucene','en')->getLucene();
sfLucene::getInstance('testLucene','fr')->getLucene();

// build invalid indexes structures
file_put_contents($root.'testLucene/en/random_file', 'r@nd()');
mkdir($root.'testLucene/foo', 0777, true);
file_put_contents($root.'testLucene/foo/bar', 'foo');
mkdir($root.'badIndex/en', 0777, true);
file_put_contents($root.'badIndex/bar', 'foo');

$dirty = sfLuceneToolkit::getDirtyIndexRemains();

$t->ok(in_array($root.'testLucene/foo', $dirty), '::getDirtyIndexRemains() schedules valid indexes but invalid cultures for deletion');
$t->ok(in_array($root.'badIndex', $dirty), '::getDirtyIndexRemains() schedules the entire of a bad index for deletion');
$t->ok(!in_array($root.'testLucene', $dirty), '::getDirtyIndexRemains() did not schedule an entire valid index for deletion');
$t->ok(!in_array($root.'testLucene/en', $dirty), '::getDirtyIndexRemains() did not schedule a valid index and valid culture for deletion');
$t->ok(!in_array($root.'testLucene/fr', $dirty), '::getDirtyIndexRemains() did not schedule another valid index and valid culture for deletion');
$t->ok(!in_array($root.'testLucene/en/random_file', $dirty), '::getDirtyIndexRemains() did not schedule an alien file in a valid index and valid culture for deletion');

$t->diag('testing ::getApplicationInstance');

$t->ok(sfLuceneToolkit::getApplicationInstance('en') === sfLucene::getInstance('testLucene', 'en'), '::getApplicationInstance() guesses the first index with no configuration parameter set');

sfConfig::set('app_lucene_index', 'fooLucene');

$t->ok(sfLuceneToolkit::getApplicationInstance('en') === sfLucene::getInstance('fooLucene', 'en'), '::getApplicationInstance() acknowledges manual override from app.yml');

remove_from_sfconfig('app_lucene_index');

$cache = sfConfigCache::getInstance()->getCacheName(sfConfig::get('sf_config_dir_name').DIRECTORY_SEPARATOR.'search.yml');

rename($cache, $cache . '~real');

file_put_contents($cache, '<?php $config = array();');

try {
  sfLuceneToolkit::getApplicationInstance();
  $t->fail('::getApplicationInstance() fails if search.yml is empty');
} catch (Exception $e) {
  $t->pass('::getApplicationInstance() fails if search.yml is empty');
}

unlink($cache);

rename($cache . '~real', $cache);
