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

$t = new limeade_test(27, limeade_output::get());

class Foo { }

function inst($app_configuration)
{
  return sfLuceneCriteria::newInstance(sfLucene::getInstance('index','en', $app_configuration));
}

$t->diag('testing constructors');
try {
  $criteria = new sfLuceneCriteria(sfLucene::getInstance('index','en', $app_configuration));
  $t->pass('__construct() takes a sfLucene instance');
} catch (Exception $e) {
  $t->fail('__construct() takes a sfLuce instance');
}
$t->isa_ok(sfLuceneCriteria::newInstance(sfLucene::getInstance('index','en', $app_configuration)), 'sfLuceneCriteria', '::newInstance() returns an sfLuceneCriteria object');

$t->diag('testing ->getQuery()');
$t->ok(is_string($criteria->getQuery()), '->getQuery() returns an instance a string');


$t->diag('testing ->add()');
$criteria->add('test', sfLuceneCriteria::TYPE_AND);
$t->pass('->add() accepts a string');

$queries = inst($app_configuration)->add('foo')->add('bar')->getQuery();
$t->cmp_ok($queries, '===', 'foo AND bar', '->add() correctly parses and adds text queries');

$queries = inst($app_configuration)->add('foo')->add('bar', sfLuceneCriteria::TYPE_OR)->getQuery();
$t->cmp_ok($queries, '===', 'foo OR bar', '->add() correctly parses and adds text queries');

$query = inst($app_configuration);
$query->add('foo');

$criteria->add($query, null);
$t->pass('->add() accepts sfLuceneCriteria');

$luceneQuery = inst($app_configuration)->add($query);
$luceneQuery->add('bar', sfLuceneCriteria::TYPE_OR);
$subqueries = inst($app_configuration)->add($luceneQuery)->getQuery();

$t->cmp_ok($subqueries, '==', '((foo) OR bar)', '->getQuery() correctly combines sfLuceneCriteria queries');

try {
  $criteria->add($criteria, true);
  $t->fail('->add() rejects itself');
} catch (Exception $e) {
  $t->pass('->add() rejects itself');
}

try {
  $criteria->add(new Foo());
  $t->fail('->add() rejects invalid queries');
} catch (Exception $e) {
  $t->pass('->add() rejects invalid queries');
}

$t->diag('testing ->addString()');

$criteria->add('test');

$queries = inst($app_configuration)->addString('foobar')->getQuery();
$t->cmp_ok($queries, '===', $queries , '->addString() correctly parses and adds string queries');

$t->diag('testing ->addSane()');

$criteria = inst($app_application);
$criteria->addSane('test'); 
$s = $criteria->getQuery();
$t->cmp_ok($s, '===', '"test"', '::addSane() with standard string');

$criteria->addSane('&" ? \unsafe'); 
$s = $criteria->getQuery();

$t->cmp_ok($s, '===', '"test" AND "&\\" ? \\\\unsafe"', '::addSane() with standard string');

try {
  $criteria->add('carl!');
  $t->fail('->add() rejects an illegal query');
} catch (Exception $e) {
  $t->pass('->add() rejects an illegal query');
}

try {
  $criteria->addSane('carl!');
  $t->pass('->addSane() accepts an illegal query');
} catch (Exception $e) {
  $t->fail('->addSane() accepts an illegal query');
  $t->skip('->addSane() correctly adds an illegal query');
}

$t->diag('testing addWildcard()');

$s = inst($app_configuration)->addWildcard('foo*')->getQuery();
$t->cmp_ok($s, '===', '"foo*"', '->addWildcard() registers the correct query with mutlitple character wildcards');

$s = inst($app_configuration)->addWildcard('f?o')->getQuery();
$t->cmp_ok($s, '===', '"f?o"', '->addWildcard() registers the correct query with single character wildcards');

$s = inst($app_configuration)->addWildcard('foo* baz?')->getQuery();
$t->cmp_ok($s, '===', '"foo* baz?"', '->addWildcard() registers the correct query with mixing character wildcards');

$t->diag('testing addPhrase()');
$s = inst($app_configuration)->addPhrase("foo bar")->getQuery();

$t->ok($s == '"foo bar"', '->addPhrase() registers the correct simple phrase query');

$t->diag('testing addRange()');

$s = inst($app_configuration)->addRange('a', 'b')->getQuery();
$t->cmp_ok($s, '===', '[a TO b]', '->addRange() registers a simple, two-way range');

$s = inst($app_configuration)->addRange('a')->getQuery();
$t->cmp_ok($s, '===', '[a TO *]', '->addRange() registers a simple, one-way forward range');

$s = inst($app_configuration)->addRange(null, 'b')->getQuery();
$t->cmp_ok($s, '===', '[* TO b]', '->addRange() registers a simple, one-way backward range');

try {
  $s = inst()->addRange(null, null);
  $t->fail('->addRange() rejects a query with no range');
} catch (Exception $e) {
  $t->pass('->addRange() rejects a query with no range');
}

$t->diag('testing addProximity()');

try {
  inst($app_configuration)->addProximity(37.7752, -122.4192, 0);
  $t->fail('->addProximity() rejects a zero proximity');
} catch (Exception $e) {
  $t->pass('->addProximity() rejects a zero proximity');
}

try {
  inst($app_configuration)->addProximity(37.7752, -122.4192, 90, 0);
  $t->fail('->addProximity() rejects a zero radius');
} catch (Exception $e) {
  $t->pass('->addProximity() rejects a zero radius');
}

// not sure this test is fine ... float computation might differ from cpu to cpu
$s = inst($app_configuration)->addProximity(37.7752, -122.4192, 200)->getQuery();
$expected = '(latitude:[35.9785590093 TO 39.5718409907] AND longitude:[-124.6922199992 TO -120.1461800008])';
$t->cmp_ok($s, '===', $expected, '->addProximity()');


$t->diag('testing getNewCriteria()');

$t->isa_ok(inst($app_configuration)->getNewCriteria(), 'sfLuceneCriteria', '->getNewCriteria() returns a new instance of sfLuceneCriteria');