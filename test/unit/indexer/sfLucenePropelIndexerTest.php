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
require dirname(__FILE__) . '/../../bin/AllFakeModels.php';

class Foo { }
class Bar extends BaseObject { }

$t = new lime_test(14, new lime_output_color());

$lucene = sfLucene::getInstance('testLucene', 'en');
$indexer = $lucene->getIndexer();

$model = new FakeForum;

$t->diag('testing factory through indirect manipulation');
$t->isa_ok($indexer, 'sfLuceneIndexerFactory', '->getIndexer() returns the factory');

$indexer = $indexer->getModel($model);
$t->isa_ok($indexer, 'sfLucenePropelIndexer', '->getIndexer()->getModel() returns the propel indexer');

$t->diag('testing direct manipulation');

try {
  $indexer = new sfLucenePropelIndexer('a', $model);
  $t->fail('__construct() rejects an invalid search instance');
} catch (Exception $e) {
  $t->pass('__construct() rejects an invalid search instance');
}

try {
  $indexer = new sfLucenePropelIndexer($lucene, new Foo);
  $t->fail('__construct() rejects an invalid model instance');
} catch (Exception $e) {
  $t->pass('__construct() rejects an invalid model instance');
}

$t->diag('testing normal conditions');

$numDocs = $lucene->numDocs();

$model->setTitle('Test');
$model->setDescription('this is cool');
$model->setId(99);

try {
  $indexer->insert();
  $t->pass('->insert() inserts a valid model without exception');
} catch (Exception $e) {
  $t->fail('->insert() inserts a valid model without exception');
  echo $e->printStackTrace();
}

$lucene->commit();

$t->is($lucene->numDocs(), $numDocs + 1, '->numDocs() returns a document count increased by one');

try {
  $indexer->delete();
  $t->pass('->delete() deletes a valid model without exception');
} catch (Exception $e) {
  $t->fail('->delete() deletes a valid model without exception');
}

$lucene->commit();

$t->is($lucene->numDocs(), $numDocs, '->numDocs() returns a document count that is back to original value');

$t->diag('testing i18n');

configure_i18n();

try {
  $indexer->insert();
  $t->pass('->insert() inserts a valid model without exception with i18n on');
} catch (Exception $e) {
  $t->fail('->insert() inserts a valid model without exception with i18n on');
}

try {
  $indexer->delete();
  $t->pass('->delete() deletes a valid model without exception with i18n on');
} catch (Exception $e) {
  $t->fail('->delete() deletes a valid model without exception with i18n on');
}

configure_i18n(false);

$t->diag('testing bad inputs');

try {
  $lucene->getIndexer()->getModel(new Foo())->insert();
  $t->fail('->insert() rejects an invalid model');
} catch (Exception $e) {
  $t->pass('->insert() rejects an invalid model');
}

try {
  $lucene->getIndexer()->getModel(new Foo())->delete();
  $t->fail('->delete() rejects an invalid model');
} catch (Exception $e) {
  $t->pass('->delete() rejects an invalid model');
}

try {
  $lucene->getIndexer()->getModel(new Bar())->insert();
  $t->fail('->insert() rejects unregistered model');
} catch (Exception $e) {
  $t->pass('->insert() rejects unregistered model');
}

try {
  $lucene->getIndexer()->getModel(new Foo())->delete();
  $t->fail('->delete() rejects unregistered model');
} catch (Exception $e) {
  $t->pass('->delete() rejects unregistered model');
}