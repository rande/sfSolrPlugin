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
  * @version SVN: $Id$
  */

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(14, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

$luceneade = new limeade_lucene($limeade);
$luceneade->configure()->clear_sandbox();

$t->diag('testing ::loadZend()');

sfConfig::set('app_lucene_zend_location', '/tmp');

try {
  $e = $t->exception('::loadZend() fails with non-existant Zend path');
  sfLuceneToolkit::loadZend();
  $e->no();
} catch (Exception $ex) {
  $e->caught($ex);
}

$limeade->config()->remove('app_lucene_zend_location');

$t->not_like_included('#/Zend/Search/Lucene/#', 'Zend Search Lucene is not loaded after failed run');
$t->not_in_include_path('Zend/Search/Lucene.php', 'Zend Search Lucene is not in the include path after failed run');

sfLuceneToolkit::loadZend();

$t->like_included('#/Zend/Search/Lucene/#','::loadZend() loads Zend Search Lucene');
$t->in_include_path('Zend/Search/Lucene.php', '::loadZend() configures include path');

$t->diag('testing ::getDirtyIndexRemains()');

$luceneade->clear_sandbox();

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

$limeade->config()->remove('app_lucene_index');
$cswap = $app->cswap($luceneade->config_dir . '/search.yml')->write('<?php $config = array();');

try {
  $e = $t->exception('::getApplicationInstance() fails if search.yml is empty');
  sfLuceneToolkit::getApplicationInstance();
  $e->no();
} catch (Exception $ex) {
  $e->caught($ex);
}

$cswap->restore();
