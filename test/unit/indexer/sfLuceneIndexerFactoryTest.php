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
// 
// class Foo { }
// class Bar { }
// 
// class sfLuceneActionIndexer { }
// 
$t = new limeade_test(1, limeade_output::get());
$t->todo('Fix these tests');
// 
// $search = sfLucene::getInstance('index', 'fr', $app_configuration);
// $h = $search->getParameterHolder();
// 
// $t->diag('testing construct()');
// 
// try {
//   $factory = new sfLuceneIndexerFactory($search);
//   $t->pass('construct() accepts an instance of sfLucene');
// } catch (Exception $e) {
//   $t->fail('construct() accepts an instance of sfLucene');
// }
// 
// $t->diag('testing ->getHandlers()');
// 
// $handlers = $factory->getHandlers();
// $t->is(array_keys($handlers), array('model','action'), '->getHandlers() returns instances of the model and action handler by default');
// 
// $t->ok($handlers['model'] == new sfLucenePropelIndexerHandler($search), '->getHandlers() returns a valid model handler');
// $t->ok($handlers['action'] == new sfLuceneActionIndexerHandler($search), '->getHandlers() returns a valid action handler');
// 
// $h->get('factories')->set('indexers', array('action' => array('Foo', 'FooIndexer')));
// $handlers = $factory->getHandlers();
// $t->ok($handlers['action'] == new Foo($search), '->getHandlers() can overload built-in handlers');
// 
// $h->get('factories')->set('indexers', array('action' => null));
// $t->is(array_keys($factory->getHandlers()), array('model'), '->getHandlers() can eliminate handlers');
// 
// $h->get('factories')->set('indexers', array('pdf' => array('Foo', 'FooIndexer')));
// $handlers = $factory->getHandlers();
// $t->is(array_keys($handlers), array('model','action','pdf'), '->getHandlers() can add new handlers');
// $t->ok($handlers['pdf'] == new Foo($search), '->getHandlers() can add new handlers correctly');
// 
// $t->diag('testing ->getModel()');
// 
// $model = new FakeForum();
// 
// $t->isa_ok($factory->getModel($model), 'sfLucenePropelIndexer', '->getModel() returns the Propel indexer by default');
// 
// $h->get('models')->get('FakeForum')->set('indexer', 'Foo');
// $t->isa_ok($factory->getModel($model), 'Foo', '->getModel() can overload the indexer on the model level');
// 
// $h->get('models')->get('FakeForum')->remove('indexer');
// $h->get('factories')->set('indexers', array('model' => array('FooHandler', 'Foo')));
// $t->isa_ok($factory->getModel($model), 'Foo', '->getModel() can overload the indexer on the search level');
// 
// $h->get('models')->get('FakeForum')->set('indexer', 'Bar');
// $t->isa_ok($factory->getModel($model), 'Bar', '->getModel() gives higher priority to model level than search level');
// 
// $t->diag('testing ->getAction()');
// $t->isa_ok($factory->getAction('foo','bar'), 'sfLuceneActionIndexer', '->getAction() returns the action indexer by default');
// $h->get('factories')->set('indexers', array('action' => array('FooHandler', 'Foo')));
// $t->isa_ok($factory->getAction('foo','bar'), 'Foo', '->getAction() can overload the indexer');