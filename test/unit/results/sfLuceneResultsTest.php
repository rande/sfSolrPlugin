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

sfConfig::set('sf_orm', 'doctrine');

class MockResult extends sfLuceneDocument
{
  public $name;
  public $sfl_type = 'model';
  public function __construct($a)
  {
    $this->name = $a;
  }
}

// create fake response
$standard_response = '{"responseHeader":{"status":0,"QTime":0},"response":{"numFound":3,"start":%s,"docs":[%s]}}';
$expected_objects = array();
$results = array();

foreach(range(1, 3) as $num)
{
  $results[] = sprintf(
    '{"description":"symfony fan boy","id":%d,"name":"rande","sfl_type":"model","sfl_guid":"GUID_%d","skills":["symfony","php","objective-c"]}',
    $num,
    $num
  );
  
  $expected_objects[] =<<<VAR_DUMP
sfLuceneDocument::__set_state(array(
   '_documentBoost' => false,
   '_fields' => 
  array (
    'description' => 'symfony fan boy',
    'id' => $num,
    'name' => 'rande',
    'sfl_type' => 'model',
    'sfl_guid' => 'GUID_$num',
    'skills' => 
    array (
      0 => 'symfony',
      1 => 'php',
      2 => 'objective-c',
    ),
  ),
   '_fieldBoosts' => 
  array (
    'description' => false,
    'id' => false,
    'name' => false,
    'sfl_type' => false,
    'sfl_guid' => false,
    'skills' => false,
  ),
))
VAR_DUMP;
}
$standard_response = sprintf($standard_response, 3, implode(", ", $results));


$response = new sfLuceneResponse($standard_response);

$search = sfLucene::getInstance('index', 'en', $app_configuration);

$results = new sfLuceneResults($response, $search);


$t->diag('testing ->getSearch(), ->toArray()');
$t->is($results->getSearch(), $search, '->getSearch() returns the same search instance');

foreach($results->toArray() as $pos => $result)
{
  $t->is(var_export($result, 1 ), $expected_objects[$pos], '->toArray() pos #'.$pos);
}

$t->diag('testing Iterator interface');

// $got = array();
// $once = false;
// foreach ($results as $key => $value)
// {
//   if (!$once)
//   {
//     $t->ok($value instanceof sfLuceneResult, 'iterator interface returns instances of sfLuceneResult');
//     $once = true;
//   }
// 
//   $t->is(var_export($value->getResult(), 1 ), $expected_objects[$key], '->getResult() pos #'.$key);
// }
// 
// $t->is($got, $data, 'sfLuceneResults implements the Iterator interface');

// die();
$t->diag('testing Countable interface');
$t->is(count($results), 3, 'sfLuceneResults implements the Countable interface');

$t->diag('testing ArrayAccess interface');

$t->ok(isset($results[1]), 'sfLuceneResults implements the ArrayAccess isset() interface');
$t->ok($results[1] instanceof sfLuceneResult, 'sfLuceneResults ArrayAccess interface getter returns instances of sfLuceneResult');
$t->ok($results[1]->getResult(), 'sfLuceneResults implements the ArrayAccess getter interface');


$nresult = new MockResult('foo');
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

$search->getEventDispatcher()->connect('sf_lucene_results.method_not_found', 'callListener');

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