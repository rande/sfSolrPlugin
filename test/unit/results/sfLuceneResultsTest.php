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

$t = new limeade_test(13, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

$luceneade = new limeade_lucene($limeade);
$luceneade->configure()->clear_sandbox();

$lucene = sfLucene::getInstance('testLucene', 'en');

class MockResult extends Zend_Search_Lucene_Search_QueryHit
{
  public $name;
  public function __construct($a)
  {
    $this->name = $a;
  }

  public function getDocument()
  {
    return new MockDocument;
  }
}

class MockDocument
{
  public function getFieldValue($field)
  {
    if ($field == 'sfl_type')
    {
      return 'regular';
    }

    throw new Exception('d');
  }
}
$data = array(new MockResult('foo'), new MockResult('bar'), new MockResult('baz'));
$search = sfLucene::getInstance('testLucene');

$results = new sfLuceneResults($data, $search);

$t->diag('testing ->getSearch(), ->toArray()');
$t->is($results->getSearch(), $search, '->getSearch() returns the same search instance');
$t->is($results->toArray(), $data, '->toArray() returns the same search data');

$t->diag('testing Iterator interface');

$got = array();
$once = false;
foreach ($results as $key => $value)
{
  if (!$once)
  {
    $t->ok($value instanceof sfLuceneResult, 'iterator interface returns instances of sfLuceneResult');
    $once = true;
  }

  $got[$key] = $value->getResult();
}

$t->is($got, $data, 'sfLuceneResults implements the Iterator interface');

$t->diag('testing Countable interface');
$t->is(count($results), count($data), 'sfLuceneResults implements the Countable interface');

$t->diag('testing ArrayAccess interface');

$t->ok(isset($results[1]), 'sfLuceneResults implements the ArrayAccess isset() interface');
$t->ok($results[1] instanceof sfLuceneResult, 'sfLuceneResults ArrayAccess interface getter returns instances of sfLuceneResult');
$t->ok($results[1]->getResult(), 'sfLuceneResults implements the ArrayAccess getter interface');

$nresult = new MockResult('foobar');
$results[3] = $nresult;
$t->is($results[3]->getResult(), $nresult, 'sfLuceneResults implements the ArrayAccess setter interface');

unset($results[3]);
$t->ok(!isset($results[3]), 'sfLuceneResults implements the ArrayAccess unset() interface');

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

$search->getEventDispatcher()->connect('results.method_not_found', 'callListener');

try {
  $results->someBadMethod();
  $t->fail('__call() rejects bad methods');
} catch (Exception $e) {
  $t->pass('__call() rejects bad methods');
}

try {
  $return = $results->goodMethod(2);
  $t->pass('__call() accepts good methods');
  $t->is($return, 3, '__call() passes arguments');
} catch (Exception $e) {
  $t->fail('__call() accepts good methods and passes arguments');

  $e->printStackTrace();

  $t->skip('__call() passes arguments');
}