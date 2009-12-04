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
  
// NOTE : For now Propel implementation is not tested / supported  
  
// 
require dirname(__FILE__) . '/../../bootstrap/unit.php';
 
$t = new limeade_test(0, limeade_output::get());

//$t = new limeade_test(30, limeade_output::get());
// $limeade = new limeade_sf($t);
// $app = $limeade->bootstrap();
// 
// $luceneade = new limeade_lucene($limeade);
// $luceneade->configure()->clear_sandbox()->load_models();
// 
// $m1 = new FakeForum;
// $m1->setCoolness(5);
// 
// $m2 = new FakeForum;
// $m2->setCoolness(4);
// 
// $m3 = new FakeForum;
// $m3->setCoolness(3);
// $m3->save();
// $m3->deleteIndex();
// 
// $m4 = new FakeForum;
// $m4->setCoolness(2);
// 
// class Foo {}
// 
// class MockBehavior extends sfLucenePropelBehavior
// {
//   public function _getSaveQueue()
//   {
//     return $this->saveQueue;
//   }
// 
//   public function _getDeleteQueue()
//   {
//     return $this->deleteQueue;
//   }
// 
//   public function _getSearchInstances($node)
//   {
//     return $this->getSearchInstances($node);
//   }
// 
//   public function clear()
//   {
//     $this->saveQueue = array();
//     $this->deleteQueue = array();
//   }
// }
// 
// $t->diag('testing ::getInitializer()');
// $t->isa_ok(sfLucenePropelBehavior::getInitializer(), 'sfLucenePropelInitializer', '::getInitializer() returns an instance of sfLucenePropelInitializer');
// 
// $t->diag('testing ->getSearchInstances()');
// 
// $behavior = new MockBehavior;
// 
// try {
//   $behavior->_getSearchInstances(new Foo);
//   $t->fail('->getSearchInstances() fails if node cannot be found');
// } catch (Exception $e) {
//   $t->pass('->getSearchInstances() fails if node cannot be found');
// }
// 
// $instances = $behavior->_getSearchInstances($m1);
// 
// $t->ok($instances === array(sfLucene::getInstance('testLucene', 'en'), sfLucene::getInstance('testLucene', 'fr')), '->getSearchInstances() returns all search instances for a Propel model');
// 
// $t->is($behavior->_getSearchInstances($m2), $instances, '->getSearchInstances() returns same instances for the same model');
// 
// $t->diag('testing ->preSave()');
// 
// $behavior->preSave($m1);
// $q = $behavior->_getSaveQueue();
// 
// $t->is($q[0], $m1, '->preSave() adds model to queue if it does not already exist');
// $t->is(count($q), 1, '->preSave() adds the model to queue only once');
// 
// $behavior->preSave($m1);
// $t->is(count($behavior->_getSaveQueue()), 1, '->preSave() does not add model again if it already exists');
// 
// $behavior->preSave($m2);
// $q = $behavior->_getSaveQueue();
// $t->is($q[0], $m1, '->preSave() keeps unresolved models in queue');
// $t->is($q[1], $m2, '->preSave() adds new models alongside old models');
// 
// $behavior->preSave($m3);
// $q = $behavior->_getSaveQueue();
// $t->is(count($q), 2, '->preSave() does not add unmodified objects to the queue');
// 
// $t->diag('testing ->preDelete()');
// 
// $m1->save();
// $m1->deleteIndex();
// $m2->save();
// $m2->deleteIndex();
// 
// $behavior->preDelete($m1);
// $q = $behavior->_getDeleteQueue();
// 
// $t->is($q[0], $m1, '->preDelete() adds model to queue if it does not already exist');
// $t->is(count($q), 1, '->predDlete() adds the model to queue only once');
// 
// $behavior->preDelete($m1);
// $t->is(count($behavior->_getDeleteQueue()), 1, '->preDelete() does not add model again if it already exists');
// 
// $behavior->preDelete($m2);
// $q = $behavior->_getDeleteQueue();
// $t->is($q[0], $m1, '->preDelete() keeps unresolved models in queue');
// $t->is($q[1], $m2, '->preDelete() adds new models alongside old models');
// 
// $behavior->preDelete($m4);
// $q = $behavior->_getDeleteQueue();
// $t->is(count($q), '2', '->preDelete() does not add new objects to the queue');
// 
// foreach (array($m1, $m2, $m3, $m3) as $m)
// {
//   $indexer = new sfLucenePropelIndexer(sfLucene::getInstance('testLucene', 'en'), $m);
//   $indexer->delete();
// }
// 
// $t->diag('testing ->postSave()');
// 
// $search = sfLucene::getInstance('testLucene', 'en');
// 
// $behavior->postSave($m3);
// $search->commit();
// $t->is($search->numDocs(), 0, '->postSave() does not save if it is not in the queue');
// 
// $behavior->postSave($m1);
// $search->commit();
// $t->is($search->numDocs(), 1, '->postSave() saves the model to the index if it exists in the queue');
// $t->is($behavior->_getSaveQueue(), array(1 => $m2), '->postSave() removes saving model from the queue');
// 
// 
// $t->diag('testing ->postDelete()');
// 
// $behavior->postDelete($m4);
// $search->commit();
// $t->is($search->numDocs(), 1, '->postDelete() does not delete if it is not in the queue');
// 
// $behavior->postDelete($m1);
// $search->commit();
// $t->is($search->numDocs(), 0, '->postDelete() deletes the model from the index if it exists in the queue');
// $t->is($behavior->_getDeleteQueue(), array(1 => $m2), '->postDelete() removes deleting model from the queue');
// 
// $t->diag('testing ::setLock()');
// 
// $behavior->clear();
// 
// sfLucenePropelBehavior::setLock(true);
// 
// $m1->setCoolness(4);
// 
// $behavior->preSave($m1);
// $t->is(count($behavior->_getSaveQueue()), 0, '::setLock() disables the save queue');
// 
// $behavior->preDelete($m1);
// $t->is(count($behavior->_getDeleteQueue()), 0, '::setLock() disables the delete queue');
// 
// $behavior->clear();
// 
// sfLucenePropelBehavior::setLock(false);
// 
// $behavior->preSave($m1);
// $t->is(count($behavior->_getSaveQueue()), 1, '::setLock() enables the save queue');
// 
// $behavior->preDelete($m1);
// $t->is(count($behavior->_getDeleteQueue()), 1, '::setLock() enables the delete queue');
// 
// $behavior->clear();
// 
// foreach (array($m1, $m2, $m3, $m3) as $m)
// {
//   $indexer = new sfLucenePropelIndexer(sfLucene::getInstance('testLucene', 'en'), $m);
//   $indexer->delete();
// }
// 
// $t->diag('testing ->insertIndex()');
// 
// $en = sfLucene::getInstance('testLucene', 'en');
// $fr = sfLucene::getInstance('testLucene', 'fr');
// 
// foreach (array('en','fr') as $cult)
// {
//   $indexer = new sfLucenePropelIndexer($$cult, $m1);
//   $indexer->delete();
// }
// 
// $behavior->insertIndex($m1);
// $en->commit();
// $fr->commit();
// 
// $t->is($en->numDocs(), 1, '->insertIndex() added model to first index it appears in');
// $t->is($fr->numDocs(), 1, '->insertIndex() added model to second index it appears in');
// 
// $t->diag('testing ->deleteIndex();');
// 
// $behavior->deleteIndex($m1);
// $en->commit();
// $fr->commit();
// 
// $t->is($en->numDocs(), 0, '->deleteIndex() deleted model from first index it appears in');
// $t->is($fr->numDocs(), 0, '->deleteIndex() deleted model from second index it appears in');
// 
// foreach (array('en','fr') as $cult)
// {
//   $indexer = new sfLucenePropelIndexer($$cult, $m1);
//   $indexer->delete();
// }
// 
// $m1->delete();
// $m2->delete();
// $m3->delete();