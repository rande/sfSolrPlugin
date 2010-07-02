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

sfConfig::set('sf_orm', 'doctrine');

$t = new limeade_test(23, limeade_output::get());

$lucene = sfLucene::getInstance('index', 'en', $app_configuration);

class MockResult extends sfLuceneDocument
{
}


$mockresult = new MockResult($lucene->getLucene());
$mockresult->score = .425;
$mockresult->id = 1;

$t->diag('testing constructor');

try {
  $ex = $t->no_exception('__construct() accepts a valid result and valid sfLucene instance');
  new sfLuceneResult($mockresult, $lucene);
  $ex->no();
} catch (Exception $e) {
  $ex->caught($e);
}

$mockresult->sfl_type = 'action';
$t->isa_ok(sfLuceneResult::getInstance($mockresult, $lucene), 'sfLuceneActionResult', '::getInstance() returns an instance of sfLuceneActionResult for "type" = action');

$mockresult->sfl_type = 'model';
$t->isa_ok(sfLuceneResult::getInstance($mockresult, $lucene), 'sfLuceneDoctrineResult', '::getInstance() returns an instance of sfLuceneModelResult for "type" = model');

$mockresult->sfl_type = 'regular';
$result = sfLuceneResult::getInstance($mockresult, $lucene);
$t->isa_ok($result, 'sfLuceneResult', '::getInstance() returns an instance of sfLuceneResult for "type" = regular');

$t->diag('testing ->getSearch(), ->getResult()');
$t->is($result->getSearch(), $lucene, '->getSearch() returns the same instance of sfLucene as initialized with');
$t->is($result->getResult(), $mockresult, '->getResult() returns the same instace of the result as initialized with');

$t->diag('testing simple ->get*()');
$t->is($result->getScore(), 43, '->getScore() gets the percentage from decimal and rounds');
$t->is($result->getInternalPartial(), 'sfLucene/regularResult', '->getInternalPartial() returns the correct partial name');

$t->diag('testing dynamic ->getXXX()');
$mockresult->sequence = '123';
$t->is($result->getSequence(), '123', '->getXXX() returns property XXX on document');
$t->ok($result->hasSequence(), '->hasXXX() returns true if document has property XXX');

$mockresult->super_duper_man = 'Fabien Potencier';
$t->is($result->getSuperDuperMan(), 'Fabien Potencier', '->getXXX() returns property XXX for camel case');
$t->ok($result->hasSuperDuperMan(), '->hasXXX() returns if document has property XXX for camel case');

try {
  $ex = $t->exception('->getXXX() fails if the property does not exist');
  $result->getSomethingReallyBad();
  $ex->no();
} catch (Exception $e) {
  $ex->caught($e);
}

$t->ok(!$result->hasSomethingReallyBad(), '->hasXXX() returns false if the document does not have property XXX');

$mockresult->sfl_field = '987';
$t->is($result->getInternalField(), '987', '->getInternalXXX() returns internal properties');
$t->ok($result->hasInternalField(), '->hasInternalXXX() returns true if internal property exists');

$t->diag('testing ->getInternalDescription()');

try {
  $ex = $t->no_exception('->getInternalDescription() executes even if there is no description');
  $result->getInternalDescription();
  $ex->no();
} catch (Exception $e) {
  $ex->caught($e);
}

$mockresult->sfl_description = 'foo bar <b>baz</b>';
$t->is($result->getInternalDescription(), 'foo bar baz', '->getInternalDescription() strips out HTML tags');

$t->diag('testing ->getInternalTitle()');
try {
  $result->getInternalTitle();
  $t->pass('->getInternalTitle() executes even if there is no title');
} catch (Exception $e) {
  $t->fail('->getInternalTitle() executes even if there is no title');
}

$mockresult->sfl_title = 'foo bar <b>baz</b>';
$t->is($result->getInternalTitle(), 'foo bar <b>baz</b>', '->getInternalTitle() does not strip out HTML tags');

$t->diag('testing mixins');

function mixin_listener(sfEvent $event)
{
  if ($event['method'] == 'goodMethod')
  {
    $args = $event['arguments'];
    $event->setReturnValue($args[0] + 1);
    
    return true;
  }
}

$lucene->getEventDispatcher()->connect('sf_lucene_result.method_not_found', 'mixin_listener');

try {
  $ex = $t->exception('__call() rejects bad methods');
  $result->someBadMethod();
  $ex->no();
} catch (Exception $e) {
  $ex->caught($e);
}

try {
  $ex = $t->no_exception('__call() accepts good methods');
  $return = $result->goodMethod(2);
  $ex->no();

  $t->is($return, 3, '__call() passes arguments');
} catch (Exception $e) {
  $ex->caught($e);

  $t->skip('__call() passes arguments');
}
