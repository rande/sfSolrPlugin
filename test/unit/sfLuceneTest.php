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

require dirname(__FILE__) . '/../bootstrap/unit.php';

$t = new limeade_test(50, limeade_output::get());

$lucene = sfLucene::getInstance('index', 'en', $app_configuration);

$t->cmp_ok($lucene->getPublicName(), '===', 'index (en)', '::getPublicName()');
$t->cmp_ok($lucene->getParameter('name'), '===', 'index', '::getParameter() - name');
$t->cmp_ok($lucene->getParameter('culture'), '===', 'en', '::getParameter() - culture');
$t->cmp_ok($lucene->getParameter('encoding'), '===', 'UTF-8', '::getParameter() - encoding');
$t->cmp_ok($lucene->getParameter('host'), '===', 'localhost', '::getParameter() - host');
$t->cmp_ok($lucene->getParameter('port'), '===', '8983', '::getParameter() - port');
$t->cmp_ok($lucene->getParameter('base_url'), '===', '/solr', '::getParameter() - base_url');


try {
  $e = $t->no_exception('::getInstance() allows valid cultures');
  $lucene = sfLucene::getInstance('index','en', $app_configuration);
  $e->no();
} catch (Exception $ex) {
  $e->caught($ex);
}

$lucene->unlatch();
unset($lucene);

try {
  $e = $t->exception('::getInstance() rejects invalid cultures');
  sfLucene::getInstance('index', 'piglatin', $app_configuration);
  $e->no();
} catch (Exception $ex) {
  $e->caught($ex);
}

try {
  $e = $t->exception('::getInstance() rejects invalid names');
  sfLucene::getInstance('badname', 'en', $app_configuration);
  $e->no();
} catch (Exception $ex) {
  $e->caught($ex);
}

$t->skip('->numDocs() TODO !! indicates index is empty');


$t->diag('testing ::getAllInstances()');

try {
  $e = $t->no_exception('::getAllInstance() executes without exception');
  $instances = sfLucene::getAllInstances($app_configuration);
  $e->no();
} catch (Exception $ex) {
  $instances = array();
  $e->caught($ex);
}

$t->is_deeply($instances, array(
    sfLucene::getInstance('index','en', $app_configuration),
    sfLucene::getInstance('index','fr', $app_configuration),
    sfLucene::getInstance('fooLucene','en', $app_configuration)
  ),
  '::getAllInstances() returns all instances'
);

$t->is_deeply(sfLucene::getAllNames($app_configuration), array('index', 'fooLucene'), '::getAllNames() returns all configured names');

$t->diag('testing ->loadConfig()');

$lucene =  sfLucene::getInstance('index','en', $app_configuration);
$h = $lucene->getParameterHolder();
$t->isa_ok($h, 'sfParameterHolder', '->getParameterHolder() returns a parameter holder');

$t->is($h->get('name'), 'index', 'property "name" is the name of the index');
$t->is($h->get('culture'), 'en', 'property "culture" is the culture of the index');
$t->is($h->get('enabled_cultures'), array('en', 'fr'), 'property "enabled_cultures" contains all enabled cultures');
$t->is($h->get('encoding'), 'UTF-8', 'property "encoding" is the encoding');
$t->is($h->get('mb_string'), true, 'property "mb_string" indicates if to use mb_string functions');

$t->isa_ok($h->get('models'), 'sfParameterHolder', 'property "models" is a sfParameterHolder');
$t->isa_ok($h->get('models')->get('FakeForum'), 'sfParameterHolder', 'properties of "models" are sfParameterHolders');

$m = $h->get('models')->get('FakeForum');

$t->is($m->get('title'), 'title', 'model property "title" is the correct title field');
$t->is($m->get('description'), 'description', 'model property "description" is the correct description field');
$t->is($m->get('categories'), array('Forum'), 'model property "categories" contains the correct categories');
$t->is($m->get('route'), 'forum/showForum?id=%id%', 'model property "route" is the correct route');
$t->is($m->get('validator'), 'isIndexable', 'model property "validator" is the correct validator');
$t->is($m->get('peer'), 'FakeForumPeer', 'model property "peer" is the correct peer');
$t->is($m->get('rebuild_limit'), 5, 'model property "rebuild_limit" is the correct rebuild limit');
$t->is($m->get('partial'), 'forumResult', 'model property "partial" is the correct partial');

$f = $m->get('fields');
$t->isa_ok($f, 'sfParameterHolder', 'model property "fields" is a sfParameterHolder');
$t->is($f->getNames(), array('id','title','description'), 'model property "fields" contains all the fields');
$t->is($f->get('id')->get('type'), 'unindexed', 'field property "type" is the type');
$t->is($f->get('id')->get('boost'), 1, 'field property "boost" is the boost');


$t->diag('testing ->getCategoriesHarness()');
$cats = $lucene->getCategoriesHarness();

$t->isa_ok($cats, 'sfLuceneCategories', '->getCategories() returns an instance of sfLuceneCategories');
$t->ok($lucene->getCategoriesHarness() === $cats, '->getCategories() is a singleton');

$t->diag('testing ->getIndexerFactory()');
$indexer = $lucene->getIndexerFactory();
$t->isa_ok($indexer, 'sfLuceneIndexerFactory', '->getIndexer() returns an instance of sfLuceneIndexerFactory');

$t->diag('testing ->configure()');
$lucene->configure();

$t->diag('testing ->find()');

class MockLucene
{
  public $args;
  public $scoring;
  public $e = false;

  public function search()
  {
    if ($this->e) throw new Exception('Because you said so');

    $this->args = func_get_args();

    return range(1, 100);
  }
}

$mock = new MockLucene;

$originalLucene = $lucene->getLucene();
$lucene->forceLucene($mock);

$t->is($lucene->find('foo'), range(1, 100), '->find() returns what ZSL returns');

$query = sfLuceneCriteria::newInstance($lucene)->add('foo')->addRange('a', 'b', 'c');
$lucene->find($query);
$t->ok($query->getQuery() == $mock->args[0], '->find() accepts sfLuceneCriteria queries');

$lucene->find(
  sfLuceneCriteria::newInstance($lucene)
    ->add('foo')
    ->addAscendingSortBy('sort1')
    ->addDescendingSortBy('sort2', SORT_DESC)
    ->addSortBy('sort3')
);

$t->is_deeply(
  array_splice($mock->args, 1),
  array (
    0, 
    10,
    array ( 
      'fl' => array ( '*,score',    ), 
      'sort' => array ( 'score desc, sort1 asc, sort2 desc, sort3 asc'   ),
    ),
    'GET'
  ), '->find() uses sorting rules from sfLuceneCriteria');


$mock->e = true;

$lucene->forceLucene($originalLucene);

$t->diag('testing ->rebuildIndex()');
class MockIndexerFactory
{
  public $handlers, $deleteLock = false, $search;

  public function __construct($h, $s)
  {
    $this->handlers = $h;
    $this->search = $s;
  }

  public function getHandlers()
  {
    $this->deleteLock = $this->search->getParameter('delete_lock');
    return $this->handlers;
  }
}

class MockIndexerHandler
{
  public $count = 0;

  public function rebuild()
  {
    $this->count++;
  }
}

$handlers = array(new MockIndexerHandler, new MockIndexerHandler);
$factory = new MockIndexerFactory($handlers, $lucene);

$originalFactory = $lucene->getIndexerFactory();
$lucene->forceIndexerFactory($factory);

$lucene->getCategoriesHarness()->getCategory('foo');
$lucene->getCategoriesHarness()->save();

$lucene->rebuildIndex();

$t->is($factory->deleteLock, true, '->rebuildIndex() enables the delete lock');
$t->ok($handlers[0]->count == 1 && $handlers[0]->count == 1, '->rebuildIndex() calls each handler\'s ->rebuild() only once');

$t->is($lucene->getCategoriesHarness()->getAllCategories(), array(), '->rebuildIndex() clears the category list');

$lucene->forceIndexerFactory($originalFactory);

$t->diag('testing wrappers');

if($lucene->getSearchService()->ping())
{
  $t->diag('Solr available');
  
  try {
    $lucene->optimize();
    $t->pass('->optimize() optimizes the index without exception');
  } catch (Exception $e) {
    $t->fail('->optimize() optimizes the index without exception : '.$e->getMessage());
  }
  
  try {
    $t->is($lucene->count(), 3, '->count() returns the document count');
    $t->pass('->count() counts the index without exception');
  } catch (Exception $e) {
    $t->skip('->count() returns the document count');
    $t->fail('->count() counts the index without exception');
  }
  
  try {
    $t->is($lucene->numDocs(), 3, '->numDocs() returns the document count');
    $t->pass('->numDocs() counts the index without exception');
  } catch (Exception $e) {
    $t->skip('->numDocs() returns the document count');
    $t->fail('->numDocs() counts the index without exception');
  }
  
  try {
    $lucene->commit();
    $t->pass('->commit() commits the index without exception');
  } catch (Exception $e) {
    $t->fail('->commit() commits the index without exception');
  }
}
else
{
  $t->diag('Solr not available');
  $t->skip('->optimize() optimizes the index without exception');
  $t->skip('->count() counts the index without exception');
  $t->skip('->numDocs() counts the index without exception');
  $t->skip('->commit() commits the index without exception');
}





$t->diag('testing mixins');

function callListener($event)
{
  if ($event['method'] == 'goodMethod')
  {
    $args = $event['arguments'];

    $event->setReturnValue($args[0] + 1);

    return true;
  }

  return false;
}

$lucene->getEventDispatcher()->connect('lucene.method_not_found', 'callListener');

try {
  $lucene->someBadMethod();
  $t->fail('__call() rejects bad methods');
} catch (Exception $e) {
  $t->pass('__call() rejects bad methods');
}

try {
  $return = $lucene->goodMethod(2);
  $t->pass('__call() accepts good methods');
  $t->is($return, 3, '__call() passes arguments');
} catch (Exception $e) {
  $t->fail('__call() accepts good methods and passes arguments');

  $e->printStackTrace();

  $t->skip('__call() passes arguments');
}