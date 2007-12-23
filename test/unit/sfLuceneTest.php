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

$t = new lime_test(91, new lime_output_color());

$t->diag('testing ::getInstance()');

$t->ok(!is_dir(sfConfig::get('sf_data_dir') . '/index/testLucene/en'), 'Lucene directory does not initially exist');

try {
  $lucene = sfLucene::getInstance('testLucene','en');
  $t->pass('::getInstance() allows valid cultures');
  $t->ok(is_dir(sfConfig::get('sf_data_dir') . '/index/testLucene/en'), '::getInstance() creates the index');

  $stat = stat(sfConfig::get('sf_data_dir') . '/index/testLucene/en/segments.gen');
} catch (Exception $e) {
  $t->fail('::getInstance() allows valid cultures');
  $t->skip('::getInstance() creates the index');
}

$lucene->unlatch();
unset($lucene);

try {
  $lucene = sfLucene::getInstance('testLucene', 'en');
  clearstatcache();
  $t->is_deeply(stat(sfConfig::get('sf_data_dir') . '/index/testLucene/en/segments.gen'), $stat, '::getInstance() again opens the index');
} catch (Exception $e) {
  $t->skip('::getInstance() agains opens the index');
}

try {
  sfLucene::getInstance('testLucene', 'piglatin');
  $t->fail('::getInstance() rejects invalid cultures');
} catch (Exception $e) {
  $t->pass('::getInstance() rejects invalid cultures');
}

try {
  sfLucene::getInstance('badname', 'en');
  $t->fail('::getInstance() rejects invalid names');
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

try {
  sfContext::getInstance()->getUser()->setCulture('en');
  $t->is(sfLucene::getInstance('testLucene')->getParameter('culture'), 'en', '::getInstance() can guess the culture');
} catch (Exception $e) {
  $t->fail('::getInstance() can guess the culture');
}

if ($new) {
  $t->ok($new->getParameter('is_new'), 'property "is_new" is true on a new index');
  $t->is($new->numDocs(), 0, '->numDocs() indicates index is empty');
} else {
  $t->skip('index has new status new status on new index');
  $t->skip('->numDocs() indicates index is empty');
}

$t->diag('testing ::getAllInstances()');

try {
  $instances = sfLucene::getAllInstances();
  $t->pass('::getAllInstance() executes without exception');
} catch (Exception $e) {
  $instances = array();
  $t->fail('::getAllInstances() executes without exception');
}

$t->is_deeply($instances, array(sfLucene::getInstance('testLucene','en'), sfLucene::getInstance('testLucene','fr'), sfLucene::getInstance('fooLucene','en')), '::getAllInstances() returns all instances');

$t->is_deeply(sfLucene::getAllNames(), array('testLucene', 'fooLucene'), '::getAllNames() returns all configured names');

$t->diag('testing ->loadConfig()');

$h = $lucene->getParameterHolder();
$t->isa_ok($h, 'sfParameterHolder', '->getParameterHolder() returns a parameter holder');

$t->is($h->get('name'), 'testLucene', 'property "name" is the name of the index');
$t->is($h->get('culture'), 'en', 'property "culture" is the culture of the index');
$t->is($h->get('enabled_cultures'), array('en', 'fr'), 'property "enabled_cultures" contains all enabled cultures');
$t->like($h->get('index_location'), '#/index/testLucene/en$#', 'property "index_location" is the correct path');
$t->is($h->get('encoding'), 'utf-8', 'property "encoding" is the encoding');
$t->is($h->get('stop_words'), array('and', 'the'), 'property "stop_words" contains the stop words');
$t->is($h->get('short_words'), 2, 'property "short_words" is the short word limit');
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
$t->is($m->get('partial'), 'modelResult', 'model property "partial" is the correct partial');

$f = $m->get('fields');
$t->isa_ok($f, 'sfParameterHolder', 'model property "fields" is a sfParameterHolder');
$t->is($f->getNames(), array('id','title','description'), 'model property "fields" contains all the fields');
$t->is($f->get('id')->get('type'), 'unindexed', 'field property "type" is the type');
$t->is($f->get('id')->get('boost'), 1, 'field property "boost" is the boost');

$t->diag('testing ::getConfig()');

$cache = sfConfigCache::getInstance()->getCacheName(sfConfig::get('sf_config_dir_name').DIRECTORY_SEPARATOR.'search.yml');
rename($cache, $cache . '~real');
file_put_contents($cache, '<?php $foo = 42;');

try {
  sfLucene::getConfig();
  $t->fail('::getConfig() fails if search.yml is corrupt');
} catch (Exception $e) {
  $t->pass('::getConfig() fails if search.yml is corrupt');
}

file_put_contents($cache, '<?php $config = array(1, 2, 3);');

try {
  $t->is(sfLucene::getConfig(), array(1, 2, 3), '::getConfig() returns the $config variable in the search.yml file');
} catch (Exception $e) {
  $t->fail('::getConfig() returns the $config variable in the search.yml file');
}

unlink($cache);
rename($cache . '~real', $cache);

$t->diag('testing ->getCategories()');
$cats = $lucene->getCategories();

$t->isa_ok($cats, 'sfLuceneCategories', '->getCategories() returns an instance of sfLuceneCategories');
$t->ok($lucene->getCategories() === $cats, '->getCategories() is a singleton');

$t->diag('testing ->getIndexer()');
$indexer = $lucene->getIndexer();
$t->isa_ok($indexer, 'sfLuceneIndexerFactory', '->getIndexer() returns an instance of sfLuceneIndexerFactory');

$t->diag('testing ->getContext()');
$t->isa_ok($lucene->getContext(), 'sfContext', '->getContext() returns an instance of sfContext');
$t->is($lucene->getContext(), sfContext::getInstance(), '->getContext() returns the same context');

$t->diag('testing ->configure()');
$lucene->configure();

$t->is(Zend_Search_Lucene_Search_QueryParser::getDefaultEncoding(), 'utf-8', '->configure() configures the query parsers encoding');

foreach (array('Text', 'TextNum', 'Utf8', 'Utf8Num') as $type)
{
  $lucene->setParameter('analyzer', $type);
  $lucene->configure();

  $class = 'Zend_Search_Lucene_Analysis_Analyzer_Common_' . $type;
  $expected = new $class();
  $expected->addFilter(new sfLuceneLowerCaseFilter(true));
  $expected->addFilter(new Zend_Search_Lucene_Analysis_TokenFilter_StopWords(array('and', 'the')));
  $expected->addFilter(new Zend_Search_Lucene_Analysis_TokenFilter_ShortWords(2));

  $actual = Zend_Search_Lucene_Analysis_Analyzer::getDefault();

  $t->ok($actual == $expected, '->configure() configures the analyzer for ' . $type);
}

$lucene->setParameter('analyzer', 'foobar');

try {
  $lucene->configure();
  $t->fail('->configure() analyzer must be of text, textnum, utf8, or utf8num');
} catch (Exception $e) {
  $t->pass('->configure() analyzer must be of text, textnum, utf8, or utf8num');
}

$lucene->setParameter('analyzer', 'utf8num');

$t->diag('testing ->find()');

class MockLucene
{
  public $args;
  public $scoring;
  public $e = false;

  public function find()
  {
    if ($this->e) throw new Exception('Because you said so');

    $this->args = func_get_args();
    $this->scoring = Zend_Search_Lucene_Search_Similarity::getDefault();

    return range(1, 100);
  }
}

class MockScoring extends Zend_Search_Lucene_Search_Similarity_Default {}

$mock = new MockLucene;

$originalLucene = $lucene->getParameter('lucene');
$lucene->setParameter('lucene', $mock);

$t->is($lucene->find('foo'), range(1, 100), '->find() returns what ZSL returns');
$t->ok(sfLuceneCriteria::newInstance()->add('foo')->getQuery() == $mock->args[0], '->find() parses string queries');
$t->isa_ok($mock->scoring, 'Zend_Search_Lucene_Search_Similarity_Default', '->find() with a string uses default scoring algorithm');

$query = sfLuceneCriteria::newInstance()->add('foo')->addRange('a', 'b', 'c');
$lucene->find($query);
$t->ok($query->getQuery() == $mock->args[0], '->find() accepts sfLuceneCriteria queries');
$t->isa_ok($mock->scoring, 'Zend_Search_Lucene_Search_Similarity_Default', '->find() without specified scorer uses default scoring algorithm');

$query = new Zend_Search_Lucene_Search_Query_Boolean();
$lucene->find($query);
$t->ok($query == $mock->args[0], '->find() accepts Zend API queries');
$t->isa_ok($mock->scoring, 'Zend_Search_Lucene_Search_Similarity_Default', '->find() with a Zend API queries uses default scoring algorithm');

$scoring = new MockScoring;
$lucene->find(sfLuceneCriteria::newInstance()->add('foo')->setScoringAlgorithm($scoring));
$t->is($mock->scoring, $scoring, '->find() changes the scoring algorithm if sfLuceneCriteria specifies it');
$t->isa_ok(Zend_Search_Lucene_Search_Similarity::getDefault(), 'Zend_Search_Lucene_Search_Similarity_Default', '->find() resets the default scoring algorithm after processing');

$lucene->find(sfLuceneCriteria::newInstance()->add('foo')->addAscendingSortBy('sort1')->addDescendingSortBy('sort2', SORT_NUMERIC));

$t->is_deeply(array_splice($mock->args, 1), array('sort1', SORT_REGULAR, SORT_ASC, 'sort2', SORT_NUMERIC, SORT_DESC), '->find() uses sorting rules from sfLuceneCriteria');

$results = $lucene->friendlyFind('foo');
$t->isa_ok($results, 'sfLuceneResults', '->friendlyFind() returns an instance of sfLuceneResults');
$t->is($results->toArray(), range(1, 100), '->friendlyFind() houses the data from ZSL');
$t->is($results->getSearch(), $lucene, '->friendlyFind() is connected to the Lucene instance');

$mock->e = true;
try {
  $lucene->find(sfLuceneCriteria::newInstance()->add('foo')->setScoringAlgorithm(new MockScoring));
  $t->fail('if ZSL throws exception, ->find() also throws the exception');
  $t->skip('if ZSL throws exception, ->find() stills resets the scoring algorithm');
} catch (Exception $e) {
  $t->pass('if ZSL throws exception, ->find() also throws the exception');
  $t->isa_ok(Zend_Search_Lucene_Search_Similarity::getDefault(), 'Zend_Search_Lucene_Search_Similarity_Default', 'if ZSL throws exception, ->find() stills resets the scoring algorithm');
}

$lucene->setParameter('lucene', $originalLucene);

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

$originalFactory = $lucene->getParameter('indexer_factory');
$lucene->setParameter('indexer_factory', $factory);

$lucene->getCategories()->getCategory('foo');
$lucene->getCategories()->save();

$lucene->rebuildIndex();

$t->is($factory->deleteLock, true, '->rebuildIndex() enables the delete lock');
$t->ok($handlers[0]->count == 1 && $handlers[0]->count == 1, '->rebuildIndex() calls each handler\'s ->rebuild() only once');

$t->is($lucene->getCategories()->getAllCategories(), array(), '->rebuildIndex() clears the category list');

$lucene->setParameter('indexer_factory', $originalFactory);

$t->diag('testing wrappers');

try {
  $lucene->optimize();
  $t->pass('->optimize() optimizes the index without exception');
} catch (Exception $e) {
  $t->fail('->optimize() optimizes the index without exception');
}

try {
  $t->is($lucene->count(), 0, '->count() returns the document count');
  $t->pass('->count() counts the index without exception');
} catch (Exception $e) {
  $t->skip('->count() returns the document count');
  $t->fail('->count() counts the index without exception');
}

try {
  $t->is($lucene->numDocs(), 0, '->numDocs() returns the document count');
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

$t->diag('testing statistics');

$originalLocation = $lucene->getParameter('index_location');

$lucene->setParameter('index_location', DATA_DIR . '/foo');

$t->is($lucene->byteSize(), 8222, '->byteSize() returns the correct size in bytes');
$t->is($lucene->segmentCount(), 2, '->segmentCount() returns the correct segment count');

$lucene->setParameter('index_location', $originalLocation);

$t->diag('testing modes');

class FooController
{
  public $cli = true;

  public function inCLI()
  {
    return $this->cli;
  }
}

$controller = new FooController();
$oldController = sfContext::getInstance()->get('controller');
sfContext::getInstance()->set('controller', $controller);

$lucene->setAutomaticMode();
$t->is($lucene->getLucene()->getMaxBufferedDocs(), 500, '->setAutomaticMode() sets MaxBufferedDocs to 500 in a CLI environment');
$t->is($lucene->getLucene()->getMaxMergeDocs(), PHP_INT_MAX, '->setAutomaticMode() sets MaxMaxMergeDocs to PHP_INT_MAX in a CLI environment');
$t->is($lucene->getLucene()->getMergeFactor(), 50, '->setAutomaticMode() sets MergeFactor to 50 in a CLI environment');

$controller->cli = false;

$lucene->setAutomaticMode();
$t->is($lucene->getLucene()->getMaxBufferedDocs(), 10, '->setAutomaticMode() sets MaxBufferedDocs to 10 in a web environment');
$t->is($lucene->getLucene()->getMaxMergeDocs(), PHP_INT_MAX, '->setAutomaticMode() sets MaxMaxMergeDocs to PHP_INT_MAX in a web environment');
$t->is($lucene->getLucene()->getMergeFactor(), 10, '->setAutomaticMode() sets MergeFactor to 10 in a web environment');

sfContext::getInstance()->set('controller', $oldController);

$lucene->setBatchMode();
$t->is($lucene->getLucene()->getMaxBufferedDocs(), 500, '->setBatchMode() sets MaxBufferedDocs to 500');
$t->is($lucene->getLucene()->getMaxMergeDocs(), PHP_INT_MAX, '->setBatchMode() sets MaxMaxMergeDocs to PHP_INT_MAX');
$t->is($lucene->getLucene()->getMergeFactor(), 50, '->setBatchMode() sets MergeFactor to 50');

$lucene->setInteractiveMode();
$t->is($lucene->getLucene()->getMaxBufferedDocs(), 10, '->setInteractiveMode() sets MaxBufferedDocs to 10');
$t->is($lucene->getLucene()->getMaxMergeDocs(), PHP_INT_MAX, '->setInteractiveMode() sets MaxMaxMergeDocs to PHP_INT_MAX');
$t->is($lucene->getLucene()->getMergeFactor(), 10, '->setInteractiveMode() sets MergeFactor to 10');

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

$lucene->getContext()->getEventDispatcher()->connect('lucene.lucene.method_not_found', 'callListener');

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