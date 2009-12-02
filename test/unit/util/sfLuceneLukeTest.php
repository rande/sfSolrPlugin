<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * @package sfLucenePlugin
  * @subpackage Test
  * @author Thomas Rabaix
  * @version SVN: $Id$
  */

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(11, limeade_output::get());

$lucene = sfLucene::getInstance('index', 'en', $app_configuration);

$luke = new sfLuceneLuke($lucene);

$t->cmp_ok($luke->getRequestHandlerUrl(), '===', 'http://localhost:8983/solr/index_en/admin/luke', '::getRequestUrl() ok');

$luke->loadInformation();

$t->cmp_ok($luke->getNumDocs(), '===', 3, '::getNumDocs() ok');
$t->cmp_ok($luke->getMaxDoc(), '>', 0, '::getMaxDoc() ok');
$t->cmp_ok($luke->getNumTerms(), '===', 38, '::getNumTerms() ok');
$t->cmp_ok(date("U", $luke->getVersion()), '!==', false, '::getVersion() ok');
$t->cmp_ok($luke->getOptimized(), '===', true, '::getOptimized() ok');
$t->cmp_ok($luke->getCurrent(), '===',true, '::getCurrent() ok');
$t->ok(is_bool($luke->getHasDeletions()), '::getHasDeletions() ok');
$t->ok(is_string($luke->getDirectory()), '::getDirectory() ok');
$t->ok(strtotime($luke->getLastModified()), '::getLastModified() ok');
$t->cmp_ok($luke->getStats('prout', 'null'), '===', null, '::getStats() ok');

