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

$t = new lime_test(23, new lime_output_color());

$lucene = sfLucene::getInstance('testLucene');

class MockResult extends Zend_Search_Lucene_Search_QueryHit
{
  public function __construct($a)
  {
  }

  public function getDocument()
  {
    return $this->doc;
  }
}

class MockDocument
{
  public function getFieldValue($field)
  {
    if (!isset($this->$field)) throw new Exception('Field ' . $field . ' does not exist');

    return $this->$field;
  }
}

$mockresult = new MockResult($lucene->getLucene());
$mockresult->score = .425;
$mockresult->id = 1;
$doc = new MockDocument;
$mockresult->doc = $doc;

$t->diag('testing constructor');

try {
  new sfLuceneResult($mockresult, $lucene);
  $t->pass('__construct() accepts a valid result and valid sfLucene instance');
} catch (Exception $e) {
  $t->fail('__construct() accepts a valid result and valid sfLucene instance');
}

$doc->sfl_type = 'action';
$t->isa_ok(sfLuceneResult::getInstance($mockresult, $lucene), 'sfLuceneActionResult', '::getInstance() returns an instance of sfLuceneActionResult for "type" = action');

$doc->sfl_type = 'model';
$t->isa_ok(sfLuceneResult::getInstance($mockresult, $lucene), 'sfLuceneModelResult', '::getInstance() returns an instance of sfLuceneModelResult for "type" = model');


$doc->sfl_type = 'regular';
$result = sfLuceneResult::getInstance($mockresult, $lucene);
$t->isa_ok($result, 'sfLuceneResult', '::getInstance() returns an instance of sfLuceneResult for "type" = regular');

$t->diag('testing ->getSearch(), ->getResult()');
$t->is($result->getSearch(), $lucene, '->getSearch() returns the same instance of sfLucene as initialized with');
$t->is($result->getResult(), $mockresult, '->getResult() returns the same instace of the result as initialized with');

$t->diag('testing simple ->get*()');
$t->is($result->getScore(), 43, '->getScore() gets the percentage from decimal and rounds');
$t->is($result->getInternalPartial(), 'sfLucene/regularResult', '->getInternalPartial() returns the correct partial name');

$t->diag('testing dynamic ->getXXX()');
$doc->sequence = '123';
$t->is($result->getSequence(), '123', '->getXXX() returns property XXX on document');
$t->ok($result->hasSequence(), '->hasXXX() returns true if document has property XXX');

$doc->super_duper_man = 'Fabien Potencier';
$t->is($result->getSuperDuperMan(), 'Fabien Potencier', '->getXXX() returns property XXX for camel case');
$t->ok($result->hasSuperDuperMan(), '->hasXXX() returns if document has property XXX for camel case');

try {
  $result->getSomethingReallyBad();
  $t->fail('->getXXX() fails if the property does not exist');
} catch (Exception $e) {
  $t->pass('->getXXX() fails if the property does not exist');
}

$t->ok(!$result->hasSomethingReallyBad(), '->hasXXX() returns false if the document does not have property XXX');

$doc->sfl_field = '987';
$t->is($result->getInternalField(), '987', '->getInternalXXX() returns internal properties');
$t->ok($result->hasInternalField(), '->hasInternalXXX() returns true if internal property exists');

$t->diag('testing ->getInternalDescription()');

try {
  $result->getInternalDescription();
  $t->pass('->getInternalDescription() executes even if there is no description');
} catch (Exception $e) {
  $t->fail('->getInternalDescription() executes even if there is no description');
}

$doc->sfl_description = 'foo bar <b>baz</b>';
$t->is($result->getInternalDescription(), 'foo bar baz', '->getInternalDescription() strips out HTML tags');

$t->diag('testing ->getInternalTitle()');
try {
  $result->getInternalTitle();
  $t->pass('->getInternalTitle() executes even if there is no title');
} catch (Exception $e) {
  $t->fail('->getInternalTitle() executes even if there is no title');
}

$doc->sfl_title = 'foo bar <b>baz</b>';
$t->is($result->getInternalTitle(), 'foo bar <b>baz</b>', '->getInternalTitle() does not strip out HTML tags');

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

sfContext::getInstance()->getEventDispatcher()->connect('lucene.result.method_not_found', 'callListener');

try {
  $result->someBadMethod();
  $t->fail('__call() rejects bad methods');
} catch (Exception $e) {
  $t->pass('__call() rejects bad methods');
}

try {
  $return = $result->goodMethod(2);
  $t->pass('__call() accepts good methods');
  $t->is($return, 3, '__call() passes arguments');
} catch (Exception $e) {
  $t->fail('__call() accepts good methods and passes arguments');

  $e->printStackTrace();

  $t->skip('__call() passes arguments');
}