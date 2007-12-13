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

$t = new lime_test(16, new lime_output_color());

$t->diag('testing ::getInstance()');

try {
  $lucene = sfLucene::getInstance('testLucene','en');
  $t->pass('::getInstance() allows valid cultures');
} catch (Exception $e) {
  $t->fail('::getInstance() allows valid cultures');
  $t->skip('the previous test must pass to continue');
  die();
}

try {
  $lucene = sfLucene::getInstance('testLucene', 'piglatin');
  $t->fail('::getInstance() rejects invalid cultures');
  $this->skip('the previous test must pass to continue');
  die();
}
 catch (Exception $e) {
  $t->pass('::getInstance() rejects invalid cultures');
}

try {
  $lucene = sfLucene::getInstance('badname', 'en');
  $t->fail('::getInstance() rejects invalid names');
  $this->skip('the previous test must pass to continue');
  die();
} catch (Exception $e) {
  $t->pass('::getInstance() rejects invalid names');
}

try {
  sfLucene::getInstance('testLucene', 'en', true);
  $t->fail('::getInstance() fails to rebuild index if index is already open');
} catch (Exception $e) {
  $t->pass('::getInstance() fails to rebuild index if index is already open');
}

try {
  $new = sfLucene::getInstance('testLucene', 'fr', true);
  $t->pass('::getInstance() allows to rebuild index if closed');
} catch (Exception $e) {
  $t->fail('::getInstance() allows to rebuild index if closed');
}

if ($new) {
  $t->ok($new->getParameter('is_new'), 'index has new status on new index');
  $t->is($new->numDocs(), 0, '->numDocs() indicates index is empty');
} else {
  $t->skip('index has new status new status on new index');
  $t->skip('->numDocs() indicates index is empty');
}

$t->diag('testing configuration getters');
$t->is_deeply($lucene->getParameter('enabled_cultures'), array('en', 'fr'), '::getEnabledCultures() returns all enabled cultures');

$t->diag('testing ->*Find()');

$t->isa_ok($lucene->getLucene(), 'Zend_Search_Lucene_Proxy', '->getLucene() returns an instance of "Zend_Search_Lucene_Proxy"');

$t->isa_ok($lucene->friendlyFind('test'), 'sfLuceneResults', '->friendlyFind() returns an instance of "sfLuceneResults"');

$t->diag('testing batch mode');

$lucene->setBatchMode();
$t->is($lucene->getLucene()->getMaxBufferedDocs(), 500, '->setBatchMode() sets MaxBufferedDocs to 500');
$t->is($lucene->getLucene()->getMaxMergeDocs(), PHP_INT_MAX, '->setBatchMode() sets MaxMaxMergeDocs to PHP_INT_MAX');
$t->is($lucene->getLucene()->getMergeFactor(), 50, '->setBatchMode() sets MergeFactor to 50');

$t->diag('testing interactive mode');
$lucene->setInteractiveMode();
$t->is($lucene->getLucene()->getMaxBufferedDocs(), 10, '->setInteractiveMode() sets MaxBufferedDocs to 10');
$t->is($lucene->getLucene()->getMaxMergeDocs(), PHP_INT_MAX, '->setInteractiveMode() sets MaxMaxMergeDocs to PHP_INT_MAX');
$t->is($lucene->getLucene()->getMergeFactor(), 10, '->setInteractiveMode() sets MergeFactor to 10');
