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

require dirname(__FILE__) . '/../bootstrap/unit.php';

$t = new lime_test(1, new lime_output_color());

$lucene = sfLucene::getInstance('testLucene','en');
$stat = stat(sfConfig::get('sf_data_dir') . '/index/testLucene/en/segments.gen');

$lucene->unlatch();
unset($lucene);

$t->comment('sleeping for 1 second...');

sleep(1); // delay so filemtime can change

try {
  $lucene = sfLucene::getInstance('testLucene', 'en', true);
  clearstatcache();
  $t->isnt(stat(sfConfig::get('sf_data_dir') . '/index/testLucene/en/segments.gen'), $stat, '::getInstance() can rebuild an existing index');
} catch (Exception $e) {
  $t->fail('::getInstance() can rebuild an existing index');
}