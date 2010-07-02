<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
// 
// /**
//   * @package sfLucenePlugin
//   * @subpackage Test
//   * @author Carl Vondrick
//   * @version SVN: $Id$
//   */
// 
require dirname(__FILE__) . '/../../bootstrap/unit.php';
// 
$t = new limeade_test(1, limeade_output::get());
$t->todo('Create test for propel indexer');

// require dirname(__FILE__) . '/../../bootstrap/unit.php';
// 
// $t = new limeade_test(64, limeade_output::get());
// $limeade = new limeade_sf($t);
// $app = $limeade->bootstrap();
// 
// $luceneade = new limeade_lucene($limeade);
// $luceneade->configure()->clear_sandbox()->load_models();
// 
// class Foo { }
// class Bar extends BaseObject { }
// 
// function getDoc($lucene, $guid)
// {
//   $term = new Zend_Search_Lucene_Index_Term($guid, 'sfl_guid');
//   $query = new Zend_Search_Lucene_Search_Query_Term($term);
//   $hits = $lucene->find($query);
// 
//   return $hits[0];
// }
// 
// $lucene = sfLucene::getInstance('testLucene', 'en');
// $model = new FakeForum;
// $model->setPrimaryKey(42);
// $h = $lucene->getParameterHolder()->get('models')->get('FakeForum');
// 
// $t->diag('testing constructor');
// 
// try {
//   new sfLucenePropelIndexer('foo', $model);
//   $t->fail('__construct() rejects invalid search instances');
// } catch (Exception $e) {
//   $t->pass('__construct() rejects invalid search instances');
// }
// 
// try {
//   new sfLucenePropelIndexer($lucene, new Bar());
//   $t->fail('__construct() rejects unregistered models');
// } catch (Exception $e) {
//   $t->pass('__construct() rejects unregistered models');
// }
// 
// try {
//   new sfLucenePropelIndexer($lucene, new Foo());
//   $t->fail('__construct() rejects non-Propel models');
// } catch (Exception $e) {
//   $t->pass('__construct() rejects non-Propel models');
// }
// 
// try {
//   $indexer = new sfLucenePropelIndexer($lucene, $model);
//   $t->pass('__construct() accepts valid search instances and valid models');
// } catch (Exception $e) {
//   $t->fail('__construct() accepts valid search instances and valid models');
// }
// 
// $t->diag('testing ->insert(), fields');
// $h->get('fields')->get('title')->set('type', 'foobar');
// 
// try {
//   $indexer->insert();
//   $t->fail('->insert() fails if a field has an invalid type');
// } catch (Exception $e) {
//   $t->pass('->insert() fails if a field has an invalid type');
// }
// 
// $h->get('fields')->get('title')->set('type', 'keyword');
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->ok($doc->getDocument()->getField('title')->isStored, 'field type "Keyword" is stored');
// $t->ok($doc->getDocument()->getField('title')->isIndexed, 'field type "Keyword" is indexed');
// $t->ok(!$doc->getDocument()->getField('title')->isTokenized, 'field type "Keyword" is not tokenized');
// $t->ok(!$doc->getDocument()->getField('title')->isBinary, 'field type "Keyword" is not binary');
// 
// $h->get('fields')->get('title')->set('type', 'unindexed');
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->ok($doc->getDocument()->getField('title')->isStored, 'field type "Unindexed" is stored');
// $t->ok(!$doc->getDocument()->getField('title')->isIndexed, 'field type "Unindexed" is not indexed');
// $t->ok(!$doc->getDocument()->getField('title')->isTokenized, 'field type "Unindexed" is not tokenized');
// $t->ok(!$doc->getDocument()->getField('title')->isBinary, 'field type "Unindexed" is not binary');
// 
// $h->get('fields')->get('title')->set('type', 'binary');
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->ok($doc->getDocument()->getField('title')->isStored, 'field type "Binary" is stored');
// $t->ok(!$doc->getDocument()->getField('title')->isIndexed, 'field type "Binary" is not indexed');
// $t->ok(!$doc->getDocument()->getField('title')->isTokenized, 'field type "Binary" is not tokenized');
// $t->ok($doc->getDocument()->getField('title')->isBinary, 'field type "Binary" is binary');
// 
// $h->get('fields')->get('title')->set('type', 'unstored');
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->todo('field type "Unstored" is not stored');
// $t->todo('field type "Unstored" is indexed');
// $t->todo('field type "Unstored" is tokenized');
// $t->todo('field type "Unstored" is not binary');
// 
// $h->get('fields')->get('title')->set('type', 'text');
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->ok($doc->getDocument()->getField('title')->isStored, 'field type "Keyword" is stored');
// $t->ok($doc->getDocument()->getField('title')->isIndexed, 'field type "Keyword" is indexed');
// $t->ok($doc->getDocument()->getField('title')->isTokenized, 'field type "Keyword" is tokenized');
// $t->ok(!$doc->getDocument()->getField('title')->isBinary, 'field type "Keyword" is not binary');
// 
// $indexer->delete();
// 
// $t->diag('testing ->insert(), document fields');
// 
// $model->setTitle('foobar!');
// 
// try {
//   $indexer->insert();
//   $lucene->commit();
//   $doc = getDoc($lucene, $indexer->getModelGuid());
//   $t->is($doc->title, 'foobar!', '->insert() handles strings from field getters');
// } catch (Exception $e) {
//   $t->fail('->insert() handles strings from field getters');
// }
// 
// $ph = new sfParameterHolder();
// $ph->add(array('type' => 'text', 'boost' => 1));
// 
// $h->get('fields')->set('nonScalar', $ph);
// 
// try {
//   $indexer->insert();
//   $t->fail('->insert() fails if field getter does not return a scalar');
// } catch (Exception $e) {
//   $t->pass('->insert() fails if field getter does not return a scalar');
// }
// 
// $indexer->delete();
// $h->get('fields')->remove('nonScalar');
// $h->get('fields')->set('stringable_object', $ph);
// 
// try {
//   $indexer->insert();
//   $lucene->commit();
//   $doc = getDoc($lucene, $indexer->getModelGuid());
//   $t->is($doc->stringable_object, 'Strings!', '->insert() converts objects to strings in field getters');
// } catch (Exception $e) {
//   $t->fail('->insert() converts objects to strings in field getters');
// }
// 
// $indexer->delete();
// 
// $model->setTitle('foobar');
// $h->get('fields')->get('title')->set('transform', 'badfunction');
// 
// try {
//   $indexer->insert();
//   $t->fail('->insert() fails if field has invalid transformation function');
// } catch (Exception $e) {
//   $t->pass('->insert() fails if field has invalid transformation function');
// }
// 
// $h->get('fields')->get('title')->set('transform', 'md5');
// 
// try {
//   $indexer->insert();
//   $lucene->commit();
//   $doc = getDoc($lucene, $indexer->getModelGuid());
//   $t->is($doc->title, md5('foobar'), '->insert() executes if field has valid transformation function');
// } catch (Exception $e) {
//   $t->fail('->insert() executes if field has valid transformation function');
// }
// 
// $h->get('fields')->get('title')->set('transform', null);
// 
// 
// $indexer->delete();
// 
// $t->diag('testing ->insert(), model validator');
// $model->setCulture('en');
// $model->setTitle('title');
// $model->setDescription('description');
// $model->shouldIndex = false;
// 
// $indexer->insert();
// $lucene->commit();
// 
// $t->is($lucene->numDocs(), 0, '->insert() does not insert document if model validator returned false');
// 
// $model->shouldIndex = true;
// 
// $indexer->insert();
// $lucene->commit();
// 
// $t->is($lucene->numDocs(), 1, '->insert() inserts document if model validator returned true');
// 
// $model->shouldIndex = false;
// $h->remove('validator');
// 
// $indexer->insert();
// $lucene->commit();
// 
// $t->is($lucene->numDocs(), 2, '->insert() inserts document if model does not have a validator');
// 
// $indexer->delete();
// $lucene->commit();
// 
// $t->diag('testing ->insert(), base document');
// $h->set('callback', 'badMethod');
// 
// try {
//   $indexer->insert();
//   $t->fail('->insert() fails if document callback does not exist');
// } catch (Exception $e) {
//   $t->pass('->insert() fails if document callback does not exist');
// }
// 
// $h->set('callback', 'getNonScalar');
// 
// try {
//   $indexer->insert();
//   $t->fail('->insert() fails if document callback does not return a Zend_Search_Lucene_Document');
// } catch (Exception $e) {
//   $t->pass('->insert() fails if document callback does not return a Zend_Search_Lucene_Document');
// }
// 
// $indexer->delete();
// 
// $h->set('callback', 'getZendDocument');
// 
// try {
//   $indexer->insert();
//   $lucene->commit();
//   $t->is($lucene->numDocs(), 1, '->insert() executes if document callback returns a Zend_Search_Lucene_Document');
// 
//   $doc = getDoc($lucene, $indexer->getModelGuid());
// 
//   try {
//     $t->is($doc->callback, 'foo', '->insert() uses document from document callback');
//   } catch (Exception $e) {
//     $t->fail('->insert() uses document from document callback');
//   }
// 
// } catch (Exception $e) {
//   $t->fail('->insert() executes if document callback returns a Zend_Search_Lucene_Document');
//   $t->skip('->insert() uses document from document callback');
// }
// 
// $indexer->delete();
// 
// $h->set('callback', null);
// 
// $t->diag('testing ->insert(), categories');
// 
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->is($doc->sfl_category, 'Forum', '->insert() configures categories correctly');
// $t->is($doc->sfl_categories_cache, serialize(explode(' ', $doc->sfl_category)), '->insert() configures categories cache correctly');
// 
// $t->is($lucene->getCategoriesHarness()->getCategory('Forum')->getCount(), 1, '->insert() updated category database count');
// 
// $app->i18n()->setup('en');
// 
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->is($doc->sfl_category, 'Forum', '->insert() configures categories correctly with i18n on');
// $t->is($doc->sfl_categories_cache, serialize(explode(' ', $doc->sfl_category)), '->insert() configures categories cache correctly with i18n on');
// 
// $t->is($lucene->getCategoriesHarness()->getCategory('Forum')->getCount(), 1, '->insert() updated category database count with i18n on');
// 
// $app->i18n()->teardown();
// 
// $h->set('categories', 'Forum');
// 
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->is($doc->sfl_category, 'Forum', '->insert() configures categories correctly if just a string');
// $t->is($doc->sfl_categories_cache, serialize(explode(' ', $doc->sfl_category)), '->insert() configures categories cache correctly if just a string');
// 
// $t->is($lucene->getCategoriesHarness()->getCategory('Forum')->getCount(), 1, '->insert() updated category database count if just a string');
// 
// $h->set('categories', array('Forum', '%title%'));
// 
// $model->setTitle('foobar');
// 
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->is($doc->sfl_category, 'Forum foobar', '->insert() configures categories correctly with a callback');
// $t->is($doc->sfl_categories_cache, serialize(array('Forum','foobar')), '->insert() configures categories cache correctly with a callback');
// 
// $t->is($lucene->getCategoriesHarness()->getCategory('Forum')->getCount(), 1, '->insert() updated category database count for first category');
// $t->is($lucene->getCategoriesHarness()->getCategory('foobar')->getCount(), 1, '->insert() updated category database count for second category');
// 
// $indexer->delete();
// 
// $h->set('categories', array('Forum', '%bad%'));
// 
// try {
//   $indexer->insert();
//   $t->fail('->insert() fails if category callback does not exist');
// } catch (Exception $e) {
//   $t->pass('->insert() fails if category callback does not exist');
// }
// 
// $h->set('categories', array('Forum', '%nonScalar%'));
// 
// try {
//   $indexer->insert();
//   $t->fail('->insert() fails if category callback returns a non-scalar');
// } catch (Exception $e) {
//   $t->pass('->insert() fails if category callback returns a non-scalar');
// }
// 
// $h->set('categories', array('Forum', '%stringable_object%'));
// 
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->is($doc->sfl_category, 'Forum Strings!', '->insert() configures categories correctly with a object-returning callback');
// $t->is($doc->sfl_categories_cache, serialize(array('Forum','Strings!')), '->insert() configures categories cache correctly with a object-returning callback');
// 
// $t->is($lucene->getCategoriesHarness()->getCategory('Forum')->getCount(), 1, '->insert() updated category database count for first category with object-returning callback');
// $t->is($lucene->getCategoriesHarness()->getCategory('Strings!')->getCount(), 1, '->insert() updated category database count for second category with object-returning callback');
// 
// $h->remove('categories');
// 
// $indexer->delete();
// $indexer->insert();
// $lucene->commit();
// $doc = getDoc($lucene, $indexer->getModelGuid());
// 
// $t->ok(!in_array('sfl_category', $doc->getDocument()->getFieldNames()), '->insert() does not create a category key without categories');
// $t->ok(!in_array('sfl_category', $doc->getDocument()->getFieldNames()), '->insert() does not create a category cache key without categories');
// 
// $t->diag('testing ->delete()');
// 
// $indexer->delete();
// 
// for ($i = 0; $i < 5; $i++) $indexer->insert();
// 
// $lucene->setParameter('delete_lock', true);
// $indexer->delete();
// $lucene->commit();
// 
// $t->is($lucene->numDocs(), 5, '->delete() does nothing if parameter "delete_lock" is set');
// 
// $lucene->setParameter('delete_lock', false);
// 
// $indexer->delete();
// $lucene->commit();
// 
// $t->is($lucene->numDocs(), 0, '->delete() deletes all matching documents in the index');
// 
// $lucene->getCategoriesHarness()->getCategory('Cat1')->add(5);
// 
// $h->set('categories', array('Cat1'));
// $indexer->insert();
// $lucene->commit();
// $count = $lucene->getCategoriesHarness()->getCategory('Cat1')->getCount();
// $indexer->delete();
// 
// $t->is($lucene->getCategoriesHarness()->getCategory('Cat1')->getCount(), $count - 1, '->delete() updates the category database count');
// 
// $indexer->insert();
// $count = $lucene->getCategoriesHarness()->getCategory('Cat1')->getCount();
// $h->set('categories', array('Cat2'));
// $indexer->delete();
// 
// $t->is($lucene->getCategoriesHarness()->getCategory('Cat1')->getCount(), $count - 1, '->delete() updates category count that it was indexed with if categories have changed');
// $t->is($lucene->getCategoriesHarness()->getCategory('Cat2')->getCount(), 0, '->delete() does not update new category count if categories have changed');
// 
// $t->diag('testing ->save()');
// 
// for ($i = 0; $i < 5; $i++) $indexer->insert();
// 
// $indexer->save();
// $lucene->commit();
// 
// $t->is($lucene->numDocs(), 1, '->save() deletes all old instances and inserts only one new one');